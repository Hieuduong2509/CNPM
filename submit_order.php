<?php 
    session_start();
    require_once("./BE/db.php");
    require_once("./BE/product.php");
    $conn = create_connection();
    if($conn->connect_error) {
        die("fail to connect" . $conn->connect_error);
    }

    $username = $_SESSION['username'];

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
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giỏ hàng | GIANG-HIEUKY</title>
  <link rel="stylesheet" href="cart.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="asset/css/submit_order.css">
</head>
<body>
  <header class="navbar">
    <div class="logo"><a href="index.php">🖥️ GIANG-HIEUKY</a></div>
    <div class="nav-right">
      <a href="index.php">Trang chủ</a>
      <?php if (isset($_SESSION['username'])): ?>
        <span class="text-muted"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($username) ?></span>
      <?php else: ?>
        <a href="account.php"><i class="fa-solid fa-user"></i> Tài Khoản</a>
      <?php endif; ?>
    </div>
  </header>
  <section class="cart-container">
    <h1>🛒 Giỏ hàng của bạn</h1>
    <div class="cart-header">
      <span>Sản phẩm</span>
      <span>Đơn giá</span>
      <span>Số lượng</span>
      <span>Thành tiền</span>
      <span>Thao tác</span>
    </div>

    <form action="submit_order.php" method="POST">
      <div class="cart-item">
        <div class="product-info">
          <img src="${product.image}" alt="">
          <div>
            <p>Laptop Asus VivoBook S14 S433EA-EK216T</p>
            <small>Phân loại: <input type="text" name="category" value="Laptop" readonly></small>
            <input type="hidden" name="productName" value="Laptop Asus VivoBook S14 S433EA-EK216T">
            <input type="hidden" name="price" value="100000000">
          </div>
        </div> 
        <div>₫100.000.000</div>
        <div class="quantity">
          <button type="button" onclick="changeQty(1, -1)">-</button>
          <button type="button" onclick="changeQty(1, 1)">+</button>
        </div>
        <div class="subtotal">₫100.000.000</div>
        <div><button type="button" class="remove-btn" onclick="removeProduct(1)">Xóa</button></div>
      </div>

      <div class="cart-item">
        <div class="product-info">
          <img src="${product.image}" alt="">
          <div>
            <p>Laptop Dell Inspiron 15 3000</p>
            <small>Phân loại: <input type="text" name="category" value="Laptop" readonly></small>
            <input type="hidden" name="productName" value="Laptop Dell Inspiron 15 3000">
            <input type="hidden" name="price" value="150000000">
          </div>
        </div>
        <div>₫150.000.000</div>
        <div class="quantity">
          <button type="button" onclick="changeQty(2, -1)">-</button>
          <input type="text" name="quantity" value="1" id="qty-2">
          <button type="button" onclick="changeQty(2, 1)">+</button>
        </div>
        <div class="subtotal">₫150.000.000</div>
        <div><button type="button" class="remove-btn" onclick="removeProduct(2)">Xóa</button></div>
      </div>

      <hr>
      <div style="margin-top: 20px;">
        <h4>Phương thức thanh toán</h4>
        <label><input type="radio" name="paymentMethod" value="COD" checked> Thanh toán khi nhận hàng</label><br>
        <label><input type="radio" name="paymentMethod" value="e-wallet">Thẻ Ngân Hàng</label><br>
        <div id="e-wallet-container" style="margin-top: 10px;"></div>
        <label><input type="radio" name="paymentMethod" value="QR"> Trả trước (QR)</label>
        <div id="qr-container" style="margin-top: 10px;"></div>

      </div>

      <div class="cart-footer">
        <input type="hidden" name="customerId" value="1"> 
        <div class="right">
          <span>Tổng cộng: <strong class="total">₫0</strong></span>
          <button type="submit" class="checkout">Đặt Hàng</button>
        </div>
      </div>
    </form>
  </section>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

  <script>
    $(document).ready(function() {
      // Get selected products from localStorage with user-specific key
      const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
      const storageKey = 'selectedProducts_' + username;
      const selectedProducts = JSON.parse(localStorage.getItem(storageKey) || '[]');
      console.log('Selected products loaded from localStorage for user:', username, selectedProducts);
      
      // Clear existing cart items
      $('.cart-item').remove();
      
      if (selectedProducts.length === 0) {
        console.warn('No selected products found in localStorage');
        $('.cart-header').after('<div class="text-center py-4">Không có sản phẩm nào được chọn</div>');
        return;
      }
      
      // Add selected products to the cart
      selectedProducts.forEach((product, index) => {
        console.log(`Processing selected product ${index}:`, product);
        
        // Ensure all required properties exist
        const productName = product.name || 'Không có tên';
        const productPrice = product.price || 0;
        const productQty = product.quantity || 1;
        const productCat = product.categoryId || 'Không xác định';
        const productImg = product.image || '';
        
        const cartItem = `
          <div class="cart-item">
            <div class="product-info">
              <img src="asset/productImg/${productImg}" alt="${productName}">
              <div>
                <p>${productName}</p>
                <small>Phân loại: <input type="text" name="category[]" value="${productCat}" readonly></small>
                <input type="hidden" name="productName[]" value="${productName}">
                <input type="hidden" name="price[]" value="${productPrice}">
                <input type="hidden" name="quantity[]" value="${productQty}">
                <input type="hidden" name="image[]" value="${productImg}">
              </div>
            </div>
            <div>₫${parseInt(productPrice).toLocaleString()}</div>
            <div class="quantity">
              <input type="text" name="quantity_display" value="${productQty}" readonly>
            </div>
            <div class="subtotal">₫${(parseInt(productPrice) * parseInt(productQty)).toLocaleString()}</div>
            <div><button type="button" class="remove-btn" disabled>Xóa</button></div>
          </div>
        `;
        $('form').prepend(cartItem);
      });

      // Calculate and display total
      let total = 0;
      selectedProducts.forEach(product => {
        total += parseInt(product.price || 0) * parseInt(product.quantity || 1);
      });
      $('.total').text('₫' + total.toLocaleString());
      console.log('Total price calculated:', total);
    });

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
  
    function generateBankCards() {
      const eWalletContainer = $('#e-wallet-container');
      eWalletContainer.empty();

      const cards = [
        { amount: '₫50000', bank: 'Nam A Bank', description: 'Đơn từ 299.000₫ với thẻ tín dụng Nam A Bank JCB', color: '#f39c12' },
        { amount: '₫50000', bank: 'Shinhan Finance', description: 'Đơn từ 4400.000₫ - Mỗi ngày với thẻ Mastercard', color: '#3498db' },
        { amount: '₫100000', bank: 'LPBank', description: 'Đơn từ 500.000₫ - Mỗi ngày', color: '#d35400' },
        { amount: '₫50000', bank: 'ACB', description: 'Đơn từ 500.000₫ - Mỗi ngày với thẻ tín dụng ACB', color: '#2980b9' },
        { amount: '₫30000', bank: 'SHB', description: 'Đơn từ 200.000₫ với thẻ SHB Visa debit', color: '#e67e22' },
        { amount: '₫30000', bank: 'SHB', description: 'Đơn từ 200.000₫ với thẻ SHB Mastercard debit', color: '#e67e22' },
      ];
      cards.forEach(card => {
        const cardDiv = $('<div></div>').css({
          background: card.color,
          padding: '10px',
          margin: '5px',
          borderRadius: '5px',
          color: 'white',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          cursor: 'pointer'
        }).click(function() {
          eWalletContainer.empty();
          eWalletContainer.append(cardDiv.clone());
        });

        const contentDiv = $('<div></div>').css({
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center'
        });

        const amountSpan = $('<span></span>').text(card.amount + ' Giảm').css({
          fontWeight: 'bold',
          fontSize: '16px'
        });
        const descSpan = $('<span></span>').text(card.description).css({
          fontSize: '12px'
        });

        const bankLogo = $('<div></div>').text(card.bank).css({
          fontSize: '12px',
          fontWeight: 'bold',
          background: 'white',
          color: card.color,
          padding: '5px',
          borderRadius: '3px'
        });

        contentDiv.append(amountSpan, descSpan);
        cardDiv.append(contentDiv, bankLogo);
        eWalletContainer.append(cardDiv);
      });
    }
  
    $(document).ready(function () {
      $('input[name="paymentMethod"]').on('change', function () {
        $('#qr-container').empty();
        $('#e-wallet-container').empty();
        if (this.value === 'QR') {
          generateQRCode();
        } else if (this.value === 'e-wallet') {
          generateBankCards();
        }
      });
  
      $('form').on('submit', function (e) {
        try {
          e.preventDefault();
          console.log('Form submitted');
          
          // Get form data
          const formData = new FormData(this);
          const paymentMethod = $('input[name="paymentMethod"]:checked').val() || 'COD';
          console.log('Payment method:', paymentMethod);
          
          // Get all product data
          const productData = [];
          const productNames = document.querySelectorAll('input[name="productName[]"]');
          const prices = document.querySelectorAll('input[name="price[]"]');
          const quantities = document.querySelectorAll('input[name="quantity[]"]');
          const categories = document.querySelectorAll('input[name="category[]"]');
          const images = document.querySelectorAll('input[name="image[]"]');
          
          console.log('Product elements found:', 
            'Names:', productNames.length, 
            'Prices:', prices.length, 
            'Quantities:', quantities.length, 
            'Categories:', categories.length,
            'Images:', images.length
          );
          
          if (productNames.length === 0) {
            console.error('No product data found in form');
            alert('Không có sản phẩm nào để đặt hàng. Vui lòng chọn sản phẩm.');
            return;
          }
          
          // Lưu lại ID của các sản phẩm để sau này xóa khỏi giỏ hàng
          const productIds = [];
          
          for (let i = 0; i < productNames.length; i++) {
            const productInfo = {
              product_name: productNames[i].value || 'Không có tên',
              price: prices[i].value || '0',
              quantity: quantities[i].value || '1',
              category: categories[i].value || 'Không xác định',
              image: images[i] ? images[i].value : ''
            };
            productData.push(productInfo);
            
            // Nếu có id sản phẩm, lưu lại để xóa khỏi giỏ hàng
            if (images[i] && images[i].dataset && images[i].dataset.productId) {
              productIds.push(images[i].dataset.productId);
            }
          }
          
          console.log('Product data:', productData);
          console.log('Product IDs to remove from cart:', productIds);
          
          // Create order object
          const orderTotal = parseInt($('.total').text().replace(/[^\d]/g, '') || '0');
          const customerName = '<?= htmlspecialchars($username) ?>' || 'Khách hàng';
          
          const orderData = {
            customer_name: customerName,
            products: productData,
            paymentMethod: paymentMethod,
            total: orderTotal,
            created_at: new Date().toLocaleString(),
            status: paymentMethod === 'COD' ? 'đồng ý' : 'đang chờ xác nhận'
          };
          
          console.log('Order data to be saved:', orderData);
          
          // Save to localStorage
          let orders = [];
          try {
            orders = JSON.parse(localStorage.getItem('orders') || '[]');
            if (!Array.isArray(orders)) {
              console.error('Orders in localStorage is not an array, resetting');
              orders = [];
            }
          } catch (parseError) {
            console.error('Error parsing orders from localStorage:', parseError);
            orders = [];
          }
          
          orders.push(orderData);
          localStorage.setItem('orders', JSON.stringify(orders));
          console.log('Orders saved to localStorage. Current orders:', orders);
          
          // Save user-specific orders
          const username = '<?= isset($_SESSION['username']) ? $_SESSION['username'] : "guest" ?>';
          const userOrdersKey = 'orders_' + username;
          let userOrders = [];
          try {
            userOrders = JSON.parse(localStorage.getItem(userOrdersKey) || '[]');
            if (!Array.isArray(userOrders)) {
              userOrders = [];
            }
          } catch (error) {
            userOrders = [];
          }
          userOrders.push(orderData);
          localStorage.setItem(userOrdersKey, JSON.stringify(userOrders));
          
          // Xóa sản phẩm đã đặt khỏi giỏ hàng
          localStorage.removeItem('selectedProducts_' + username);
          
          // Lấy danh sách ID sản phẩm đã chọn trực tiếp từ localStorage (đã lưu từ cart.php)
          const selectedProductIds = JSON.parse(localStorage.getItem('selectedProductIds_' + username) || '[]');
          console.log('selectedProductIds from localStorage:', selectedProductIds);
          
          // Xóa sản phẩm đã đặt hàng khỏi giỏ hàng
          const cartStorageKey = 'cartItems_' + username;
          const cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
          console.log('Current cart items:', cartItems);
          
          // Lọc giữ lại sản phẩm không có trong đơn hàng vừa đặt
          const remainingItems = cartItems.filter(item => !selectedProductIds.includes(item.id));
          console.log('Remaining cart items after filtering:', remainingItems);
          localStorage.setItem(cartStorageKey, JSON.stringify(remainingItems));
          
          // Xóa danh sách ID sản phẩm đã chọn
          localStorage.removeItem('selectedProductIds_' + username);
          localStorage.removeItem('preparing_order');
          
          // Định nghĩa hàm updateCartCount() để đảm bảo icon giỏ hàng được cập nhật
          function updateCartCount() {
            const cartItems = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
            const totalQuantity = cartItems.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
            // Nếu có thể, cập nhật icon giỏ hàng tại đây
            if (window.opener && !window.opener.closed) {
              try {
                // Nếu submit_order.php được mở từ trang khác, cập nhật trang đó
                if (typeof window.opener.updateCartCount === 'function') {
                  window.opener.updateCartCount();
                }
              } catch (e) {
                console.error('Không thể gọi hàm updateCartCount từ trang mẹ:', e);
              }
            }
            return totalQuantity;
          }
          
          // Gọi hàm để cập nhật số lượng giỏ hàng
          updateCartCount();
          
          // Alert success
          alert('Đặt hàng thành công!');
          setTimeout(function() {
            localStorage.setItem('cart_just_updated', 'true');
            window.location.href = 'index.php';
          }, 500); 
        } catch (error) {
          console.error('Error during form submission:', error);
          alert('Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại sau.');
        }
      });
    });
  </script>
  
</body>
</html>
