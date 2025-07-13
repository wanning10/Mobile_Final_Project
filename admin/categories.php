<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $icon = sanitizeInput($_POST['icon']);
    $image = sanitizeInput($_POST['image']);
    if (!empty($name)) {
        createCategory($conn, $name, $description, $image, $icon);
        header('Location: categories.php');
        exit();
    }
}

// Handle Edit Category
if (isset($_POST['edit_category'])) {
    $id = intval($_POST['category_id']);
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $icon = sanitizeInput($_POST['icon']);
    $image = sanitizeInput($_POST['image']);
    if (!empty($name)) {
        updateCategory($conn, $id, $name, $description, $image, $icon);
        header('Location: categories.php');
        exit();
    }
}

// Handle Delete Category
if (isset($_POST['delete_category'])) {
    $id = intval($_POST['category_id']);
    deleteCategory($conn, $id);
    header('Location: categories.php');
    exit();
}

// Get all categories
$categories = getCategories($conn);

// For editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $editCategory = getCategoryById($conn, intval($_GET['edit']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
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
                <a href="categories.php" class="nav-link active">Categories</a>
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
            <h2><i class="fas fa-tags"></i> Manage Categories</h2>

            <!-- Add Category Button (top right) -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 1em;">
                <button class="btn btn-primary" id="show-add-category">Add Category</button>
            </div>

            <!-- Add Category Popup Modal -->
            <div class="modal" id="addCategoryModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeAddCategory" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3>Add Category</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" name="description" id="description">
                        </div>
                        <div class="form-group">
                            <label for="icon">Icon (FontAwesome class)</label>
                            <input type="text" name="icon" id="icon" value="fas fa-coffee">
                        </div>
                        <div class="form-group">
                            <label for="image">Image URL</label>
                            <input type="text" name="image" id="image">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_category" class="btn">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Category Form (inline, only if editing) -->
            <?php if ($editCategory): ?>
            <div class="admin-form">
                <h3>Edit Category</h3>
                <form method="post" action="">
                    <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($editCategory['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($editCategory['description']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon (FontAwesome class)</label>
                        <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($editCategory['icon']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="text" name="image" id="image" value="<?php echo htmlspecialchars($editCategory['image']); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="edit_category" class="btn">Update Category</button>
                        <a href="categories.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Categories Table -->
            <div class="admin-table">
                <h3>All Categories</h3>
                <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:separate; border-spacing:0 0.5em;">
                    <thead>
                        <tr>
                            <!-- Left side fields -->
                            <th style="background:#f8f8f8;">ID</th>
                            <th style="background:#f8f8f8;">Name</th>
                            <th style="background:#f8f8f8;">Description</th>
                            <!-- Right side fields -->
                            <th style="background:#f0f0f0;">Icon</th>
                            <th style="background:#f0f0f0;">Image</th>
                            <th style="background:#f0f0f0;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <!-- Left side fields -->
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <!-- Right side fields -->
                                <td><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category['icon']); ?></td>
                                <td><?php if ($category['image']): ?><img src="<?php echo htmlspecialchars($category['image']); ?>" alt="Image" style="height:30px;max-width:60px;object-fit:cover;"/><?php endif; ?></td>
                                <td>
                                    <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-small"><i class="fas fa-edit"></i></a>
                                    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-small btn-danger"><i class="fas fa-trash"></i></button>
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
