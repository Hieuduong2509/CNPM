<?php
require_once("./BE/db.php");
header('Content-Type: application/json');

try {
    $json_data = file_get_contents('php://input');
    $order_data = json_decode($json_data, true);
    // Kiểm tra dữ liệu
    if (!is_array($order_data) || !isset($order_data['products']) || !is_array($order_data['products'])) {
        throw new Exception("Dữ liệu đơn hàng không hợp lệ");
    }
    $conn = create_connection();
    if ($conn->connect_error) {
        throw new Exception("Lỗi kết nối cơ sở dữ liệu: " . $conn->connect_error);
    }
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        $customerId = 1; 
        if (isset($order_data['customer_name'])) {
            $customer_name = $order_data['customer_name'];
            $stmt = $conn->prepare("SELECT customerId FROM customer WHERE name = ?");
            $stmt->bind_param("s", $customer_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $customerId = $row['customerId'];   
            }
            $stmt->close();
        }
        
        // Lấy status từ order_data
        $status = $order_data['status'] ?? 'Processing';
        
        // Lưu thông tin đơn hàng đầu tiên để lấy orderId cho bảng invoice
        $firstOrderId = null;
        
        // Xử lý từng sản phẩm trong đơn hàng
        foreach ($order_data['products'] as $product) {
            $productId = null;
            $productName = $product['product_name'] ?? '';
            $stmt = $conn->prepare("SELECT productId FROM product WHERE name = ?");
            $stmt->bind_param("s", $productName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Sản phẩm đã tồn tại
                $row = $result->fetch_assoc();
                $productId = $row['productId'];
            } else {
                // Sản phẩm chưa tồn tại, thêm mới vào bảng product
                $description = $product['description'] ?? '';
                $price = $product['price'] ?? 0;
                $stockQuantity = $product['stockQuantity'] ?? 0;
                $categoryId = $product['category'] == 'Laptop' ? 1 : ($product['category'] == 'Mouse' ? 2 : 3); 
                $image = $product['image'] ?? null;
                
                $stmt_insert_product = $conn->prepare("INSERT INTO product (name, description, price, stockQuantity, categoryId, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert_product->bind_param("ssdiis", $productName, $description, $price, $stockQuantity, $categoryId, $image);
                $stmt_insert_product->execute();
                $productId = $conn->insert_id;
                $stmt_insert_product->close();
            }
            
            $stmt->close();
            
            // Thêm vào bảng orders (thay thế cho order + orderitem)
            $quantity = $product['quantity'] ?? 1;
            $stmt = $conn->prepare("INSERT INTO orders (customerId, productId, quantity, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $customerId, $productId, $quantity, $status);
            $stmt->execute();
            
            // Lưu orderId của sản phẩm đầu tiên để dùng cho invoice
            if ($firstOrderId === null) {
                $firstOrderId = $conn->insert_id;
            }
            
            $stmt->close();
        }
        
        // Thêm vào bảng invoice nếu có orderId
        if ($firstOrderId !== null) {
            $paymentMethod = $order_data['paymentMethod'] ?? 'COD';
            $amount = $order_data['total'] ?? 0;
            $paymentStatus = ($paymentMethod == 'COD') ? 'chưa thanh toán' : 'đã thanh toán';
            
            $stmt = $conn->prepare("INSERT INTO invoice (orderId, paymentMethod, amount, paymentStatus) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $firstOrderId, $paymentMethod, $amount, $paymentStatus);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Đơn hàng đã được lưu thành công',
            'orderId' => $firstOrderId
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
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
