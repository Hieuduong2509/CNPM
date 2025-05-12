<?php
require_once("./BE/db.php");
header('Content-Type: application/json');

try {
    $json_data = file_get_contents('php://input');
    $product_data = json_decode($json_data, true);

    error_log("Received product data: " . $json_data);

    if (!is_array($product_data) || !isset($product_data['product_name'])) {
        throw new Exception("Dữ liệu sản phẩm không hợp lệ");
    }

    $conn = create_connection();
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối cơ sở dữ liệu: " . $conn->connect_error);
    }

    $conn->begin_transaction();

    try {
        // Lấy và chuẩn hóa dữ liệu
        $name = trim($product_data['product_name']);
        $description = $product_data['description'] ?? '';
        $price = isset($product_data['price']) ? floatval($product_data['price']) : 0;
        $stockQuantity = isset($product_data['stockQuantity']) ? intval($product_data['stockQuantity']) : 0;
        $customer_name = $product_data['customer_name'] ?? '';
        $image = $product_data['image'] ?? null;
        $popular = isset($product_data['popular']) ? intval($product_data['popular']) : 0;

        // Ánh xạ category
        $categoryMap = [
            'Laptop' => 1,
            'Mouse' => 2,
            'Keyboard' => 3
        ];

        $categoryId = 1; // mặc định là Laptop
        if (isset($product_data['category'])) {
            $categoryRaw = $product_data['category'];
            if (is_numeric($categoryRaw)) {
                $categoryId = intval($categoryRaw);
            } elseif (isset($categoryMap[$categoryRaw])) {
                $categoryId = $categoryMap[$categoryRaw];
            } else {
                throw new Exception("Danh mục không hợp lệ: $categoryRaw");
            }
        }

        error_log("Prepared: name=$name, price=$price, stock=$stockQuantity, category=$categoryId");

        // Kiểm tra trùng tên sản phẩm
        $stmt = $conn->prepare("SELECT productId FROM product WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật nếu tồn tại
            $row = $result->fetch_assoc();
            $productId = $row['productId'];
            $stmt->close();

            $stmt_update = $conn->prepare("UPDATE product SET description = ?, price = ?, stockQuantity = ?, categoryId = ?, popular = ?, image = ?, customer_name = ? WHERE productId = ?");
            $stmt_update->bind_param("sdiiissi", $description, $price, $stockQuantity, $categoryId, $popular, $image, $customer_name, $productId);

            if ($stmt_update->execute()) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công', 'productId' => $productId]);
            } else {
                throw new Exception("Lỗi cập nhật: " . $stmt_update->error);
            }

            $stmt_update->close();
        } else {
            $stmt->close();

            // Thêm sản phẩm mới
            $stmt_insert = $conn->prepare("INSERT INTO product (name, description, price, stockQuantity, categoryId, popular, image, customer_name)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssdiiiss", $name, $description, $price, $stockQuantity, $categoryId, $popular, $image, $customer_name);

            if ($stmt_insert->execute()) {
                $productId = $conn->insert_id;
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công', 'productId' => $productId]);
            } else {
                throw new Exception("Lỗi thêm sản phẩm: " . $stmt_insert->error);
            }

            $stmt_insert->close();
        }

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    error_log("Error in save_product.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
