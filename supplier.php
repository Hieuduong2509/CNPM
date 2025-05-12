<?php
session_start();
require_once("./BE/db.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Đăng Sản Phẩm Mới</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .badge-confirmed { background-color: #28a745; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-cancelled { background-color: #dc3545; }

    .table-wrapper {
      max-height: 400px;
      overflow-y: auto;
      overflow-x: auto;
    }

    td, th {
      word-break: break-word;
      white-space: nowrap;
    }
    
    .product-item {
      border: 1px solid #dee2e6;
      border-radius: 5px;
      padding: 10px;
      margin-bottom: 10px;
      position: relative;
    }
    
    .remove-product {
      position: absolute;
      top: 5px;
      right: 5px;
      cursor: pointer;
      color: #dc3545;
    }
    
    .preview-image {
      max-width: 100%;
      max-height: 150px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <header class="navbar bg-dark text-white p-2"> 
    <div class="container">
      <h2>Đăng Sản Phẩm Mới</h2>
      <div class="nav-right">
        <a href="index.php" class="btn btn-outline-light mr-2">Trang chủ</a>
        <a href="logout.php" class="btn btn-outline-danger">Đăng xuất</a>
      </div>
    </div>
  </header>
  
  <div class="container mt-4">
    <h2 class="mb-4 text-center">Tạo Sản Phẩm Mới</h2>
    
    <div class="row">
      <!-- Product Form -->
      <div class="col-md-12 mb-4">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Thông tin sản phẩm</h4>
          </div>
          <div class="card-body">
            <form id="productForm">
              <!-- Thông tin nhà cung cấp -->
              <div class="form-group">
                <label for="supplier_name"><i class="fas fa-user mr-1"></i> Tên nhà cung cấp</label>
                <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
              </div>
              
              <!-- Thông tin sản phẩm -->
              <h5 class="mt-4 mb-3"><i class="fas fa-shopping-cart mr-1"></i> Thông tin sản phẩm</h5>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="product_name">Tên sản phẩm</label>
                    <input type="text" class="form-control" id="product_name" name="product_name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="product_category">Loại sản phẩm</label>
                    <select class="form-control" id="product_category" name="product_category" required>
                        <option value="">Chọn loại sản phẩm</option>
                        <option value="1">Laptop</option>
                        <option value="2">Mouse</option>
                        <option value="3">Keyboard</option>
                        <option value="4">Desktop</option>
                        <option value="5">Linh kiện</option>
                        <option value="6">Khác</option>
                      </select>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="product_quantity">Số lượng tồn kho</label>
                    <input type="number" class="form-control" id="product_quantity" name="product_quantity" min="1" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="product_price">Giá sản phẩm (VNĐ)</label>
                    <input type="number" class="form-control" id="product_price" name="product_price" min="0" required>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label for="product_image">Hình ảnh sản phẩm</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="product_image" name="product_image" accept="image/*">
                  <label class="custom-file-label" for="product_image">Chọn file ảnh</label>
                </div>
                <div id="image-preview" class="mt-2"></div>
              </div>
              
              <div class="form-group">
                <label for="product_description">Mô tả sản phẩm</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="3"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
                <i class="fas fa-paper-plane mr-1"></i> Đăng sản phẩm
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Product submissions table -->
  <div class="container mt-4 mb-5">
    <h3 class="mb-3 text-center">Sản phẩm đã đăng</h3>
    <div class="table-wrapper border rounded p-2 bg-white">
      <table class="table table-bordered table-hover">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Sản phẩm</th>
            <th>Loại</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Ngày đăng</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody id="submissionsList">
          <!-- Product submissions will be loaded here -->
        </tbody>
      </table>
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
        case 'approved': return 'badge-confirmed';
        case 'pending': return 'badge-pending';
        case 'rejected': return 'badge-cancelled';
        default: return 'badge-secondary';
      }
    }

    $('#productForm').submit(function(e) {
      e.preventDefault();
      
      const supplierName = $('#supplier_name').val();
      const productName = $('#product_name').val();
      const productCategory = $('#product_category').val();
      const productQuantity = $('#product_quantity').val();
      const productPrice = $('#product_price').val();
      const productDescription = $('#product_description').val();
      const productImage = $('#image-preview img').attr('src') || '';
      
      // Kiểm tra dữ liệu đầu vào
      if (!supplierName || !productName || !productCategory || !productQuantity || !productPrice) {
        alert('Vui lòng điền đầy đủ thông tin sản phẩm');
        return;
      }
      
      // Tạo đối tượng sản phẩm
      const productRequest = {
        supplier_name: supplierName,
        product_name: productName,
        category: productCategory,
        price: parseFloat(productPrice),
        quantity: parseInt(productQuantity),
        description: productDescription,
        image: productImage,
        status: 'pending',
        created_at: new Date().toLocaleString('vi-VN')
      };
      
      // Lưu vào localStorage
      let productRequests = [];
      try {
        productRequests = JSON.parse(localStorage.getItem('product_requests')) || [];
        if (!Array.isArray(productRequests)) {
          productRequests = [];
        }
      } catch (error) {
        productRequests = [];
      }
      
      productRequests.push(productRequest);
      localStorage.setItem('product_requests', JSON.stringify(productRequests));
      
      // Thông báo thành công
      alert('Sản phẩm đã được đăng thành công và đang chờ phê duyệt!');
      
      // Đặt lại form
      $('#productForm')[0].reset();
      $('#image-preview').html('');
      $('.custom-file-label').text('Chọn file ảnh');
      
      // Tải lại danh sách sản phẩm đã đăng
      loadProductSubmissions();
    });

    // Load product submissions from localStorage
    function loadProductSubmissions() {
      let productRequests = [];
      try {
        productRequests = JSON.parse(localStorage.getItem('product_requests') || '[]');
        
        if (!Array.isArray(productRequests) || productRequests.length === 0) {
          document.getElementById('submissionsList').innerHTML = 
            '<tr><td colspan="7" class="text-center">Chưa có sản phẩm nào được đăng</td></tr>';
          return;
        }
      } catch (error) {
        document.getElementById('submissionsList').innerHTML = 
          '<tr><td colspan="7" class="text-center">Lỗi khi đọc dữ liệu sản phẩm</td></tr>';
        return;
      }
      
      const tbody = document.getElementById('submissionsList');
      tbody.innerHTML = '';
      
      productRequests.forEach((product, index) => {
        if (!product || typeof product !== 'object') {
          return;
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>
            ${product.image ? `<img src="${product.image}" width="40" height="40" class="mr-2">` : ''}
            ${product.product_name || 'Không tên'}
          </td>
          <td>${product.category || 'Không xác định'}</td>
          <td>${formatCurrency(product.price || 0)}</td>
          <td>${product.quantity || 0}</td>
          <td>${product.created_at || 'Không xác định'}</td>
          <td><span class="badge ${getStatusClass(product.status || 'pending')}">${product.status || 'pending'}</span></td>
        `;
        tbody.appendChild(row);
      });
    }
    
    // Document ready
    $(document).ready(function() {
      // Load product submissions initially
      loadProductSubmissions();
      
      // Handle image preview
      $('#product_image').change(function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#image-preview').html(`<img src="${e.target.result}" class="preview-image">`);
          }
          reader.readAsDataURL(file);
          $('.custom-file-label').text(file.name);
        }
      });
    });
  </script>
</body>
</html>