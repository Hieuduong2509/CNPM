<?php
require_once("./BE/db.php"); // Your DB connection function

$categoryMap = [
    'laptop' => 1,
    'Mouse' => 2,
    'Keyboard' => 3,
    'PC'=>4
];

$conn = create_connection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$json = file_get_contents('notedata.json');
$products = json_decode($json, true);

foreach ($products as $product) {
    $name = $product['title'];
    $description = $product['description'] ?? '';
    $price = isset($product['price']) ? floatval($product['price']) : 0;
    $stockQuantity = isset($product['stockQuantity']) ? intval($product['stockQuantity']) : 0;
    $image = $product['image'] ?? '';
    $popular = isset($product['popular']) ? intval($product['popular']) : 0;
    

    // Category conversion
    $categoryId = 0;
    if (isset($product['category'])) {
        $rawCategory = $product['category'];
        if (is_numeric($rawCategory)) {
            $categoryId = intval($rawCategory);
        } elseif (isset($categoryMap[$rawCategory])) {
            $categoryId = $categoryMap[$rawCategory];
        }
    }

    // Prevent duplicates (optional: skip if exists)
    $stmt_check = $conn->prepare("SELECT productId FROM product WHERE name = ?");
    $stmt_check->bind_param("s", $name);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO product (name, description, price, stockQuantity, categoryId, popular, image )
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiis", $name, $description, $price, $stockQuantity, $categoryId, $popular, $image);
        
        if ($stmt->execute()) {
            echo "Inserted: $name\n";
        } else {
            echo "Failed to insert $name: " . $stmt->error . "\n";
        }

        $stmt->close();
    } else {
        echo "Skipped (already exists): $name\n";
    }

    $stmt_check->close();
}

$conn->close();
?>
