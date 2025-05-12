<?php 
    session_start();
    require_once("./BE/db.php");
    require_once("./BE/product.php");
    $conn = create_connection();
    if($conn->connect_error) {
        die("fail to connect" . $conn->connect_error);
    }
    
    if(isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT name FROM customer WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $username = $row['name'];
        }
        $stmt->close();
    } else {
        $username = 'Tài Khoản';
    }
    $conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giỏ hàng | GIANG-HIEUKY</title>
  <link rel="stylesheet" href="cart.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="asset/css/cart.css">  
</head>
<body>
  <header class="navbar">
    <div class="logo"><a href="index.php">🖥️ GIANG-HIEUKY</a></div>
    <div class="nav-right">
      <a href="index.php">Trang chủ</a>
      <a href="cart.php" class="active">Giỏ hàng</a>
      <?php if (isset($_SESSION['username'])): ?>
        <span class="text-muted"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($username) ?></span>
      <?php else: ?>
        <a href="account.php"><i class="fa-solid fa-user"></i> Tài Khoản</a>
      <?php endif; ?>
    </div>
  </header>

  <section class="cart-container">
    <h1>🛒 Giỏ hàng của bạn</h1>
    <?php if (!isset($_SESSION['username'])): ?>
      <div class="login-message">
        Vui lòng <a href="account.php">đăng nhập</a> để xem giỏ hàng của bạn.
      </div>
    <?php else: ?>
      <div id="cartContent">
        <div class="cart-header">
          <span>Sản phẩm</span>
          <span>Đơn giá</span>
          <span>Số lượng</span>
          <span>Thành tiền</span>
          <span>Thao tác</span>
        </div>

        <form action="submit_order.php" method="POST" id="cartForm">
          <div id="cartItems">
            <!-- Cart items will be dynamically added here -->
          </div>

          <hr>
          <div class="cart-footer">
            <div class="right">
              <span>Tổng cộng: <strong class="total">₫0</strong></span>
              <button type="submit" class="checkout">Mua hàng</button>
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </section>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    // Function to update cart quantity display throughout the app
    function updateCartQuantity() {
      const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
      const cartStorageKey = 'cartItems_' + username;
      const cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
      const totalQuantity = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
      const cartQuantityElements = document.querySelectorAll('.dot-cart');
      cartQuantityElements.forEach(element => {
        element.textContent = totalQuantity;
      });
      return totalQuantity;
    }
    
    // Standard function for updating cart count - consistent across all pages
    function updateCartCount() {
      return updateCartQuantity();
    }
    
    $(document).ready(function() {
      <?php if (isset($_SESSION['username'])): ?>
        // Load cart items from localStorage with user-specific key
        const username = '<?= $_SESSION['username'] ?>';
        const cartStorageKey = 'cartItems_' + username;
        const cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
        
        if (cartItems.length === 0) {
          $('#cartItems').html('<div class="text-center py-4">Giỏ hàng của bạn đang trống</div>');
          return;
        }

        // Display cart items
        cartItems.forEach((item) => {
          const cartItem = `
            <div class="cart-item">
              <div class="product-info">
                <input type="checkbox" name="productId[]" value="${item.id}">
                <img src="asset/productImg/${item.image}" alt="${item.name}">
                <div>
                  <p>${item.name}</p>
                  <small>Mô tả: ${item.description}</small><br>
                  <small>Tồn kho: ${item.stockQuantity}</small>
                  <input type="hidden" name="productName[]" value="${item.name}">
                  <input type="hidden" name="price[]" value="${item.price}">
                  <input type="hidden" name="categoryId[]" value="${item.categoryId}">
                  <input type="hidden" name="description[]" value="${item.description}">
                  <input type="hidden" name="stockQuantity[]" value="${item.stockQuantity}">
                </div>
              </div>
              <div>₫${parseInt(item.price).toLocaleString()}</div>
              <div class="quantity">
                <button type="button" onclick="changeQty(${item.id}, -1)">-</button>
                <input type="text" name="quantity[]" value="${item.quantity}" id="qty-${item.id}" readonly>
                <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
              </div>
              <div class="subtotal">₫${(parseInt(item.price) * item.quantity).toLocaleString()}</div>
              <div><button type="button" class="remove-btn" onclick="removeProduct(${item.id})">Xóa</button></div>
            </div>
          `;
          $('#cartItems').append(cartItem);
        });

        // Initial total update
        updateTotal();
        
        // Make sure cart quantity is up-to-date
        updateCartQuantity();
      <?php endif; ?>
    });

    function updateTotal() {
      let total = 0;
      document.querySelectorAll('input[name="productId[]"]:checked').forEach(function(checkbox) {
        let qty = parseInt(checkbox.closest('.cart-item').querySelector('input[name="quantity[]"]').value);
        let price = parseInt(checkbox.closest('.cart-item').querySelector('input[name="price[]"]').value);
        total += price * qty;
      });
      document.querySelector('.total').textContent = '₫' + total.toLocaleString();
    }
  
    function changeQty(productId, amount) {
      let qtyInput = document.getElementById('qty-' + productId);
      let currentQty = parseInt(qtyInput.value);
      if (!isNaN(currentQty)) {
        const newQty = Math.max(1, currentQty + amount);
        qtyInput.value = newQty;
        updateSubtotal(productId);
        
        // Use user-specific storage key
        const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
        const cartStorageKey = 'cartItems_' + username;
        let cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
        
        cartItems = cartItems.map(item => {
          if (item.id == productId) {
            item.quantity = newQty;
          }
          return item;
        });
        localStorage.setItem(cartStorageKey, JSON.stringify(cartItems));
        // Update cart quantity display
        updateCartQuantity();
      }
    }

    function updateSubtotal(productId) {
      let checkbox = document.querySelector('input[name="productId[]"][value="' + productId + '"]');
      let qty = parseInt(document.getElementById('qty-' + productId).value);
      let price = parseInt(checkbox.closest('.cart-item').querySelector('input[name="price[]"]').value);
      let subtotalElement = checkbox.closest('.cart-item').querySelector('.subtotal');
      subtotalElement.textContent = '₫' + (price * qty).toLocaleString();
      updateTotal();
    }
  
    function removeProduct(productId) {
      if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        // Get current user
        const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
        const cartStorageKey = 'cartItems_' + username;
        
        // Lấy danh sách sản phẩm từ localStorage
        let cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
        console.log('Before removal:', cartItems);
        
        // Convert productId to number to ensure consistent comparison
        const productIdNum = parseInt(productId);
        
        // Lọc bỏ sản phẩm có productId cần xóa, sử dụng loose equality (==)
        cartItems = cartItems.filter(item => parseInt(item.id) != productIdNum);
        console.log('After removal:', cartItems);
        
        // Cập nhật lại localStorage
        localStorage.setItem(cartStorageKey, JSON.stringify(cartItems));
        console.log('Updated localStorage:', JSON.parse(localStorage.getItem(cartStorageKey)));
        
        // Xóa phần tử khỏi DOM
        const cartItem = document.querySelector(`.cart-item input[value="${productId}"]`).closest('.cart-item');
        cartItem.remove();
        
        // Cập nhật tổng tiền
        updateTotal();
        
        // Cập nhật số lượng giỏ hàng
        updateCartQuantity();
        
        // Nếu không còn sản phẩm nào, hiển thị thông báo
        if (cartItems.length === 0) {
          $('#cartItems').html('<div class="text-center py-4">Giỏ hàng của bạn đang trống</div>');
        }
      }
    }
  
    function generateQRCode() {
      const qrContainer = $('#qr-container');
      qrContainer.empty(); 
  
      const randomCode = "PAY_" + Math.random().toString(36).substring(2, 10).toUpperCase();
      const qrDiv = $('<div id="qrcode"></div>');
      qrContainer.append(qrDiv);
  
      new QRCode(document.getElementById("qrcode"), {
        text: randomCode,
        width: 128,
        height: 128
      });
    }
  
    $(document).ready(function () {
      $('input[name="paymentMethod"]').on('change', function () {
        $('#qr-container').empty();
        $('#e-wallet-container').empty();
        if (this.value === 'QR') {
          generateQRCode();
        }
        if (this.value === 'e-wallet') {
          generateBankCards();
        }
      });

      // Add event listeners for product checkboxes
      $(document).on('change', 'input[name="productId[]"]', function () {
        updateSubtotal(this.value);
        updateTotal();
      });

      $('form').on('submit', function (e) {
        e.preventDefault();
        
        // Get selected products
        const selectedProducts = [];
        $('input[name="productId[]"]:checked').each(function() {
          const item = $(this).closest('.cart-item');
          selectedProducts.push({
            id: $(this).val(),
            name: item.find('input[name="productName[]"]').val(),
            price: item.find('input[name="price[]"]').val(),
            quantity: item.find('input[name="quantity[]"]').val(),
            categoryId: item.find('input[name="categoryId[]"]').val(),
            image: item.find('img').attr('src').split('/').pop(),
            description: item.find('input[name="description[]"]').val(),
            stockQuantity: item.find('input[name="stockQuantity[]"]').val()
          });
        });
        
        if (selectedProducts.length === 0) {
          alert('Vui lòng chọn ít nhất một sản phẩm để mua');
          return;
        }
        
        // Lưu lại danh sách ID sản phẩm đã chọn để sau này có thể xóa ra khỏi giỏ hàng
        const selectedIds = selectedProducts.map(p => p.id);
        
        // Store selected products in localStorage with user-specific key
        const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
        localStorage.setItem('selectedProducts_' + username, JSON.stringify(selectedProducts));
        localStorage.setItem('selectedProductIds_' + username, JSON.stringify(selectedIds));
        
        // Đánh dấu là đang chuẩn bị đặt hàng
        localStorage.setItem('preparing_order', 'true');
        
        // Submit the form to proceed to checkout
        window.location.href = 'submit_order.php';
      });

      // Initial total update
      updateTotal();
    });
  </script>
</body>
</html>