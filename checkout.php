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

// Get cart items
$cartItems = getCartItems($conn, $_SESSION['user_id']);
$cartTotal = getCartTotal($conn, $_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cartItems) || $cartTotal <= 0) {
    $_SESSION['error'] = 'Your cart is empty. Please add some items before checkout.';
    header('Location: cart.php');
    exit();
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $zip = sanitizeInput($_POST['zip']);
    
    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($zip)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($phone) < 10) {
        $error = 'Please enter a valid phone number';
    } elseif (strlen($zip) < 5) {
        $error = 'Please enter a valid ZIP code';
    } else {
        // Calculate total with tax
        $tax = $cartTotal * 0.085;
        $totalAmount = $cartTotal + $tax;
        
        // Prepare shipping address
        $shippingAddress = $address . ', ' . $city . ', ' . $state . ' ' . $zip;
        
        // Create order
        $orderId = createOrder($conn, $_SESSION['user_id'], $totalAmount, $shippingAddress, $phone);
        
        if ($orderId) {
            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=$orderId");
            exit();
        } else {
            $error = 'Failed to create order. Please check your cart and try again.';
        }
    }
}

// Get user info
$user = getUserById($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cozy Beverage</title>
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

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <h2><i class="fas fa-credit-card"></i> Checkout</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="checkout-container">
                <div class="checkout-form">
                    <h3>Shipping Information</h3>
                    <form method="POST" action="checkout.php" id="checkout-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required 
                                   placeholder="(555) 123-4567">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address *</label>
                            <input type="text" id="address" name="address" required 
                                   placeholder="123 Coffee Street">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" id="state" name="state" required>
                            </div>
                            <div class="form-group">
                                <label for="zip">ZIP Code *</label>
                                <input type="text" id="zip" name="zip" required 
                                   placeholder="12345">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-credit-card"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div class="order-items">
                        <?php foreach($cartItems as $item): ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-price">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <div class="total-line">
                            <span>Tax (8.5%):</span>
                            <span>$<?php echo number_format($cartTotal * 0.085, 2); ?></span>
                        </div>
                        <div class="total-line total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($cartTotal * 1.085, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
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
    <script>
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            const state = document.getElementById('state').value.trim();
            const zip = document.getElementById('zip').value.trim();
            
            let errors = [];
            
            if (!name) errors.push('Full name is required');
            if (!email) errors.push('Email is required');
            if (!phone) errors.push('Phone number is required');
            if (!address) errors.push('Address is required');
            if (!city) errors.push('City is required');
            if (!state) errors.push('State is required');
            if (!zip) errors.push('ZIP code is required');
            
            if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                errors.push('Please enter a valid email address');
            }
            
            if (phone && phone.replace(/\D/g, '').length < 10) {
                errors.push('Please enter a valid phone number');
            }
            
            if (zip && zip.length < 5) {
                errors.push('Please enter a valid ZIP code');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
            }
        });
    </script>
</body>
</html> 