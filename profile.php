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

$error = '';
$success = '';

// Get user info
$user = getUserById($conn, $_SESSION['user_id']);

// Handle profile update (keep your logic, you may need a popup/modal for the new design)
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $email = sanitizeInput($_POST['email']);
//     $currentPassword = $_POST['current_password'];
//     $newPassword = $_POST['new_password'];
//     $confirmPassword = $_POST['confirm_password'];

//     // Validation
//     if (empty($email)) {
//         $error = 'Email is required';
//     } elseif (!validateEmail($email)) {
//         $error = 'Please enter a valid email address';
//     } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
//         $error = 'New password must be at least 6 characters long';
//     } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
//         $error = 'New passwords do not match';
//     } else {
//         // Update profile
//         if (updateUserProfile($conn, $_SESSION['user_id'], $email, $currentPassword, $newPassword)) {
//             $success = 'Profile updated successfully!';
//             $user = getUserById($conn, $_SESSION['user_id']); // Refresh user data
//         } else {
//             $error = 'Failed to update profile. Please check your current password.';
//         }
//     }
// }
// Handle profile update


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profile'])) {
    $editType = $_POST['edit_type'] ?? '';
    if ($editType === 'username') {
        $username = sanitizeInput($_POST['username']);
        if (empty($username)) {
            $error = 'Username is required';
        } else {
            if (updateUserProfile($conn, $_SESSION['user_id'], $username, $user['email'], '', '')) {
                $success = 'Username updated successfully!';
                $user = getUserById($conn, $_SESSION['user_id']);
            } else {
                $error = 'Failed to update username.';
            }
        }
    } else if ($editType === 'email') {
        $email = sanitizeInput($_POST['email']);
        if (empty($email) || !validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            if (updateUserProfile($conn, $_SESSION['user_id'], $user['username'], $email, '', '')) {
                $success = 'Email updated successfully!';
                $user = getUserById($conn, $_SESSION['user_id']);
            } else {
                $error = 'Failed to update email.';
            }
        }
    } else if ($editType === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (empty($currentPassword) || empty($newPassword) || $newPassword !== $confirmPassword || strlen($newPassword) < 6) {
            $error = 'Password invalid or mismatch';
        } else {
            if (updateUserProfile($conn, $_SESSION['user_id'], $user['username'], $user['email'], $currentPassword, $newPassword)) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Failed to update password. Please check your current password.';
            }
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
    <title>Profile - CATFE</title>
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
                        <a href="#" class="nav-link active" id="profile-icon">
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

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <h2 style="color: #333;">My Profile</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="profile-container">
                <!-- LEFT: Basic Info -->
                <div class="profile-info">
                    <div class="profile-header centered-header">
                        <!-- <img src="assets/images/profile-pic.png" alt="Profile Picture" class="profile-avatar"> -->
                        <div class="profile-username">
                            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="label">Username:</span>
                        <span class="value">
                            <?php echo htmlspecialchars($user['username']); ?>
                             <a href="#" class="edit-icon" onclick="openEditModal(event, 'username')"><i class="fas fa-pen"></i></a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Member Since:</span>
                        <span class="value"><?php echo formatDate($user['created_at']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Account Type:</span>
                        <span class="value"><?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email:</span>
                        <span class="value">
                            <?php echo htmlspecialchars($user['email']); ?>
                            <a href="#" class="edit-icon" onclick="openEditModal(event, 'email')"><i class="fas fa-pen"></i></a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Password:</span>
                        <span class="value">
                            ********
                           <a href="#" class="edit-icon" onclick="openEditModal(event, 'password')"><i class="fas fa-pen"></i></a>
                        </span>
                    </div>
                </div>
                <!-- RIGHT: Order History -->
                <div class="order-history">
                    <h3 style="font-size: 1.2rem;"><i class="fas fa-history"></i> Order History</h3>
                    <?php if (empty($userOrders)): ?>
                        <div class="order-history-content">
                            <div class="no-orders">
                                <i class="fas fa-shopping-bag"></i>
                                <h4>No orders yet</h4>
                                <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                                <a href="products.php" class="btn btn-primary">Start Shopping</a>
                            </div>
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
                                            <span class="total-amount">RM <?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="account-actions">
                <h3 style="color: #333; font-size: 1.2rem; margin-bottom: 1.5rem;">Account Actions</h3>
                <div class="actions-grid">
                    <a href="products.php" class="action-card">
                        <i class="fas fas fa-store"></i>
                        <h4>Shop Now</h4>
                        <p>Browse our latest products</p>
                    </a>
                    <a href="cart.php" class="action-card">
                        <i class="fas fas fa-cart-arrow-down"></i>
                        <h4>View Cart</h4>
                        <p>Check your shopping cart</p>
                    </a>
                    <a href="map.php" class="action-card">
                        <i class="fas fas fa-map-location-dot"></i>
                        <h4>Find Us</h4>
                        <p>Get directions to our shop</p>
                    </a>
                    <a href="about.php" class="action-card">
                        <i class="fas fas fa-circle-question"></i>
                        <h4>About Us</h4>
                        <p>Learn more about CATFE</p>
                    </a>
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
        // Admin Dropdown
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');
        if (adminToggle && dropdownMenu) {
            adminToggle.addEventListener('click', () => {
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });
            window.addEventListener('click', function (e) {
                if (!e.target.closest('.admin-dropdown')) {
                    dropdownMenu.style.display = 'none';
                }
            });
        }

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
    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="edit-modal" style="display:none;">
        <div class="edit-modal-content">
            <span class="close-edit-modal" id="closeModalBtn">&times;</span>
            <h2 id="modalTitle">Edit Profile</h2>
            <form method="POST" action="profile.php" id="edit-profile-form">
                <input type="hidden" name="edit_profile" value="1">
                <input type="hidden" name="edit_type" id="editType">

                <!-- Username -->
                <div id="editUsernameField" class="form-group" style="display:none;">
                    <label>Username:</label>
                    <input type="text" name="username" id="editUsernameInput" value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>

                <!-- Email -->
                <div id="editEmailField" class="form-group" style="display:none;">
                    <label>Email:</label>
                    <input type="email" name="email" id="editEmailInput" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <!-- Password -->
                <div id="editPasswordField" style="display:none;">
                    <div class="form-group">
                        <label>Current Password:<span style="color:red;">*</span></label>
                        <input type="password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password:</label>
                        <input type="password" name="confirm_password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <script>
    function openEditModal(e, type) {
        e.preventDefault();
        document.getElementById('editUsernameField').style.display = 'none';
        document.getElementById('editEmailField').style.display = 'none';
        document.getElementById('editPasswordField').style.display = 'none';

        document.getElementById('editType').value = type;
        if (type === 'username') {
            document.getElementById('modalTitle').textContent = 'Edit Username';
            document.getElementById('editUsernameField').style.display = 'block';
            document.getElementById('editUsernameInput').value = "<?php echo htmlspecialchars($user['username']); ?>";
        } else if (type === 'email') {
            document.getElementById('modalTitle').textContent = 'Edit Email';
            document.getElementById('editEmailField').style.display = 'block';
            document.getElementById('editEmailInput').value = "<?php echo htmlspecialchars($user['email']); ?>";
        } else if (type === 'password') {
            document.getElementById('modalTitle').textContent = 'Change Password';
            document.getElementById('editPasswordField').style.display = 'block';
        }
        document.getElementById('editProfileModal').style.display = 'block';
    }

    // Close modal
    document.getElementById('closeModalBtn').onclick = function() {
        document.getElementById('editProfileModal').style.display = 'none';
    };
    window.onclick = function(event) {
        var modal = document.getElementById('editProfileModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    </script>
</body>
</html>
