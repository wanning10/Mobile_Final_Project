<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Define isLoggedIn and isAdmin for navigation use
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Check if user is logged in and is admin
if (!$isAdmin) {
    header('Location: ../login.php');
    exit();
}

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
        header('Location: categories.php?added=1');
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
        header('Location: categories.php?updated=1');
        exit();
    }
}

// Handle Delete Category
if (isset($_POST['delete_category'])) {
    $id = intval($_POST['category_id']);
    deleteCategory($conn, $id);
    header('Location: categories.php?deleted=1');
    exit();
}

// Get all categories (IMPORTANT: this is the array for your foreach)
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
            <h2 style="color: #333;">Manage Categories</h2>
            <button class="btn btn-primary" id="show-add-category" style="margin-bottom: 40px;">Add Category</button>

            <!-- Add Category Modal -->
            <div class="modal" id="addCategoryModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeAddCategory" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3 style="color: #8B4513; margin-bottom:1em;">Add Category</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <div class="form-group">
                            <label for="name" style="color: #333;">Name</label>
                            <input type="text" name="name" id="name" required pattern="[A-Za-z\s]+" title="Name must contain only letters and spaces">
                        </div>
                        <div class="form-group">
                            <label for="description" style="color: #333;">Description</label>
                            <input type="text" name="description" id="description">
                        </div>
                        <div class="form-group">
                            <label for="icon" style="color: #333;">Icon</label>
                            <input type="text" name="icon" id="icon" value="fas fa-coffee">
                        </div>
                        <div class="form-group">
                            <label for="image" style="color: #333;">Image URL</label>
                            <input type="text" name="image" id="image">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_category" class="btn" style="background-color: #8B4513; color: white;">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <?php if ($editCategory): ?>
            <div class="modal" id="editCategoryModal" style="display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeEditCategory" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3 style="color: #8B4513; margin-bottom:1em;">Edit Category</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">
                        <div class="form-group">
                            <label for="name" style="color: #333;">Name</label>
                            <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($editCategory['name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="description" style="color: #333;">Description</label>
                            <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($editCategory['description']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="icon" style="color: #333;">Icon</label>
                            <input type="text" name="icon" id="icon" value="<?php echo htmlspecialchars($editCategory['icon']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="image" style="color: #333;">Image URL</label>
                            <input type="text" name="image" id="image" value="<?php echo htmlspecialchars($editCategory['image']); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="edit_category" class="btn" style="background-color: #8B4513; color: white;">Update Category</button>
                            <a href="categories.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Categories Table -->
            <div class="admin-table">
                <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="background:#f8f8f8;">ID</th>
                            <th style="background:#f8f8f8;">Name</th>
                            <th style="background:#f8f8f8;">Description</th>
                            <th style="background:#f0f0f0;">Icon</th>
                            <th style="background:#f0f0f0;">Image</th>
                            <th style="background:#f0f0f0;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category['icon']); ?></td>
                                <!-- <td><?php if ($category['image']): ?><img src="/<?php echo ltrim(htmlspecialchars($category['image']), '/'); ?>" alt="Image" style="height:30px;max-width:60px;object-fit:cover;"/><?php endif; ?></td> -->
                                <td>
                                    <?php if (!empty($category['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($category['image']); ?>" 
                                            alt="<?php echo htmlspecialchars($category['name']); ?>"
                                            style="height:30px;max-width:60px;object-fit:cover;">
                                    <?php else: ?>
                                        <span>No image</span>
                                    <?php endif; ?>
                                </td>
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

    <!-- Footer -->
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
                <div class="footer-section" style="padding-left: 5em;">
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
    // Add Category Modal
    document.addEventListener("DOMContentLoaded", function () {
        const showBtn = document.getElementById("show-add-category");
        const closeBtn = document.getElementById("closeAddCategory");
        const modal = document.getElementById("addCategoryModal");
        if (showBtn && modal && closeBtn) {
            showBtn.addEventListener("click", () => {
                modal.style.display = "flex";
            });
            closeBtn.addEventListener("click", () => {
                modal.style.display = "none";
            });
            // Close when clicking outside modal content
            modal.addEventListener("click", (e) => {
                if (e.target === modal) {
                    modal.style.display = "none";
                }
            });
        }
    });
    </script>
    <script>
    // Edit Category Modal
    document.addEventListener("DOMContentLoaded", function () {
        const editModal = document.getElementById("editCategoryModal");
        const closeEdit = document.getElementById("closeEditCategory");
        if (editModal && closeEdit) {
            closeEdit.addEventListener("click", () => {
                editModal.style.display = "none";
                window.location.href = "categories.php";
            });
            editModal.addEventListener("click", (e) => {
                if (e.target === editModal) {
                    editModal.style.display = "none";
                    window.location.href = "categories.php";
                }
            });
        }
    });
    </script>
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
    window.addEventListener("click", function(e){
        if (!e.target.matches('#profile-icon, #profile-icon *')) {
            var dropdown = document.getElementById("profile-dropdown");
            if (dropdown) dropdown.style.display = "none";
        }
    });
    </script>
</body>
<?php if (isset($_GET['added'])): ?>
<script>
window.onload = function() {
    alert('Category has been successfully added.');
}
</script>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
<script>
window.onload = function() {
    alert('Category has been successfully updated.');
}
</script>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<script>
window.onload = function() {
    alert('Category has been successfully deleted.');
}
</script>
<?php endif; ?>
</html>
