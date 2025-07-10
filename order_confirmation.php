<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Cozy Beverage</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="logged-in">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <i class="fas fa-coffee"></i>
                    <span>Cozy Beverage</span>
                </a>
            </div>
            <div class="nav-menu" id="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Cart
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="map.php" class="nav-link">Map</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="admin/" class="nav-link">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">Logout</a>
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
                    <h2>Order Confirmed!</h2>
                    <p>Thank you for your order. We'll start preparing it right away.</p>
                </div>
                
                <div class="order-details">
                    <h3>Order Details</h3>
                    <div class="order-info">
                        <div class="info-item">
                            <span class="label">Order ID:</span>
                            <span class="value">#<?php echo $order['id']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo formatDate($order['created_at']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Status:</span>
                            <span class="value status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Amount:</span>
                            <span class="value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h4>Items Ordered</h4>
                        <?php foreach($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                    <p>Price: $<?php echo number_format($item['price'], 2); ?> each</p>
                                </div>
                                <div class="item-total">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
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
                            <p>We're preparing your order with care.</p>
                        </div>
                        <div class="step">
                            <i class="fas fa-shipping-fast"></i>
                            <h4>Ready for Pickup</h4>
                            <p>We'll notify you when your order is ready.</p>
                        </div>
                        <div class="step">
                            <i class="fas fa-coffee"></i>
                            <h4>Enjoy!</h4>
                            <p>Pick up your order and enjoy your beverages!</p>
                        </div>
                    </div>
                </div>
                
                <div class="confirmation-actions">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Cozy Beverage</h3>
                    <p>Your perfect companion for a relaxing beverage experience.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="map.php">Location</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: info@cozybeverage.com</p>
                    <p>Phone: (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Cozy Beverage. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html> 