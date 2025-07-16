<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Get order details
$order = getOrderById($conn, $orderId, $_SESSION['user_id']);
if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$orderItems = getOrderItems($conn, $orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Confirmation - CATFE</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
</head>
<body class="logged-in">

  <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Logo" style="height:30px;">
                    <span>CATFE</span>
                </a>
            </div>
            <div class="nav-menu" id="nav-menu">
            <!-- Centered menu links -->
            <!-- <div class="nav-center-menu"> -->
                <a href="index.php" class="nav-link">HOME</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <a href="products.php" class="nav-link">PRODUCTS</a>
                <a href="map.php" class="nav-link">MAP</a>
            <!-- </div> -->

            <!-- Right icons (cart & profile) -->
            <!-- <div class="nav-right-icons"> -->
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($isLoggedIn): ?>
                    <div class="profile-dropdown">
                        <a href="#" class="nav-link" id="profile-icon">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-content" id="profile-dropdown">
                            <a href="profile.php">My Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                    <?php if ($isAdmin): ?>
                        <!-- <a href="../admin/index.php" class="nav-link">ADMIN</a> -->
                        <!-- Admin Dropdown -->
                        <div class="admin-dropdown">
                            <!-- <a href="index.php" class="admin-toggle nav-link active">
                                <span>ADMIN</span>
                            </a> -->
                            <button class="admin-toggle nav-link" href="#">
                                <!-- <i class="fas fa-user-cog"></i> -->
                                <span>ADMIN</span>
                            </button>
                            <div class="admin-dropdown-menu">
                                <a href="admin/index.php">Dashboard</a>
                                <a href="admin/products.php">Manage Products</a>
                                <a href="admin/categories.php">Manage Categories</a>
                                <a href="admin/users.php">Manage Users</a>
                                <a href="admin/orders.php">Manage Orders</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="nav-link">
                        LOGIN
                    </a>
                <?php endif; ?>
            <!-- </div> -->
            </div>

            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

  <!-- Order Confirmation Section -->
  <section class="confirmation-section">
    <div class="container">
      <div class="confirmation-container">
        <div class="confirmation-header">
          <i class="fas fa-check-circle"></i>
          <h2 style="color: #333; margin-bottom: 1rem; margin-top: 1.5rem; font-size: 3rem; font-weight: bold;">Order Confirmed!</h2>
          <p style="font-size: 1.2rem; margin-bottom: 6rem; color: #333;">Thank you for your order. We'll start preparing it right away.</p>
        </div>

        <div class="order-details">
          <h3>Order Details</h3>
          <div class="order-info">
            <div class="info-item"><span class="label">Order ID:</span> <span class="value">#<?php echo $order['id']; ?></span></div>
            <div class="info-item"><span class="label">Order Date:</span> <span class="value"><?php echo formatDate($order['created_at']); ?></span></div>
            <div class="info-item"><span class="label">Status:</span> <span class="value status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></div>
            <div class="info-item"><span class="label">Total Amount:</span> <span class="value">RM <?php echo number_format($order['total_amount'], 2); ?></span></div>
          </div>

          <div class="order-items">
            <h4>Items Ordered</h4>
            <div class="order-items-header">
                <span>Product</span>
                <span>Price</span>
                <span>Quantity</span>
                <span>Total</span>
            </div>
            <?php foreach($orderItems as $item): ?>
            <div class="order-item">
                <div class="item-product">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/images/placeholder-product.png'); ?>"
                         alt="<?php echo htmlspecialchars($item['name']); ?>" />
                    <div>
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br />
                        <small><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></small>
                    </div>
                </div>
                <div class="item-price">RM <?php echo number_format($item['price'], 2); ?></div>
                <div class="item-quantity"><?php echo $item['quantity']; ?></div>
                <div class="item-total">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="next-steps">
          <h3>What's Next?</h3>
          <div class="steps">
            <div class="step">
              <i class="fas fa-clock"></i>
              <h4>Order Processing</h4>
              <p>Our baristas and cats are preparing your order with care and love.</p>
            </div>
            <div class="step">
              <i class="fas fa-bell"></i>
              <h4>Ready for Pickup</h4>
              <p>We'll notify you when your order is ready.</p>
            </div>
            <div class="step">
              <i class="fas fa-paw"></i>
              <h4>Enjoy at Catfe</h4>
              <p>Pick up your order and relax with your beverage.</p>
            </div>
          </div>
        </div>

        <div class="confirmation-actions">
          <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Back to Home</a>
          <a href="products.php" class="btn btn-secondary"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section" style="padding-right: 3em;">
                     <h3 style="display: flex; align-items: center; gap: 8px;">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 30px;">
                        CATFE
                    </h3>
                    <p>Sip, unwind, and enjoy the gentle company of cats.
                        Catfe brings comfort, quality coffee, and calm all in one cozy space.</p>
                </div>
                <!-- <div class="footer-section" style="padding-left: 5em;"> -->
                <div class="footer-section quick-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="map.php">Map</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> Jalan Sunsuria, Bandar Sunsuria, 43900 Sepang, Selangor</p>
                    <p><i class="fas fa-phone" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> 60123456789</p>
                    <p><i class="fas fa-envelope" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> catfe@example.com</p>
                    <p><i class="fas fa-clock" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> Open Daily: 7AM - 9PM</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Catfe. All rights reserved.</p>
            </div>
        </div>
    </footer>

  <script src="assets/js/main.js"></script>
  <script>
    // Profile Dropdown
    document.getElementById("profile-icon").addEventListener("click", function(e){
        e.preventDefault();
        var dropdown = document.getElementById("profile-dropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    window.addEventListener("click", function(e){
        if (!e.target.matches('#profile-icon, #profile-icon *')) {
            var dropdown = document.getElementById("profile-dropdown");
            if (dropdown) dropdown.style.display = "none";
        }
    });
  </script>
</body>
</html>
