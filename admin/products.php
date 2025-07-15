<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and admin
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
if (!$isAdmin) {
    header('Location: ../login.php');
    exit();
}

// Get categories for form
$categories = getCategories($conn);

// Handle Add Product
if (isset($_POST['add_product'])) {
    $data = [
        'name' => sanitizeInput($_POST['name']),
        'description' => sanitizeInput($_POST['description']),
        'price' => floatval($_POST['price']),
        'category_id' => intval($_POST['category_id']),
        'image_url' => sanitizeInput($_POST['image_url']),
        'stock_quantity' => intval($_POST['stock_quantity']),
        'is_available' => intval($_POST['is_available']),
        'is_featured' => intval($_POST['is_featured'])
    ];
    if (!empty($data['name']) && $data['category_id'] > 0) {
        createProduct($conn, $data);
        header('Location: products.php?added=1');
        exit();
    }
}

// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $data = [
        'name' => sanitizeInput($_POST['name']),
        'description' => sanitizeInput($_POST['description']),
        'price' => floatval($_POST['price']),
        'category_id' => intval($_POST['category_id']),
        'image_url' => sanitizeInput($_POST['image_url']),
        'stock_quantity' => intval($_POST['stock_quantity']),
        'is_available' => intval($_POST['is_available']),
        'is_featured' => intval($_POST['is_featured'])
    ];
    if (!empty($data['name']) && $data['category_id'] > 0) {
        updateProduct($conn, $id, $data);
        header('Location: products.php?updated=1');
        exit();
    }
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = intval($_POST['product_id']);
    deleteProduct($conn, $id);
    header('Location: products.php?deleted=1');
    exit();
}

// Get all products
$products = getProducts($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
                    <button class="admin-toggle nav-link active" href="#"><span>ADMIN</span></button>
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
            <h2 style="color: #333;">Manage Products</h2>
            <button class="btn btn-primary" id="show-add-product" style="margin-bottom: 40px;">Add Product</button>

            <!-- Add Product Modal -->
            <div class="modal" id="addProductModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeAddProduct" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3 style="color: #8B4513; margin-bottom:1em;">Add Product</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <div class="form-group">
                            <label for="name" style="color: #333;">Name</label>
                            <input type="text" name="name" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id" style="color: #333;">Category</label>
                            <select name="category_id" id="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price" style="color: #333;">Price (RM)</label>
                            <input type="number" step="0.01" name="price" id="price" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity" style="color: #333;">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="is_available" style="color: #333;">Available</label>
                            <select name="is_available" id="is_available">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_featured" style="color: #333;">Featured</label>
                            <select name="is_featured" id="is_featured">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image_url" style="color: #333;">Image URL</label>
                            <input type="text" name="image_url" id="image_url">
                        </div>
                        <div class="form-group">
                            <label for="description" style="color: #333;">Description</label>
                            <textarea name="description" id="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_product" class="btn" style="background-color: #8B4513; color: white;">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Table -->
            <div class="admin-table">
                <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="background:#f8f8f8;">Name</th>
                            <th style="background:#f8f8f8;">Category</th>
                            <th style="background:#f8f8f8;">Price (RM)</th>
                            <th style="background:#f8f8f8;">Stock</th>
                            <th style="background:#f0f0f0;">Available</th>
                            <th style="background:#f0f0f0;">Featured</th>
                            <th style="background:#f0f0f0;">Image</th>
                            <th style="background:#f0f0f0;">Description</th>
                            <th style="background:#f0f0f0;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo (int)$product['stock_quantity']; ?></td>
                                <td><?php echo $product['is_available'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $product['is_featured'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <?php if ($product['image_url']): ?>
                                        <img src="/<?php echo ltrim(htmlspecialchars($product['image_url']), '/'); ?>" alt="Image" style="height:30px;max-width:60px;object-fit:cover;"/>
                                    <?php endif; ?>
                                </td>
                                <td class="description-cell" title="<?php echo htmlspecialchars($product['description']); ?>" style="max-width:200px; overflow-x:auto;">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </td>
                                <td>
                                    <button
                                        class="btn btn-small edit-btn"
                                        data-id="<?php echo $product['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-category="<?php echo htmlspecialchars($product['category_id']); ?>"
                                        data-price="<?php echo number_format($product['price'], 2); ?>"
                                        data-stock="<?php echo (int)$product['stock_quantity']; ?>"
                                        data-image="<?php echo htmlspecialchars($product['image_url']); ?>"
                                        data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                        data-available="<?php echo $product['is_available']; ?>"
                                        data-featured="<?php echo $product['is_featured']; ?>"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn btn-small btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div class="modal" id="editProductModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeEditProduct" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3 style="color: #8B4513; margin-bottom:1em;">Edit Product</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        <div class="form-group">
                            <label style="color: #333;">Name</label>
                            <input type="text" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Category</label>
                            <select name="category_id" id="edit_category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Price (RM)</label>
                            <input type="number" step="0.01" name="price" id="edit_price" required>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="edit_stock" required>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Available</label>
                            <select name="is_available" id="edit_is_available">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Featured</label>
                            <select name="is_featured" id="edit_is_featured">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Image URL</label>
                            <input type="text" name="image_url" id="edit_image_url">
                        </div>
                        <div class="form-group">
                            <label style="color: #333;">Description</label>
                            <textarea name="description" id="edit_description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="edit_product" class="btn" style="background-color: #8B4513; color: white;">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section" style="padding-right: 3em;">
                    <!-- <h3>CATFE</h3> -->
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
    <script src="../assets/js/main.js"></script>
    <script>
        // Add Product Modal
        document.addEventListener("DOMContentLoaded", function () {
            const showAddBtn = document.getElementById("show-add-product");
            const addModal = document.getElementById("addProductModal");
            const closeAddBtn = document.getElementById("closeAddProduct");
            showAddBtn.addEventListener("click", function () {
                addModal.style.display = "flex";
            });
            closeAddBtn.addEventListener("click", function () {
                addModal.style.display = "none";
            });
            window.addEventListener("click", function (e) {
                if (e.target === addModal) {
                    addModal.style.display = "none";
                }
            });
        });

        // Edit Product Modal
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('edit_product_id').value = button.dataset.id;
                document.getElementById('edit_name').value = button.dataset.name;
                document.getElementById('edit_category_id').value = button.dataset.category;
                document.getElementById('edit_price').value = button.dataset.price;
                document.getElementById('edit_stock').value = button.dataset.stock;
                document.getElementById('edit_image_url').value = button.dataset.image;
                document.getElementById('edit_description').value = button.dataset.description;
                document.getElementById('edit_is_available').value = button.dataset.available;
                document.getElementById('edit_is_featured').value = button.dataset.featured;
                document.getElementById('editProductModal').style.display = 'flex';
            });
        });
        document.getElementById('closeEditProduct').addEventListener('click', () => {
            document.getElementById('editProductModal').style.display = 'none';
        });
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('editProductModal');
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Admin Dropdown
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');
        adminToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        window.addEventListener('click', function (e) {
            if (!e.target.closest('.admin-dropdown')) {
                dropdownMenu.style.display = 'none';
            }
        });

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
</body>
<?php if (isset($_GET['added'])): ?>
<script>
window.onload = function() {
    alert('Product has been successfully added.');
}
</script>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
<script>
window.onload = function() {
    alert('Product has been successfully updated.');
}
</script>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<script>
window.onload = function() {
    alert('Product has been successfully deleted.');
}
</script>
<?php endif; ?>
</html>
