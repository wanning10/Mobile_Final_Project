<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

if (!$isAdmin) {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$totalUsers = count(getAllUsers($conn));
$totalOrders = count(getAllOrders($conn));
$totalProducts = count(getProducts($conn));
$totalCategories = count(getCategories($conn));

// Get recent orders (not used in new design)
// $recentOrders = getAllOrders($conn);
// $recentOrders = array_slice($recentOrders, 0, 5); // Not needed now
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cozy Beverage</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="logged-in">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo, left -->
            <div class="nav-logo">
                <a href="index.php">
                    <img src="../assets/images/logo.png" alt="Logo" style="height:30px;">
                    <span>CATFE</span>
                </a>
            </div>
            <!-- Center menu -->
            <div class="nav-center-menu">
                <a href="../index.php" class="nav-link">HOME</a>
                <a href="../about.php" class="nav-link">ABOUT</a>
                <a href="../products.php" class="nav-link">PRODUCTS</a>
                <a href="../map.php" class="nav-link">MAP</a>
            </div>
            <!-- Right icons -->
            <div class="nav-right-icons">
                <a href="../cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <!-- Profile Dropdown -->
                <div class="profile-dropdown">
                    <a href="#" class="nav-link" id="profile-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-content" id="profile-dropdown">
                        <a href="../profile.php">My Profile</a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
                <?php if($isAdmin): ?>
                <!-- Admin Dropdown -->
                <div class="admin-dropdown">
                    <button class="admin-toggle nav-link active" type="button">
                        <span>ADMIN</span>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="index.php">Dashboard</a>
                        <a href="products.php">Manage Products</a>
                        <a href="categories.php">Manage Categories</a>
                        <a href="users.php">Manage Users</a>
                        <a href="orders.php">Manage Orders</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Admin Dashboard Section -->
    <section class="admin-section">
        <div class="container">
            <h2 style="color: #333;">Admin Dashboard</h2>
            <div class="admin-dashboard">
                <div class="admin-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $totalUsers; ?></div>
                </div>
                <div class="admin-card">
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $totalProducts; ?></div>
                </div>
                <div class="admin-card">
                    <h3>Total Categories</h3>
                    <div class="number"><?php echo $totalCategories; ?></div>
                </div>
                <div class="admin-card">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $totalOrders; ?></div>
                </div>
            </div>

            <div class="admin-content">
                <div class="admin-actions">
                    <h3 style="margin-top: 1rem; font-size: 1.5rem; color: #8B4513;">Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="products.php" class="action-card">
                            <i class="fas fa-box-open"></i>
                            <h4>Manage Products</h4>
                            <p>Add new products to the catalog</p>
                        </a>
                        <a href="categories.php" class="action-card">
                            <i class="fas fa-tags"></i>
                            <h4>Manage Categories</h4>
                            <p>Organize products by categories</p>
                        </a>
                        <a href="users.php" class="action-card">
                            <i class="fas fa-users"></i>
                            <h4>Manage Users</h4>
                            <p>View and manage user accounts</p>
                        </a>
                        <a href="orders.php" class="action-card">
                            <i class="fas fa-receipt"></i>
                            <h4>Manage Orders</h4>
                            <p>See all customer orders</p>
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
                        <li><a href="../products.php">Products</a></li>
                        <li><a href="../about.php">About</a></li>
                        <li><a href="../map.php">Location</a></li>
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

    <script src="../assets/js/main.js"></script>
    <script>
        // Admin Dropdown Toggle
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');
        if(adminToggle && dropdownMenu){
            adminToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });
            window.addEventListener('click', function (e) {
                if (!e.target.closest('.admin-dropdown')) {
                    dropdownMenu.style.display = 'none';
                }
            });
        }

        // Profile Dropdown Toggle
        document.getElementById("profile-icon").addEventListener("click", function(e){
            e.preventDefault();
            var dropdown = document.getElementById("profile-dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });
        window.addEventListener("click", function(e){
            if (!e.target.closest('.profile-dropdown')) {
                var dropdown = document.getElementById("profile-dropdown");
                if (dropdown) dropdown.style.display = "none";
            }
        });
    </script>
</body>
</html>
