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

// Get user info
$user = getUserById($conn, $_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } else {
        // Update profile
        if (updateUserProfile($conn, $_SESSION['user_id'], $email, $currentPassword, $newPassword)) {
            $success = 'Profile updated successfully!';
            $user = getUserById($conn, $_SESSION['user_id']); // Refresh user data
        } else {
            $error = 'Failed to update profile. Please check your current password.';
        }
    }
}

// Get user orders
$userOrders = getUserOrders($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Cozy Beverage</title>
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
                <a href="profile.php" class="nav-link active">Profile</a>
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

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <h2><i class="fas fa-user"></i> My Profile</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <div class="profile-info">
                    <h3>Account Information</h3>
                    <div class="info-item">
                        <span class="label">Username:</span>
                        <span class="value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Member Since:</span>
                        <span class="value"><?php echo formatDate($user['created_at']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Account Type:</span>
                        <span class="value"><?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?></span>
                    </div>
                </div>
                
                <div class="profile-form">
                    <h3>Update Profile</h3>
                    <form method="POST" action="profile.php" id="profile-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password (optional)</label>
                            <input type="password" id="new_password" name="new_password" 
                                   minlength="6" title="Password must be at least 6 characters long">
                            <small>Leave blank to keep current password</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="order-history">
                <h3><i class="fas fa-history"></i> Order History</h3>
                
                <?php if (empty($userOrders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>No orders yet</h4>
                        <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach($userOrders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h4>Order #<?php echo $order['id']; ?></h4>
                                        <p class="order-date"><?php echo formatDate($order['created_at']); ?></p>
                                        <p class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </p>
                                    </div>
                                    <div class="order-total">
                                        <span class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" 
                                       class="btn btn-outline">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="account-actions">
                <h3>Account Actions</h3>
                <div class="actions-grid">
                    <a href="products.php" class="action-card">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>Shop Now</h4>
                        <p>Browse our latest products</p>
                    </a>
                    <a href="cart.php" class="action-card">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>View Cart</h4>
                        <p>Check your shopping cart</p>
                    </a>
                    <a href="map.php" class="action-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Find Us</h4>
                        <p>Get directions to our shop</p>
                    </a>
                    <a href="about.php" class="action-card">
                        <i class="fas fa-info-circle"></i>
                        <h4>About Us</h4>
                        <p>Learn more about Cozy Beverage</p>
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
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value && this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 