<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// // Check if user is logged in
// $isLoggedIn = isset($_SESSION['user_id']);
// if (!$isLoggedIn) {
//     header('Location: login.php');
//     exit();
// }

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

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
// $cartItems = getCartItems($conn, $_SESSION['user_id']);
// $cartTotal = getCartTotal($conn, $_SESSION['user_id']);

// // Update cart count in session
// updateCartCount($conn, $_SESSION['user_id']);
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

$cartItems = getCartItems($conn, $_SESSION['user_id']);
$cartTotal = getCartTotal($conn, $_SESSION['user_id']);
updateCartCount($conn, $_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - CATFE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

            <!-- Centered menu links -->
            <div class="nav-center-menu">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <a href="products.php" class="nav-link">PRODUCTS</a>
                <a href="map.php" class="nav-link">MAP</a>
            </div>

            <!-- Right icons (cart & profile) -->
            <div class="nav-right-icons">
                <a href="cart.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <div class="profile-dropdown">
                    <a href="#" class="nav-link" id="profile-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-content" id="profile-dropdown">
                        <a href="profile.php">My Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
                <?php if($isAdmin): ?>
                    <a href="admin/" class="nav-link">ADMIN</a>
                <?php endif; ?>
            </div>

            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Cart Section -->
    <!-- <section class="cart-section">
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
    </section> -->

    <section class="cart-section">
        <div class="container">
            <!-- Shopping Cart Title -->
            <h2 class="cart-title">
                Shopping Cart
            </h2>
            <p class="cart-description">
                Review your items below before proceeding to checkout.
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart-container">
                    <div class="empty-cart-content">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h2 class="empty-cart-title">Your Cart is Empty</h2>
                        <p class="empty-cart-message">Looks like you haven't added any items yet</p>
                        <a href="products.php" class="btn btn-primary empty-cart-button">
                            <i class="fas fa-utensils"></i> Browse Menu
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart container left-right -->
                <div class="cart-container">
                    <!-- Left: Cart Items -->
                    <div class="cart-items-container">
                        <!-- Cart Header -->
                        <div class="cart-header">
                            <div>Product</div>
                            <div>Price</div>
                            <div>Quantity</div>
                            <div>Total</div>
                        </div>

                        <?php foreach($cartItems as $item): ?>
                            <!-- Cart Item -->
                            <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="cart-item-info">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div>
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="product-category"><?php echo htmlspecialchars($item['category'] ?? 'Bakery'); ?></p>
                                    </div>
                                </div>
                                <div class="cart-item-price">RM <?php echo number_format($item['price'], 2); ?></div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn minus" data-product-id="<?php echo $item['product_id']; ?>">-</button>
                                    <input type="number" 
                                        class="cart-quantity" 
                                        data-product-id="<?php echo $item['product_id']; ?>"
                                        value="<?php echo $item['quantity']; ?>" 
                                        min="1" max="99">
                                    <button class="quantity-btn plus" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                </div>
                                <div class="cart-item-total">
                                    RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    <button class="remove-from-cart" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Right: Cart Summary -->
                    <div class="cart-summary">
                        <h3>Cart Summary</h3>
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span>RM <?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Tax (8.5%):</span>
                            <span>RM <?php echo number_format($cartTotal * 0.085, 2); ?></span>
                        </div>
                        <div class="summary-item total">
                            <span>Total:</span>
                            <span>RM <?php echo number_format($cartTotal * 1.085, 2); ?></span>
                        </div>
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-secondary">
                                Continue Shopping
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
                <div class="footer-section" style="padding-right: 3em;">
                    <!-- <h3>CATFE</h3> -->
                     <h3 style="display: flex; align-items: center; gap: 8px;">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 30px;">
                        CATFE
                    </h3>
                    <p>Sip, unwind, and enjoy the gentle company of cats.
                        Catfe brings comfort, quality coffee, and calm all in one cozy space.</p>
                </div>
                <div class="footer-section" style="padding-left: 5em;">
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
    
    <script>
        document.getElementById("profile-icon").addEventListener("click", function(e){
            e.preventDefault();
            var dropdown = document.getElementById("profile-dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });

        // Close dropdown when clicking outside
        window.addEventListener("click", function(e){
            if (!e.target.matches('#profile-icon, #profile-icon *')) {
                var dropdown = document.getElementById("profile-dropdown");
                if (dropdown) dropdown.style.display = "none";
            }
        });
    </script>

    <script src="assets/js/main.js"></script>
</body>
</html> 