<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Handle Edit User
$editUser = null;
if (isset($_GET['edit'])) {
    $editUser = getUserById($conn, intval($_GET['edit']));
}

// Handle Update User
if (isset($_POST['edit_user'])) {
    $userId = intval($_POST['user_id']);
    $email = sanitizeInput($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = !empty($_POST['new_password']) ? $_POST['new_password'] : null;
    $result = updateUserProfile($conn, $userId, $email, $currentPassword, $newPassword);
    if ($result) {
        header('Location: users.php');
        exit();
    } else {
        $editUser = getUserById($conn, $userId);
        $error = 'Failed to update user. Please check the current password.';
    }
}

// Get all users
$users = getAllUsers($conn);
// Exclude the currently logged-in admin from the list
$users = array_filter($users, function($user) {
    return $user['id'] != $_SESSION['user_id'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
                <a href="users.php" class="nav-link active">Users</a>
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
            <h2><i class="fas fa-user-cog"></i> Manage Users</h2>

            <!-- Edit User Form -->
            <?php if ($editUser): ?>
                <div class="admin-form">
                    <h3>Edit User: <?php echo htmlspecialchars($editUser['username']); ?></h3>
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($editUser['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="current_password">Current Password <span style="color:red">*</span></label>
                            <input type="password" name="current_password" id="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password (leave blank to keep current)</label>
                            <input type="password" name="new_password" id="new_password">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="edit_user" class="btn">Update User</button>
                            <a href="users.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="admin-table">
                <h3>All Users</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                                <td><?php echo isset($user['created_at']) ? htmlspecialchars($user['created_at']) : '-'; ?></td>
                                <td>
                                    <a href="users.php?edit=<?php echo $user['id']; ?>" class="btn btn-small"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
