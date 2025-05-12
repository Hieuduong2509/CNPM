<?php
require_once("./BE/db.php");
header('Content-Type: application/json');

try {
    // Lấy dữ liệu JSON từ request
    $json_data = file_get_contents('php://input');
    $product_data = json_decode($json_data, true);
    
    // Ghi log dữ liệu nhận được
    error_log("Received product data: " . $json_data);
    
    // Kiểm tra dữ liệu
    if (!is_array($product_data) || !isset($product_data['product_name'])) {
        throw new Exception("Dữ liệu sản phẩm không hợp lệ");
    }
    
    $conn = create_connection();
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối cơ sở dữ liệu: " . $conn->connect_error);
    }
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Chuẩn bị dữ liệu - ĐÚNG với cấu trúc bảng của bạn
        $name = $product_data['product_name'];
        $description = $product_data['description'] ?? ' ';
        $price = isset($product_data['price']) ? floatval($product_data['price']) : 0;
        $stockQuantity = isset($product_data['quantity']) ? intval($product_data['quantity']) : 
                        (isset($product_data['stockQuantity']) ? intval($product_data['stockQuantity']) : 0);
        $customer_name = $product_data['customer_name'] ?? '';
        // Xác định categoryId
        // Ánh xạ tên danh mục sang categoryId
$categoryMap = [
    'Laptop' => 1,
    'Mouse' => 2,
    'Keyboard' => 3,
];

// Lấy categoryId từ tên hoặc số
$categoryId = 0;
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

        // Lấy các thông tin khác
        $image = isset($product_data['image']) ? $product_data['image'] : '';
        $popular = isset($product_data['popular']) ? intval($product_data['popular']) : 0;
        
        // Ghi log dữ liệu đã chuẩn bị
        error_log("Prepared data for insertion: name=$name, price=$price, categoryId=$categoryId, image=$image");
        
        // Kiểm tra xem sản phẩm đã tồn tại chưa
        $stmt = $conn->prepare("SELECT productId FROM product WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        error_log("Check tồn tại sản phẩm: " . $result->num_rows);
        
        if ($result->num_rows > 0) {
            // Sản phẩm đã tồn tại
            $row = $result->fetch_assoc();
            $productId = $row['productId'];
            $stmt->close();
            
            // Cập nhật sản phẩm - CHÍNH XÁC theo cấu trúc bảng
            $stmt_update = $conn->prepare("UPDATE product SET description = ?, price = ?, stockQuantity = ?, categoryId = ?, popular = ?, image = ? WHERE productId = ?");
            $stmt_update->bind_param("sdiiisi", $description, $price, $stockQuantity, $categoryId, $popular, $image, $productId);
            
            if ($stmt_update->execute()) {
                $stmt_update->close();
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Sản phẩm đã được cập nhật thành công',
                    'productId' => $productId
                ]);
            } else {
                throw new Exception("Lỗi khi cập nhật sản phẩm: " . $stmt_update->error);
            }
        } else {
            // Sản phẩm chưa tồn tại, thêm mới
            $stmt->close();
            
            // ĐIỀU CHỈNH: Chỉ sử dụng các trường thực tế có trong bảng
            // Khớp chính xác với cấu trúc INSERT của bạn
            $stmt_insert = $conn->prepare("INSERT INTO product (name, description, price, stockQuantity, categoryId, popular, image, customer_name) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Kiểu dữ liệu: 
            $stmt_insert->bind_param("ssdiiiss", $name, $description, $price, $stockQuantity, $categoryId, $popular, $image, $customer_name);
            if ($stmt_insert->execute()) {
                $productId = $conn->insert_id;
                $stmt_insert->close();
                
                // Commit transaction
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Sản phẩm đã được thêm thành công',
                    'productId' => $productId
                ]);
                
                error_log("Product saved successfully with ID: $productId");
            } else {
                $error = $stmt_insert->error;
                error_log("SQL Error: $error");
                throw new Exception("Lỗi khi thêm sản phẩm: $error");
            }
        }
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
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
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
