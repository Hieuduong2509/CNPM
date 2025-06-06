<?php 
    session_start();
    require_once("./BE/db.php");
    require_once("./BE/product.php");
    $conn = create_connection();
    if($conn->connect_error) {
        die("fail to connect" . $conn->connect_error);
    }

    if(isset($_SESSION["username"])) {
      $username = $_SESSION["username"];
    } else {
      $username = "Tài Khoản";
    }

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPUTER SHOP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="asset/css/style.css">
    <script src="main.js"></script>
    <style>
        .page-top, header {
            color: whitesmoke;
        }
        body {
            background-color: rgb(243, 243, 243);
        }
    </style>
</head>
<body style="padding-top: 100px;">
<div class="fixed-top">
<!-- Top Contact -->
<section class="page-top py-2 bg-dark text-white ">
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

<?php if (isset($_SESSION['username'])): ?>
  <li class="list-inline-item">
    <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
  </li>
<?php endif; ?>
                    <li class="list-inline-item"><a href="cart.php" class="text-dark"><i class="fa-solid fa-cart-shopping"></i> <span class="dot-cart">0</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Navigation Menu -->
<!-- NAVBAR -->
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
  <div class="container">
    <!-- Mobile toggle -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Category toggle button -->
    <button class="btn  ml-auto text-white" type="button" data-toggle="collapse" data-target="#cate">
      DANH MỤC SẢN PHẨM
    </button>

    <!-- Main menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto header-top-user_cart">
        
        <li class="nav-item"><a class="nav-link" href="productShow.php?search=laptop">Laptop</a></li>
        <li class="nav-item"><a class="nav-link" href="productShow.php?search=PC">PC gaming</a></li>
        <li class="nav-item"><a class="nav-link" href="productShow.php?search=monitor">Màn hình</a></li>
        <li class="nav-item"><a class="nav-link" href="about.html">Giới thiệu</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- CATEGORY GRID -->
<div class="container collapse" id="cate">
  <div class="row">
    <?php 
      require_once("./BE/db.php");
      $conn = create_connection();
      $sql = "SELECT * FROM category";
      $category = $conn->query($sql);

      if ($category && $category->num_rows > 0) {
          while ($row = $category->fetch_assoc()) { ?>
            <div class="col-6 col-md-3 col-lg-2 mb-3">
              <a href="productShow.php?search=<?=$row['name']?>">
              <div class="category-box text-white text-center py-2 px-2 rounded shadow-sm border">
                <?= htmlspecialchars($row['name']) ?>
              </div>
              </a>
            </div>
    <?php }
      } else {
        echo '<div class="col-12 text-danger">Không có danh mục!</div>';
      }
    ?>
  </div>
</div>



</div>

<!-- Slider -->
<section class="section-slider py-5 mt-5 pt-5">
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
<section class="hot-product py-5">
    <div class="container">
        <div class="row">
            <?php 
                $products = get_products();
                foreach ($products as $p) {
                    if ($p['popular'] === '1') {
                        $pID = $p['productId'];
                        $pname = $p['name'];
                        $des = $p['description'];
                        $price = $p['price'];
                        $stock = $p['stockQuantity'];    
                        $img = $p['image'];  
            ?>
            
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-4">
              <a href="productDetail.php?productId=<?= $pID ?>" class="text-decoration-none text-dark">
                  <div class="card h-100">
                      <img class="card-img-top img-fluid" src="asset/productImg/<?=$img?>" alt="Image">
                      <div class="card-body">
                          <h5 class="card-title"><?= $pname ?></h5>
                          <h6 class="card-subtitle mb-2 text-muted">$<?= $price ?></h6>
                          <p class="card-text description"><?= $des ?></p>
                          <p class="card-text stock"><small class="text-muted">Stock: <?= $stock ?></small></p>
                      </div>
                  </div>
              </a>
          </div>
            <?php } } ?>
        </div>
    </div>
