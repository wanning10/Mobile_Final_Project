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

$error = '';
$success = '';

// Check for session messages
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Get cart items
$cartItems = getCartItems($conn, $_SESSION['user_id']);
$cartTotal = getCartTotal($conn, $_SESSION['user_id']);

// Update cart count in session
updateCartCount($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Cozy Beverage</title>
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
                <a href="cart.php" class="nav-link active">
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

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <h2><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items" id="cart-items">
                        <?php foreach($cartItems as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="cart-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                </div>
                                <div class="cart-item-actions">
                                    <div class="quantity-controls">
                                        <label for="quantity-<?php echo $item['product_id']; ?>">Quantity:</label>
                                        <input type="number" 
                                               id="quantity-<?php echo $item['product_id']; ?>" 
                                               class="cart-quantity" 
                                               data-product-id="<?php echo $item['product_id']; ?>"
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="99">
                                    </div>
                                    <div class="cart-item-total">
                                        Total: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </div>
                                    <button class="btn btn-secondary remove-from-cart" 
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="cart-total">
                            <h3>Cart Summary</h3>
                            <div class="summary-item">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Tax (8.5%):</span>
                                <span>$<?php echo number_format($cartTotal * 0.085, 2); ?></span>
                            </div>
                            <div class="summary-item total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($cartTotal * 1.085, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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