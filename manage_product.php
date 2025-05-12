<?php
    require_once("./BE/db.php");
    session_start();
    $user = '';
    $pass = '';
    $error = '';

$conn = create_connection();

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_sql = "DELETE FROM product WHERE productId = '$delete_id'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Product deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting product!');</script>";
    }
}

// Get all products
$sql = "SELECT product.*, category.name as category_name 
        FROM product 
        JOIN category ON product.categoryId = category.categoryId";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - COMPUTER SHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <style>
        .page-top, header {
            color: whitesmoke;
        }
        body {
            background-color: rgb(243, 243, 243);
        }
        .product-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body style="padding-top: 100px;">
    <div class="fixed-top">
        <!-- Header -->
        <header class="bg-white shadow-sm py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3 text-center text-md-left mb-2 mb-md-0">
                        <a href="index.php"> <img width="150px" src="asset/images/Screenshot 2025-04-23 000327.png" alt="" class="img-fluid"></a>
                    </div>
                    <div class="col-12 col-md-6 text-center">
                        <h2>Product Management</h2>
                    </div>
                    <div class="col-12 col-md-3 text-center text-md-right">
                        <a href="admin.php" class="btn btn-primary">Back to Home</a>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <div class="container mt-5">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['productId']) ?></td>
                                <td>
                                    <img src="asset/productImg/<?= htmlspecialchars($row['image']) ?>" 
                                         alt="Product Image" 
                                         class="product-image">
                                </td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td>$<?= htmlspecialchars($row['price']) ?></td>
                                <td><?= htmlspecialchars($row['stockQuantity']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <a href="manage_product.php?delete_id=<?= $row['productId'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Xác Nhận Xóa Sản Phẩm')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 