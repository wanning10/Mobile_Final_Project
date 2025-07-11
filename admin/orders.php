<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
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
    header('Location: orders.php');
    exit();
}

// Handle Delete Order
if (isset($_POST['delete_order'])) {
    $orderId = intval($_POST['order_id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    header('Location: orders.php');
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
                <a href="index.php" class="nav-link">Admin</a>
                <a href="orders.php" class="nav-link active">Orders</a>
                <a href="../logout.php" class="nav-link">Logout</a>
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
            <h2><i class="fas fa-list"></i> Manage Orders</h2>

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
                            <option value="processing" <?php if ($editOrder['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                            <option value="completed" <?php if ($editOrder['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if ($editOrder['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="edit_order" class="btn">Update Order</button>
                        <a href="orders.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Orders Table -->
            <div class="admin-table">
                <h3>All Orders</h3>
                <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:separate; border-spacing:0 0.5em;">
                    <thead>
                        <tr>
                            <th style="background:#f8f8f8;">Order ID</th>
                            <th style="background:#f8f8f8;">Customer</th>
                            <th style="background:#f8f8f8;">Amount</th>
                            <th style="background:#f8f8f8;">Status</th>
                            <th style="background:#f8f8f8;">Date</th>
                            <th style="background:#f0f0f0;">Actions</th>
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
