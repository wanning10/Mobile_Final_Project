<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// For nav bar dropdowns
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Ensure user is admin
if (!$isAdmin) {
    header('Location: ../login.php');
    exit();
}

// Handle Edit Order (status only)
$editOrder = null;
if (isset($_GET['edit'])) {
    $editOrderId = intval($_GET['edit']);
    $orders = getAllOrders($conn);
    foreach ($orders as $order) {
        if ($order['id'] == $editOrderId) {
            $editOrder = $order;
            break;
        }
    }
}

// Handle Update Order (status only)
if (isset($_POST['edit_order'])) {
    $orderId = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    header('Location: orders.php?updated=1');
    exit();
}

// Handle Delete Order
if (isset($_POST['delete_order'])) {
    $orderId = intval($_POST['order_id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    header('Location: orders.php?deleted=1');
    exit();
}

// Get all orders
$orders = getAllOrders($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="logged-in">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <img src="../assets/images/logo.png" alt="Logo" style="height:30px;">
                    <span>CATFE</span>
                </a>
            </div>
            <div class="nav-menu" id="nav-menu">
            <!-- Centered menu links -->
            <!-- <div class="nav-center-menu"> -->
                <a href="../index.php" class="nav-link">HOME</a>
                <a href="../about.php" class="nav-link">ABOUT</a>
                <a href="../products.php" class="nav-link">PRODUCTS</a>
                <a href="../map.php" class="nav-link">MAP</a>
            <!-- </div> -->
            <!-- Right icons (cart & profile) -->
            <!-- <div class="nav-right-icons"> -->
                <a href="../cart.php" class="nav-link">
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
                        <a href="../profile.php">My Profile</a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
                <?php if($isAdmin): ?>
                <div class="admin-dropdown">
                    <button class="admin-toggle nav-link active" href="#">
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

    <section class="admin-section">
        <div class="container">
            <h2 style="color: #333;">Manage Orders</h2>
            <!-- Edit Order Form (inline, only if editing) -->
            <?php if ($editOrder): ?>
            <div class="admin-form">
                <h3>Edit Order #<?php echo $editOrder['id']; ?></h3>
                <form method="post" action="">
                    <input type="hidden" name="order_id" value="<?php echo $editOrder['id']; ?>">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="pending" <?php if ($editOrder['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="confirmed" <?php if ($editOrder['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                            <option value="shipped" <?php if ($editOrder['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="delivered" <?php if ($editOrder['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="cancelled" <?php if ($editOrder['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="edit_order" class="btn" style="background-color: #8B4513; color: white;">Update Order</button>
                        <a href="orders.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Orders Table -->
            <div class="admin-table">
                <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="background:#f8f8f8;">Order ID</th>
                            <th style="background:#f8f8f8;">Customer</th>
                            <th style="background:#f8f8f8;">Amount (RM)</th>
                            <th style="background:#f8f8f8;">Status</th>
                            <th style="background:#f8f8f8;">Date</th>
                            <th style="background:#f8f8f8;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td>
                                <a href="orders.php?edit=<?php echo $order['id']; ?>" class="btn btn-small"><i class="fas fa-edit"></i></a>
                                <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="delete_order" class="btn btn-small btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section" style="padding-right: 3em;">
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 30px;">
                        CATFE
                    </h3>
                    <p>Sip, unwind, and enjoy the gentle company of cats.
                        Catfe brings comfort, quality coffee, and calm all in one cozy space.</p>
                </div>
                <!-- <div class="footer-section" style="padding-left: 5em;"> -->
                <div class="footer-section quick-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../products.php">Products</a></li>
                        <li><a href="../about.php">About</a></li>
                        <li><a href="../map.php">Map</a></li>
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
    <script src="../assets/js/main.js"></script>
    <script>
        // Admin Dropdown
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');
        adminToggle.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        window.addEventListener('click', function (e) {
            if (!e.target.closest('.admin-dropdown')) {
                dropdownMenu.style.display = 'none';
            }
        });
    </script>
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
</body>
<?php if (isset($_GET['updated'])): ?>
<script>
window.onload = function() {
    alert('Order has been successfully updated.');
}
</script>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<script>
window.onload = function() {
    alert('Order has been successfully deleted.');
}
</script>
<?php endif; ?>
</html>
