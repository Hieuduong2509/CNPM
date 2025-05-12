<?php
session_start();
require_once("./BE/db.php");
// Kiểm tra đăng nhập admin nếu cần
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Order Management & Product Approval</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .badge-confirmed { background-color: #28a745; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-cancelled { background-color: #dc3545; }

    .table-wrapper {
      max-height: 600px;
      overflow-y: auto;
      overflow-x: auto;
    }

    td, th {
      word-break: break-word;
      white-space: nowrap;
    }

    .table th:nth-child(2), .table td:nth-child(2) {
      min-width: 180px;
    }
    
    .status-badge {
      cursor: pointer;
    }
  </style>
</head>
<body>
  <header class="navbar bg-dark text-white p-2"> 
    <div class="container">
      <h2>Admin Panel</h2>
      <div class="nav-right">
        <a href="index.php" class="btn btn-outline-light">Trang chủ</a>
        <a href="manage_product.php" class="btn btn-outline-light">Quản Lý Sản Phẩm</a>
        <a href="logout.php" class="btn btn-outline-danger">Đăng xuất</a>
      </div>
    </div>
  </header>
  <div class="container-fluid mt-4">
   <div class="container-fluid mt-4">
  <h2 class="mb-4 text-center">Bảng điều khiển quản trị</h2>
  <div class="row">
    
    <!-- Bên trái: Duyệt đơn hàng của User -->
    <div class="col-md-6 mb-4">
      <h4 class="text-center">📦 Duyệt đơn hàng người dùng</h4>
      <div class="table-wrapper border rounded p-2 bg-white">
        <table class="table table-bordered table-hover text-center">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Khách hàng</th>
              <th>Sản phẩm</th>
              <th>Số lượng</th>
              <th>Tổng tiền</th>
              <th>Thanh toán</th>
              <th>Ngày</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody id="orderTableBody">
            <!-- Dữ liệu đơn hàng sẽ được load tại đây -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bên phải: Duyệt sản phẩm từ Supplier -->
    <div class="col-md-6 mb-4">
      <h4 class="text-center">🛠️ Duyệt sản phẩm nhà cung cấp</h4>
      <div class="table-wrapper border rounded p-2 bg-white">
        <table class="table table-bordered table-hover text-center">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Nhà cung cấp</th>
              <th>Tên sản phẩm</th>
              <th>Loại</th>
              <th>Giá</th>
              <th>Ảnh</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody id="supplierTableBody">
            <!-- Dữ liệu sản phẩm nhà cung cấp sẽ được load tại đây -->
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

    </div>
    
  </div>

  <!-- Order Details Modal -->
  <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
          <!-- Order details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Format currency
    function formatCurrency(amount) {
      return '₫' + parseInt(amount).toLocaleString();
    }
    
    // Get status badge class
    function getStatusClass(status) {
      switch (status.toLowerCase()) {
        case 'đồng ý': 
        case 'approved': return 'badge-confirmed';
        case 'đang chờ xác nhận': 
        case 'pending': return 'badge-pending';
        case 'hủy': 
        case 'rejected': return 'badge-cancelled';
        default: return 'badge-secondary';
      }
    }
    
    // Toggle order status
    function toggleOrderStatus(orderId) {
      try {
        const orders = JSON.parse(localStorage.getItem('orders') || '[]');
        
        if (!Array.isArray(orders) || orderId < 0 || orderId >= orders.length) {
          console.error(`Invalid order index ${orderId} or orders array`, orders);
          alert('Không thể thay đổi trạng thái. Đơn hàng không tồn tại.');
          return;
        }
        
        const order = orders[orderId];
        if (!order || typeof order !== 'object') {
          console.error(`Invalid order at index ${orderId}:`, order);
          alert('Không thể thay đổi trạng thái. Dữ liệu đơn hàng không hợp lệ.');
          return;
        }
        
        // Toggle between 'đồng ý' and 'đang chờ xác nhận'
        order.status = order.status === 'đồng ý' ? 'đang chờ xác nhận' : 'đồng ý';
        console.log(`Changed order ${orderId} status to ${order.status}`);
        
        localStorage.setItem('orders', JSON.stringify(orders));
        loadOrders(); // Reload the orders
      } catch (error) {
        console.error('Error toggling order status:', error);
        alert('Có lỗi xảy ra khi thay đổi trạng thái đơn hàng.');
      }
    }
    
    // Approve product request
    function approveProductRequest(productId) {
      try {
        const productRequests = JSON.parse(localStorage.getItem('product_requests') || '[]');
        
        if (!Array.isArray(productRequests) || productId < 0 || productId >= productRequests.length) {
          console.error(`Invalid product request index ${productId}`);
          alert('Không thể phê duyệt. Sản phẩm không tồn tại.');
          return;
        }
        
        const product = productRequests[productId];
        if (!product || typeof product !== 'object') {
          console.error(`Invalid product at index ${productId}`);
          alert('Không thể phê duyệt. Dữ liệu sản phẩm không hợp lệ.');
          return;
        }
        
        // Update status to approved
        product.status = 'approved';
        console.log(`Approved product request ${productId}`);
        
        // Lưu vào cơ sở dữ liệu
        saveProductToDatabase(product, productId);
        
        localStorage.setItem('product_requests', JSON.stringify(productRequests));
        loadProductRequests(); // Reload the product requests
        
        alert('Sản phẩm đã được phê duyệt và thêm vào cửa hàng!');
      } catch (error) {
        console.error('Error approving product request:', error);
        alert('Có lỗi xảy ra khi phê duyệt sản phẩm.');
      }
    }
    
    // Save product to database
    function saveProductToDatabase(product, productId) {
      try {
        // Show loading message
        const loadingMsg = $('<div class="alert alert-info">Đang lưu sản phẩm vào cơ sở dữ liệu...</div>');
        $('#supplierTableBody').before(loadingMsg);
        
        // Chuẩn bị dữ liệu sản phẩm để gửi đến server
        const productData = {
          product_name: product.product_name,
          description: product.description || '',
          price: parseFloat(product.price) || 0,
          stockQuantity: parseInt(product.quantity) || 0,
          category: product.category || '1',
          image: product.image || '',
          customer_name: product.supplier_name || '',
          popular: 0 // Default value
        };
        
        console.log('Sending product data to server:', JSON.stringify(productData));
        
        // Gửi đến server sử dụng fetch API
        fetch('save_product.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(productData)
        })
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Server response:', data);
          loadingMsg.remove();
          
          if (data.success) {
            // Hiển thị thông báo thành công
            const successMsg = $(`<div class="alert alert-success">Sản phẩm đã được lưu thành công với ID: ${data.productId}</div>`);
            $('#supplierTableBody').before(successMsg);
            
            // Xóa thông báo sau 3 giây
            setTimeout(() => {
              successMsg.fadeOut(function() {
                $(this).remove();
              });
            }, 3000);
          } else {
            // Hiển thị lỗi với thông tin debug
            let errorDetails = '';
            if (data.debug) {
              errorDetails = '<button class="btn btn-sm btn-link" onclick="$(\'#debug-info\').toggle()">Hiển thị chi tiết lỗi</button>' + 
                '<div id="debug-info" style="display:none"><pre>' + 
                JSON.stringify(data.debug, null, 2) + '</pre></div>';
            }
            
            const errorMsg = $(`<div class="alert alert-danger">
              Lỗi: ${data.message || 'Không thể lưu sản phẩm'}
              ${errorDetails}
            </div>`);
            $('#supplierTableBody').before(errorMsg);
            
            // Xóa thông báo lỗi sau 10 giây
            setTimeout(() => {
              errorMsg.fadeOut(function() {
                $(this).remove();
              });
            }, 10000);
          }
        })
        .catch(error => {
          loadingMsg.remove();
          console.error('Error saving product:', error);
          
          // Hiển thị thông báo lỗi
          const errorMsg = $(`<div class="alert alert-danger">
            <p>Lỗi kết nối: ${error.message}</p>
            <p>Vui lòng kiểm tra kết nối mạng và thử lại sau.</p>
            <p>Hãy kiểm tra console để xem chi tiết lỗi.</p>
          </div>`);
          $('#supplierTableBody').before(errorMsg);
          
          setTimeout(() => {
            errorMsg.fadeOut(function() {
              $(this).remove();
            });
          }, 10000);
        });
      } catch (error) {
        console.error('Error preparing product data:', error);
        alert('Có lỗi xảy ra khi chuẩn bị dữ liệu sản phẩm.');
      }
    }
    
    // Reject product request
    function rejectProductRequest(productId) {
      try {
        const productRequests = JSON.parse(localStorage.getItem('product_requests') || '[]');
        
        if (!Array.isArray(productRequests) || productId < 0 || productId >= productRequests.length) {
          console.error(`Invalid product request index ${productId}`);
          alert('Không thể từ chối. Sản phẩm không tồn tại.');
          return;
        }
        
        const product = productRequests[productId];
        if (!product || typeof product !== 'object') {
          console.error(`Invalid product at index ${productId}`);
          alert('Không thể từ chối. Dữ liệu sản phẩm không hợp lệ.');
          return;
        }
        
        // Update status to rejected
        product.status = 'rejected';
        console.log(`Rejected product request ${productId}`);
        
        localStorage.setItem('product_requests', JSON.stringify(productRequests));
        loadProductRequests(); // Reload the product requests
        
        alert('Sản phẩm đã bị từ chối!');
      } catch (error) {
        console.error('Error rejecting product request:', error);
        alert('Có lỗi xảy ra khi từ chối sản phẩm.');
      }
    }
    
    // View product details
    function viewProductDetails(productId) {
      try {
        const productRequests = JSON.parse(localStorage.getItem('product_requests') || '[]');
        
        if (!Array.isArray(productRequests) || productId < 0 || productId >= productRequests.length) {
          console.error(`Invalid product request index ${productId}`);
          alert('Không thể hiển thị chi tiết. Sản phẩm không tồn tại.');
          return;
        }
        
        const product = productRequests[productId];
        if (!product || typeof product !== 'object') {
          console.error(`Invalid product at index ${productId}`);
          alert('Không thể hiển thị chi tiết. Dữ liệu sản phẩm không hợp lệ.');
          return;
        }
        
        // Create product details HTML
        let detailsHTML = `
          <div class="product-details">
            <div class="row mb-3">
              <div class="col-md-6">
                <h6>Nhà cung cấp: ${product.supplier_name || 'Không tên'}</h6>
                <p>Ngày đăng: ${product.created_at || 'Không xác định'}</p>
              </div>
              <div class="col-md-6 text-right">
                <h6>Trạng thái: <span class="badge ${getStatusClass(product.status || 'pending')}">${product.status || 'pending'}</span></h6>
                <h5>Giá: ${formatCurrency(product.price || 0)}</h5>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <h6>Tên sản phẩm: ${product.product_name || 'Không tên'}</h6>
                <p>Loại: ${product.category || 'Không xác định'}</p>
                <p>Số lượng: ${product.quantity || 0}</p>
              </div>
              <div class="col-md-6">
                ${product.image ? `<img src="${product.image}" class="img-fluid img-thumbnail" style="max-height: 200px;" alt="${product.product_name}">` : '<div class="text-muted">Không có hình ảnh</div>'}
              </div>
            </div>
            
            <div class="row">
              <div class="col-12">
                <h6>Mô tả:</h6>
                <p>${product.description || 'Không có mô tả'}</p>
              </div>
            </div>
          </div>
        `;
        
        $('#orderDetailsContent').html(detailsHTML);
        $('#orderDetailsModalLabel').text('Chi tiết sản phẩm');
        $('#orderDetailsModal').modal('show');
      } catch (error) {
        console.error('Error displaying product details:', error);
        alert('Có lỗi xảy ra khi hiển thị chi tiết sản phẩm.');
      }
    }
    
    // View order details
    function viewOrderDetails(orderId) {
      try {
        const orders = JSON.parse(localStorage.getItem('orders') || '[]');
        console.log(`Viewing details for order ${orderId}, Orders:`, orders);
        
        if (!Array.isArray(orders) || orderId < 0 || orderId >= orders.length) {
          console.error(`Invalid order index ${orderId} or orders array`, orders);
          alert('Không thể hiển thị chi tiết đơn hàng. Đơn hàng không tồn tại.');
          return;
        }
        
        const order = orders[orderId];
        if (!order || typeof order !== 'object') {
          console.error(`Invalid order at index ${orderId}:`, order);
          alert('Không thể hiển thị chi tiết đơn hàng. Dữ liệu đơn hàng không hợp lệ.');
          return;
        }
        
        let detailsHTML = `
          <div class="order-details">
            <div class="row mb-3">
              <div class="col-md-6">
                <h6>Khách hàng: ${order.customer_name || 'Không tên'}</h6>
                <p>Ngày đặt: ${order.created_at || 'Không xác định'}</p>
                <p>Phương thức thanh toán: ${order.paymentMethod || 'Không xác định'}</p>
              </div>
              <div class="col-md-6 text-right">
                <h6>Trạng thái: <span class="badge ${getStatusClass(order.status || 'Không xác định')}">${order.status || 'Không xác định'}</span></h6>
                <h5>Tổng tiền: ${formatCurrency(order.total || 0)}</h5>
              </div>
            </div>
            
            <h6>Sản phẩm:</h6>
            <table class="table table-bordered">
              <thead class="thead-light">
                <tr>
                  <th>Sản phẩm</th>
                  <th>Loại</th>
                  <th>Đơn giá</th>
                  <th>Số lượng</th>
                  <th>Thành tiền</th>
                </tr>
              </thead>
              <tbody>
        `;
        
        if (Array.isArray(order.products) && order.products.length > 0) {
          order.products.forEach(product => {
            if (!product || typeof product !== 'object') {
              console.warn('Invalid product in order:', product);
              return;
            }
            
            const price = parseInt(product.price || 0);
            const quantity = parseInt(product.quantity || 1);
            const subtotal = price * quantity;
            
            detailsHTML += `
              <tr>
                <td>
                  ${product.image ? `<img src="asset/productImg/${product.image}" width="50" height="50" class="mr-2">` : ''}
                  ${product.product_name || 'Không tên'}
                </td>
                <td>${product.category || 'Không xác định'}</td>
                <td>${formatCurrency(price)}</td>
                <td>${quantity}</td>
                <td>${formatCurrency(subtotal)}</td>
              </tr>
            `;
          });
        } else {
          detailsHTML += `
            <tr>
              <td colspan="5" class="text-center">Không có sản phẩm nào trong đơn hàng này</td>
            </tr>
          `;
        }
        
        detailsHTML += `
              </tbody>
            </table>
          </div>
        `;
        
        $('#orderDetailsModalLabel').text('Chi tiết đơn hàng');
        $('#orderDetailsContent').html(detailsHTML);
        $('#orderDetailsModal').modal('show');
      } catch (error) {
        console.error('Error displaying order details:', error);
        alert('Có lỗi xảy ra khi hiển thị chi tiết đơn hàng.');
      }
    }
    
    // Save order to database
    function saveOrderToDatabase(orderId) {
      try {
        const orders = JSON.parse(localStorage.getItem('orders') || '[]');
        
        if (!Array.isArray(orders) || orderId < 0 || orderId >= orders.length) {
          console.error(`Invalid order index ${orderId} or orders array`, orders);
          alert('Không thể lưu đơn hàng. Đơn hàng không tồn tại.');
          return;
        }
        
        const order = orders[orderId];
        if (!order || typeof order !== 'object') {
          console.error(`Invalid order at index ${orderId}:`, order);
          alert('Không thể lưu đơn hàng. Dữ liệu đơn hàng không hợp lệ.');
          return;
        }
        
        // Show loading message
        const loadingMsg = $('<div class="alert alert-info">Đang lưu đơn hàng vào cơ sở dữ liệu...</div>');
        $('#orderTableBody').before(loadingMsg);
        
        // Log order data being sent to server
        console.log('Sending order data to server:', JSON.stringify(order));
        
        // Send to server using fetch API
        fetch('save_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(order)
        })
        .then(response => {
          console.log('Response status:', response.status);
          return response.json();
        })
        .then(data => {
          console.log('Server response:', data);
          loadingMsg.remove();
          
          if (data.success) {
            // Show success message
            const successMsg = $(`<div class="alert alert-success">Đơn hàng đã được lưu thành công với ID: ${data.orderId}</div>`);
            $('#orderTableBody').before(successMsg);
            
            // Remove order from localStorage
            orders.splice(orderId, 1);
            localStorage.setItem('orders', JSON.stringify(orders));
            
            // Reload orders
            setTimeout(() => {
              successMsg.fadeOut(function() {
                $(this).remove();
                loadOrders();
              });
            }, 3000);
          } else {
            // Show error with debug info
            let errorDetails = '';
            if (data.debug) {
              errorDetails = '<button class="btn btn-sm btn-link" onclick="$(\'#debug-info\').toggle()">Hiển thị chi tiết lỗi</button>' + 
                '<div id="debug-info" style="display:none"><pre>' + 
                JSON.stringify(data.debug, null, 2) + '</pre></div>';
            }
            
            const errorMsg = $(`<div class="alert alert-danger">
              Lỗi: ${data.message || 'Không thể lưu đơn hàng'}
              ${errorDetails}
            </div>`);
            $('#orderTableBody').before(errorMsg);
          }
        })
        .catch(error => {
          loadingMsg.remove();
          console.error('Error saving order:', error);
          
          // Show error message
          const errorMsg = $(`<div class="alert alert-danger">
            <p>Lỗi kết nối: ${error.message}</p>
            <p>Vui lòng kiểm tra kết nối mạng và thử lại sau.</p>
            <p>Hãy kiểm tra console để xem chi tiết lỗi.</p>
          </div>`);
          $('#orderTableBody').before(errorMsg);
          
          setTimeout(() => {
            errorMsg.fadeOut(function() {
              $(this).remove();
            });
          }, 10000);
        });
      } catch (error) {
        console.error('Error preparing order data:', error);
        alert('Có lỗi xảy ra khi chuẩn bị dữ liệu đơn hàng.');
      }
    }
    
    // Delete order
    function deleteOrder(orderId) {
      try {
        if (confirm('Bạn có chắc muốn xóa đơn hàng này?')) {
          const orders = JSON.parse(localStorage.getItem('orders') || '[]');
          
          if (!Array.isArray(orders) || orderId < 0 || orderId >= orders.length) {
            console.error(`Invalid order index ${orderId} or orders array`, orders);
            alert('Không thể xóa. Đơn hàng không tồn tại.');
            return;
          }
          
          orders.splice(orderId, 1);
          console.log(`Deleted order at index ${orderId}. Remaining orders:`, orders);
          
          localStorage.setItem('orders', JSON.stringify(orders));
          loadOrders(); // Reload the orders
        }
      } catch (error) {
        console.error('Error deleting order:', error);
        alert('Có lỗi xảy ra khi xóa đơn hàng.');
      }
    }
    
    // Delete product request
    function deleteProductRequest(productId) {
      try {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
          const productRequests = JSON.parse(localStorage.getItem('product_requests') || '[]');
          
          if (!Array.isArray(productRequests) || productId < 0 || productId >= productRequests.length) {
            console.error(`Invalid product index ${productId}`);
            alert('Không thể xóa. Sản phẩm không tồn tại.');
            return;
          }
          
          productRequests.splice(productId, 1);
          console.log(`Deleted product at index ${productId}. Remaining products:`, productRequests);
          
          localStorage.setItem('product_requests', JSON.stringify(productRequests));
          loadProductRequests(); // Reload the product requests
        }
      } catch (error) {
        console.error('Error deleting product request:', error);
        alert('Có lỗi xảy ra khi xóa sản phẩm.');
      }
    }
    
    // Load orders from localStorage
    function loadOrders() {
      let orders = [];
      try {
        const ordersStr = localStorage.getItem('orders');
        console.log('Raw orders from localStorage:', ordersStr);
        
        if (!ordersStr) {
          console.log('No orders found in localStorage (null or empty)');
          document.getElementById('orderTableBody').innerHTML = 
            '<tr><td colspan="9" class="text-center">Không có đơn hàng nào</td></tr>';
          return;
        }
        
        orders = JSON.parse(ordersStr);
        console.log('Parsed orders from localStorage:', orders);
        
        if (!Array.isArray(orders)) {
          console.error('Orders in localStorage is not an array');
          document.getElementById('orderTableBody').innerHTML = 
            '<tr><td colspan="9" class="text-center">Dữ liệu đơn hàng không hợp lệ</td></tr>';
          return;
        }
        
        if (orders.length === 0) {
          console.log('Orders array is empty');
          document.getElementById('orderTableBody').innerHTML = 
            '<tr><td colspan="9" class="text-center">Không có đơn hàng nào</td></tr>';
          return;
        }
      } catch (error) {
        console.error('Error parsing orders from localStorage:', error);
        document.getElementById('orderTableBody').innerHTML = 
          '<tr><td colspan="9" class="text-center">Lỗi khi đọc dữ liệu đơn hàng</td></tr>';
        return;
      }
      
      const tbody = document.getElementById('orderTableBody');
      tbody.innerHTML = '';
      
      orders.forEach((order, index) => {
        console.log(`Processing order ${index}:`, order);
        
        if (!order || typeof order !== 'object') {
          console.error(`Invalid order at index ${index}:`, order);
          return;
        }
        
        // Extract first product for display
        let firstProduct = { product_name: 'N/A', quantity: 0 };
        let totalProducts = 0;
        
        if (Array.isArray(order.products) && order.products.length > 0) {
          firstProduct = order.products[0];
          totalProducts = order.products.length;
        } else {
          console.warn(`Order ${index} has no products or invalid products:`, order.products);
        }
        
        console.log(`First product for order ${index}:`, firstProduct);
        
        const productText = totalProducts > 1 
          ? `${firstProduct.product_name || 'Không tên'} <small>và ${totalProducts - 1} sản phẩm khác</small>` 
          : (firstProduct.product_name || 'Không tên');
        
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>${order.customer_name || 'Không tên'}</td>
          <td>${productText}</td>
          <td>${firstProduct.quantity || 0}</td>
          <td>${formatCurrency(order.total || 0)}</td>
          <td>${order.paymentMethod || 'Không xác định'}</td>
          <td>${order.created_at || 'Không xác định'}</td>
          <td><span class="badge ${getStatusClass(order.status || 'Không xác định')} status-badge" onclick="toggleOrderStatus(${index})">${order.status || 'Không xác định'}</span></td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(${index})">Xem</button>
            <button class="btn btn-sm btn-info" onclick="saveOrderToDatabase(${index})">Xác Nhận</button>
            <button class="btn btn-sm btn-danger" onclick="deleteOrder(${index})">Xóa</button>
          </td>
        `;
        tbody.appendChild(row);
      });
    }
    
    // Load product requests from localStorage
    function loadProductRequests() {
      let productRequests = [];
      try {
        const requestsStr = localStorage.getItem('product_requests');
        console.log('Raw product requests from localStorage:', requestsStr);
        
        if (!requestsStr) {
          console.log('No product requests found in localStorage');
          document.getElementById('supplierTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center">Không có sản phẩm nào chờ duyệt</td></tr>';
          return;
        }
        
        productRequests = JSON.parse(requestsStr);
        console.log('Parsed product requests from localStorage:', productRequests);
        
        if (!Array.isArray(productRequests)) {
          console.error('Product requests in localStorage is not an array');
          document.getElementById('supplierTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center">Dữ liệu sản phẩm không hợp lệ</td></tr>';
          return;
        }
        
        if (productRequests.length === 0) {
          console.log('Product requests array is empty');
          document.getElementById('supplierTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center">Không có sản phẩm nào chờ duyệt</td></tr>';
          return;
        }
      } catch (error) {
        console.error('Error parsing product requests from localStorage:', error);
        document.getElementById('supplierTableBody').innerHTML = 
          '<tr><td colspan="8" class="text-center">Lỗi khi đọc dữ liệu sản phẩm</td></tr>';
        return;
      }
      
      const tbody = document.getElementById('supplierTableBody');
      tbody.innerHTML = '';
      
      productRequests.forEach((product, index) => {
        console.log(`Processing product request ${index}:`, product);
        
        if (!product || typeof product !== 'object') {
          console.error(`Invalid product at index ${index}:`, product);
          return;
        }
        
        const row = document.createElement('tr');
        
        // Display status-specific buttons
        let actionButtons = '';
        if (product.status === 'pending') {
          actionButtons = `
            <button class="btn btn-sm btn-success" onclick="approveProductRequest(${index})">Duyệt</button>
            <button class="btn btn-sm btn-danger" onclick="rejectProductRequest(${index})">Từ chối</button>
          `;
        } else {
          actionButtons = `
            <button class="btn btn-sm btn-danger" onclick="deleteProductRequest(${index})">Xóa</button>
          `;
        }
        
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>${product.supplier_name || 'Không tên'}</td>
          <td>${product.product_name || 'Không tên'}</td>
          <td>${product.category || 'Không xác định'}</td>
          <td>${formatCurrency(product.price || 0)}</td>
          <td>
            ${product.image ? `<img src="${product.image}" width="50" height="50" class="img-thumbnail">` : 'Không có ảnh'}
          </td>
          <td><span class="badge ${getStatusClass(product.status || 'pending')}">${product.status || 'pending'}</span></td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="viewProductDetails(${index})">Xem</button>
            ${actionButtons}
          </td>
        `;
        tbody.appendChild(row);
      });
    }
    
    // Load orders when page loads
    document.addEventListener('DOMContentLoaded', function() {
      loadOrders();
      loadProductRequests();
    });
    
  </script>
</body>
</html>