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
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

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
        
        // Get payment method (ADD THIS PART)
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');

        // Create order
        $orderId = createOrder($conn, $_SESSION['user_id'], $totalAmount, $shippingAddress, $phone, $paymentMethod);
        
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
    <title>Checkout - CATFE</title>
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

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <!-- <h2><i class="fas fa-credit-card"></i> Checkout</h2> -->
            <h2 class="checkout-title">Checkout</h2>
            <p class="checkout-description">We can't wait to prepare your drinks and treats with care.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="checkout.php" id="checkout-form">
                <div class="checkout-container">
                    <div class="checkout-form">
                        <h3>Shipping Information</h3>
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
                                    placeholder="(60) 123456789">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address *</label>
                                <input type="text" id="address" name="address" required 
                                    placeholder="Jalan Sunsuria, Bandar Sunsuria">
                            </div>
                            
                            <div class="form-row three-cols">
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required
                                        placeholder="Sepang">
                                </div>
                                <div class="form-group">
                                    <label for="state">State *</label>
                                    <input type="text" id="state" name="state" required
                                        placeholder="Selangor">
                                </div>
                                <div class="form-group">
                                    <label for="zip">ZIP Code *</label>
                                    <input type="text" id="zip" name="zip" required 
                                        placeholder="43900">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" value="Malaysia" readonly />
                            </div>

                            <!-- <div class="form-group">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    <i class="fas fa-credit-card"></i> Place Order
                                </button>
                            </div> -->
                    </div>

                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div class="order-items">
                            <?php foreach($cartItems as $item): ?>
                                <div class="order-item">
                                    <div class="item-info-with-image">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                            alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
                                        <div class="item-text">
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                                        </div>
                                    </div>
                                    <div class="item-price">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-line">
                                <span>Subtotal:</span>
                                <span>RM <?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                            <div class="total-line">
                                <span>Tax (8.5%):</span>
                                <span>RM <?php echo number_format($cartTotal * 0.085, 2); ?></span>
                            </div>
                            <div class="total-line total">
                                <span>Total:</span>
                                <span>RM <?php echo number_format($cartTotal * 1.085, 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Payment Method Section -->
                        <div class="payment-method">
                            <h4>Payment Method</h4>

                            <!-- Credit/Debit Card -->
                            <div class="payment-option">
                                <label class="payment-label-credit">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                <div class="payment-text-icons">
                                    <span class="payment-name-credit">Credit / Debit Card</span>
                                    <div class="card-icons">
                                    <img src="assets/images/payment-method/visa-new.png" alt="Visa" class="payment-icon">
                                    <img src="assets/images/payment-method/mastercard-new.png" alt="MasterCard" class="payment-icon">
                                    </div>
                                </div>
                                </label>

                                <!-- Credit Card Details -->
                                <div class="card-details">
                                <div class="form-group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                    <label for="expiry">Expiry (MM/YY)</label>
                                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY">
                                    </div>
                                    <div class="form-group">
                                    <label for="cvc">Card Code</label>
                                    <input type="text" id="cvc" name="cvc" placeholder="CVC">
                                    </div>
                                </div>
                                </div>
                            </div>

                            <!-- PayPal -->
                            <div class="payment-option">
                                <label class="payment-label" for="paypal">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <span class="payment-name">PayPal</span>
                                <img src="assets/images/payment-method/paypal.webp" alt="PayPal" class="payment-icon">
                                </label>
                            </div>

                            <!-- GrabPay -->
                            <div class="payment-option">
                                <label class="payment-label" for="grabpay">
                                <input type="radio" id="grabpay" name="payment_method" value="grabpay">
                                <span class="payment-name">GrabPay</span>
                                <img src="assets/images/payment-method/grabpay.png" alt="GrabPay" class="payment-icon">
                                </label>
                            </div>
                        </div>

                        <div class="order-actions">
                            <!-- <a href="cart.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a> -->
                            <button type="submit" form="checkout-form" class="btn btn-primary" style="width: 100%;">
                                Place Order
                                <!-- <i class="fas fa-credit-card"></i> Place Order -->
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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

    <script src="assets/js/main.js"></script>
    <script>
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            let errors = [];

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            const state = document.getElementById('state').value.trim();
            const zip = document.getElementById('zip').value.trim();

            // Add payment validation
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const cardNumber = document.getElementById('card_number').value.trim();
            const expiry = document.getElementById('expiry').value.trim();
            const cvc = document.getElementById('cvc').value.trim();
            
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

            // Payment method validation
            if (!paymentMethod) {
                errors.push('Please select a payment method');
            } else if (paymentMethod.value === 'credit_card') {
                if (!cardNumber) errors.push('Card number is required');
                if (!expiry) errors.push('Expiry date is required');
                if (!cvc) errors.push('CVC is required');
                
                // Additional credit card validation if needed
                if (cardNumber && !cardNumber.replace(/\s/g, '').match(/^\d{13,16}$/)) {
                    errors.push('Please enter a valid card number');
                }
                if (expiry && !expiry.match(/^(0[1-9]|1[0-2])\/?([0-9]{2})$/)) {
                    errors.push('Please enter a valid expiry date (MM/YY)');
                }
                if (cvc && !cvc.match(/^\d{3,4}$/)) {
                    errors.push('Please enter a valid CVC (3 or 4 digits)');
                }
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
            }
        });
    </script>
</body>
</html> 