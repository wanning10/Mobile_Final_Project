<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$totalUsers = count(getAllUsers($conn));
$totalOrders = count(getAllOrders($conn));
$totalProducts = count(getProducts($conn));
$totalCategories = count(getCategories($conn));

// Get recent orders
$recentOrders = getAllOrders($conn);
$recentOrders = array_slice($recentOrders, 0, 5); // Get only 5 most recent
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
            <div class="nav-logo">
                <a href="../index.php">
                    <i class="fas fa-coffee"></i>
                    <span>Cozy Beverage</span>
                </a>
            </div>
            <div class="nav-menu" id="nav-menu">
                <a href="../index.php" class="nav-link">Home</a>
                <a href="../products.php" class="nav-link">Products</a>
                <a href="../cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Cart
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="../map.php" class="nav-link">Map</a>
                <a href="../about.php" class="nav-link">About</a>
                <a href="../profile.php" class="nav-link">Profile</a>
                <a href="index.php" class="nav-link active">Admin</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Admin Dashboard -->
    <section class="admin-section">
        <div class="container">
            <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            
            <div class="admin-dashboard">
                <div class="admin-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Users</h3>
                    <div class="number"><?php echo $totalUsers; ?></div>
                    <a href="users.php" class="btn btn-outline">Manage Users</a>
                </div>
                
                <div class="admin-card">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $totalProducts; ?></div>
                    <a href="products.php" class="btn btn-outline">Manage Products</a>
                </div>
                
                <div class="admin-card">
                    <i class="fas fa-tags"></i>
                    <h3>Total Categories</h3>
                    <div class="number"><?php echo $totalCategories; ?></div>
                    <a href="categories.php" class="btn btn-outline">Manage Categories</a>
                </div>
                
                <div class="admin-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $totalOrders; ?></div>
                    <a href="orders.php" class="btn btn-outline">Manage Orders</a>
                </div>
            </div>
            
            <div class="admin-content">
                <div class="recent-orders">
                    <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                    <?php if (empty($recentOrders)): ?>
                        <p>No orders yet.</p>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-small">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="admin-actions">
                    <h3>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="products.php" class="action-card">
                            <i class="fas fa-plus"></i>
                            <h4>Add Product</h4>
                            <p>Add new products to the catalog</p>
                        </a>
                        <a href="categories.php" class="action-card">
                            <i class="fas fa-tags"></i>
                            <h4>Manage Categories</h4>
                            <p>Organize products by categories</p>
                        </a>
                        <a href="users.php" class="action-card">
                            <i class="fas fa-user-cog"></i>
                            <h4>Manage Users</h4>
                            <p>View and manage user accounts</p>
                        </a>
                        <a href="orders.php" class="action-card">
                            <i class="fas fa-list"></i>
                            <h4>View Orders</h4>
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
</body>
</html> 