</section>
<section class="py-5 bg-light">
  <div class="container">
    <div class="row text-center">
      <!-- Feature 1 -->
      <div class="col-12 col-md-3 mb-4">
        <i class="fa-solid fa-truck-fast fa-2x text-primary mb-2"></i>
        <h5>Giao hàng nhanh</h5>
        <p class="text-muted">Miễn phí giao hàng toàn quốc trong 24h.</p>
      </div>
      <!-- Feature 2 -->
      <div class="col-12 col-md-3 mb-4">
        <i class="fa-solid fa-shield-halved fa-2x text-primary mb-2"></i>
        <h5>Bảo hành uy tín</h5>
        <p class="text-muted">Bảo hành 1 đổi 1 trong 12 tháng đầu.</p>
      </div>
      <!-- Feature 3 -->
      <div class="col-12 col-md-3 mb-4">
        <i class="fa-solid fa-headset fa-2x text-primary mb-2"></i>
        <h5>Hỗ trợ 24/7</h5>
        <p class="text-muted">Tư vấn kỹ thuật và hỗ trợ khách hàng mọi lúc.</p>
      </div>
      <!-- Feature 4 -->
      <div class="col-12 col-md-3 mb-4">
        <i class="fa-solid fa-tags fa-2x text-primary mb-2"></i>
        <h5>Giá tốt mỗi ngày</h5>
        <p class="text-muted">Luôn có chương trình khuyến mãi hấp dẫn.</p>
      </div>
    </div>
  </div>
</section>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- swiper js -->
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
                // progressContent.textContent = `${Math.ceil(time / 1000)}s`;
            }
        }
    });
</script>
<script>
// Hàm cập nhật số lượng sản phẩm trong giỏ hàng
function updateCartCount() {
    // Lấy username hiện tại
    const username = <?= json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : "guest") ?>;

    const cartStorageKey = 'cartItems_' + username;
    
    // Lấy số lượng sản phẩm trong giỏ hàng từ localStorage với key tương ứng với user
    const cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
    
    // Tính tổng số lượng sản phẩm
    const totalQuantity = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);

    // Cập nhật số lượng hiển thị bên cạnh biểu tượng giỏ hàng
    const cartQuantityElement = document.querySelector('.dot-cart');
    if (cartQuantityElement) {
        cartQuantityElement.textContent = totalQuantity;
    }
    
    console.log(`[${new Date().toLocaleTimeString()}] Cập nhật số lượng giỏ hàng: ${totalQuantity}`);
    return totalQuantity;
}

document.addEventListener('DOMContentLoaded', function() {
    // Cập nhật số lượng giỏ hàng khi trang được tải
    updateCartCount();
    
    // Kiểm tra xem giỏ hàng vừa được cập nhật từ trang submit_order không
    if (localStorage.getItem('cart_just_updated') === 'true') {
        console.log('Phát hiện cập nhật giỏ hàng mới!');
        // Xóa cờ hiệu
        localStorage.removeItem('cart_just_updated');
        
        // Cập nhật số lượng giỏ hàng lần nữa để đảm bảo hiển thị đúng
        setTimeout(function() {
            const count = updateCartCount();
            console.log('Số lượng giỏ hàng sau khi cập nhật:', count);
        }, 100);
    }
    
    // Kiểm tra giá trị trong localStorage
    const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
    const cartStorageKey = 'cartItems_' + username;
    console.log('Giỏ hàng hiện tại:', JSON.parse(localStorage.getItem(cartStorageKey) || '[]'));
    
    // Cập nhật số lượng sau mỗi 3 giây để đồng bộ với các thay đổi có thể xảy ra từ các tab khác
    setInterval(updateCartCount, 3000);
});
</script>
</body>
<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-3 mt-5">
  <div class="container">
    <div class="row">
      <!-- About Us -->
      <div class="col-md-4">
        <h5>About Us</h5>
        <p>We offer top-quality computers, laptops, and accessories. Trusted by thousands of tech enthusiasts.</p>
      </div>

      <!-- Quick Links -->
      <div class="col-md-4">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="index.php" class="text-white">Home</a></li>
          <li><a href="products.php" class="text-white">Products</a></li>
          <li><a href="contact.html" class="text-white">Contact</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div class="col-md-4">
        <h5>Contact Us</h5>
        <p><i class="fa-solid fa-phone-volume"></i> +123 456 7890</p>
        <p><i class="fa-solid fa-envelope"></i> support@computershop.com</p>
        <p><i class="fa-solid fa-map-location-dot"></i> 131, Dao Cam Moc Street, Dis 8, HCM city</p>
      </div>
    </div>
    



    <hr class="bg-light">

    <div class="text-center">
      &copy; <?php echo date("Y"); ?> Computer Shop. All rights reserved.
    </div>
  </div>
</footer>
<!-- Scroll to Top Button -->
<button onclick="topFunction()" id="scrollTopBtn" class="btn btn-primary" style="font-weight: bolder; ">
  ↑
</button>

</html>
