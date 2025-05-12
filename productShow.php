<?php
session_start();
    require_once("./BE/db.php");
    require_once("./BE/product.php");
    $conn = create_connection();
    if($conn->connect_error) {
        die("fail to connect" . $conn->connect_error);
    }

    if(isset($_SESSION['username'])) {
      $user = $_SESSION['username'];
    } else {
      $user = 'Tài Khoản';}

    $stmt = $conn->prepare("SELECT name FROM customer WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $stmt->close();
      $conn->close();
    
    
    if(isset($_SESSION['username'])){
      $username = $row['name'];
    }else{
      $username='Tài Khoản';
    }}
require_once('./BE/db.php');

$conn=create_connection();
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$searchTerm = mysqli_real_escape_string($conn, $searchTerm); // sanitize

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIANG-HIEUKY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="asset/css/style.css">
    <style>
        .page-top, header {
            color: whitesmoke;
        }
        body {
            background-color: rgb(243, 243, 243);
        }
    </style>
</head>
<body>

<!-- Top Contact -->
<section class="page-top py-2 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col text-center text-md-left">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item mr-3"><i class="fa-solid fa-phone-volume"></i> <a href="tel:0905379388" class="text-white">0905379388</a></li>
                    <li class="list-inline-item"><i class="fa-solid fa-envelope"></i> <a href="contact.html" class="text-white">LIÊN HỆ</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Header -->
<header class="bg-white shadow-sm py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-md-3 text-center text-md-left mb-2 mb-md-0">
               <a href="index.php"> <img width="150px" src="asset/images/Screenshot 2025-04-23 000327.png" alt="" class="img-fluid" ></a>
            </div>
            <!-- search bar -->
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <form action="productShow.php" method="get">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Nhập sản phẩm cần tìm...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Tìm kiếm <i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- account and cart -->
            <div class="col-12 col-md-3 text-center text-md-right text-dark">
                <ul class="list-inline mb-0">
<li class="list-inline-item">
  <?php if (isset($_SESSION['username'])): ?>
    <span class="text-muted"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($username) ?></span>
  <?php else: ?>
    <a href="account.php"><i class="fa-solid fa-user"></i> Tài Khoản</a>
  <?php endif; ?>
</li>
                    <li class="list-inline-item "><i class="fa-solid fa-cart-shopping"></i> <span class="dot-cart">0</span></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Navigation Menu -->
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-bars"></i> DANH MỤC SẢN PHẨM</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"> </span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto justify-content-center">
                <li class="nav-item"><a class="nav-link" href="#">Xây dựng cấu hình pc</a></li>
                <li class="nav-item"><a class="nav-link" href="#">PC gaming</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Màn hình</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Laptop</a></li>
                <li class="nav-item"><a class="nav-link" href="#">PC văn phòng</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Khuyến mãi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Laptop MSI</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Giới thiệu</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Slider -->
<section class="section-slider py-3">
    <div class="container">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/30-4-trang-chu-pc.jpg" alt=""></div>
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/banner-core-ultra.jpg" alt=""></div>
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/banner-trang-chu-build-pc-30-4.jpg" alt=""></div>
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/banner-trang-chu-tang-game.jpg" alt=""></div>
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/banner-vga-rtx-5080-1.jpg" alt=""></div>
                <div class="swiper-slide"><img class="img-fluid w-100" src="asset/images/WEB_BANNERTRANGCHUPCGAMINGGIATU8TR.jpg" alt=""></div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <div class="autoplay-progress">
                <svg viewBox=""></svg>
                <span></span>
            </div>
        </div>
    </div>
</section>

<!-- Popular Products -->
<div class="container">
    <div class="row ">

<?php
$sql = "SELECT product.* FROM product JOIN category ON product.categoryId = category.categoryId WHERE product.name LIKE '%$searchTerm%' OR category.name LIKE '%$searchTerm%'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pname = $row['name'];
        $price = $row['price'];
        $des = $row['description'];
        $stock = $row['stockQuantity'];
        $img = $row['image']; // make sure DB has image paths
?>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-2 py-3 px-1">
                <div class="card h-100 border" onclick="">
                    <img class="card-img-top" src="asset/productImg/<?=$img?>" alt="Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= $pname ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">$<?= $price ?></h6>
                        <p class="card-text"><?= $des ?></p>
                        <p class="card-text"><small class="text-muted">Stock: <?=$stock ?></small></p>
                    </div>
                </div>
            </div>
<?php
    }
} else {
    echo "<p>No results  for '$searchTerm'</p>";
}
?>
</div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const progressCircle = document.querySelector(".autoplay-progress svg");
    const progressContent = document.querySelector(".autoplay-progress span");
    var swiper = new Swiper(".mySwiper", {
        spaceBetween: 30,
        centeredSlides: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },
        on: {
            autoplayTimeLeft(s, time, progress) {
                progressCircle.style.setProperty("--progress", 1 - progress);
                progressContent.textContent = `${Math.ceil(time / 1000)}s`;
            }
        }
    });
</script>
</body>
</html>
