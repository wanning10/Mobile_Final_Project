<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
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
        header('Location: products.php');
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
        header('Location: products.php');
        exit();
    }
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = intval($_POST['product_id']);
    deleteProduct($conn, $id);
    header('Location: products.php');
    exit();
}

// Get all products
$products = getProducts($conn);

// For editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $editProduct = getProductById($conn, intval($_GET['edit']));
}
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
                <a href="products.php" class="nav-link active">Products</a>
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
            <h2><i class="fas fa-box-open"></i> Manage Products</h2>


            <button class="btn btn-primary" id="show-add-product">Add Product</button>
 

            <!-- Pop out product form-->
            <div class="modal" id="addProductModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; padding:2em; border-radius:8px; max-width:500px; width:100%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <span class="close-modal" id="closeAddProduct" style="position:absolute; top:10px; right:15px; font-size:1.5em; cursor:pointer;">&times;</span>
                    <h3>Add Product</h3>
                    <form method="post" action="" style="overflow-y:auto; max-height:60vh;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" step="0.01" name="price" id="price" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="is_available">Available</label>
                            <select name="is_available" id="is_available">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_featured">Featured</label>
                            <select name="is_featured" id="is_featured">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="text" name="image_url" id="image_url">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="add_product" class="btn">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit product form -->
            <?php if ($editProduct): ?>
            <div class="admin-form">
                <h3>Edit Product</h3>
                <form method="post" action="">
                    <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($editProduct['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php if ($editProduct['category_id'] == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="price" required value="<?php echo htmlspecialchars($editProduct['price']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" required value="<?php echo htmlspecialchars($editProduct['stock_quantity']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="is_available">Available</label>
                        <select name="is_available" id="is_available">
                            <option value="1" <?php if ($editProduct['is_available']) echo 'selected'; ?>>Yes</option>
                            <option value="0" <?php if (!$editProduct['is_available']) echo 'selected'; ?>>No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="is_featured">Featured</label>
                        <select name="is_featured" id="is_featured">
                            <option value="1" <?php if ($editProduct['is_featured']) echo 'selected'; ?>>Yes</option>
                            <option value="0" <?php if (!$editProduct['is_featured']) echo 'selected'; ?>>No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="text" name="image_url" id="image_url" value="<?php echo htmlspecialchars($editProduct['image_url']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($editProduct['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="edit_product" class="btn">Update Product</button>
                        <a href="products.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Products Table -->
            <div class="admin-table">
                <h3>All Products</h3>
                <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:separate; border-spacing:0 0.5em;">
                    <thead>
                        <tr>
                            <!-- Left side fields -->

                            <th style="background:#f8f8f8;">Name</th>
                            <th style="background:#f8f8f8;">Category</th>
                            <th style="background:#f8f8f8;">Price</th>
                            <th style="background:#f8f8f8;">Stock</th>
                            <!-- Right side fields -->
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
                                <!-- Left side fields -->
                                <!-- <td><?php echo $product['id']; ?></td> -->
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo (int)$product['stock_quantity']; ?></td>
                                <!-- Right side fields -->
                                <td><?php echo $product['is_available'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $product['is_featured'] ? 'Yes' : 'No'; ?></td>
                                <td><?php if ($product['image_url']): ?><img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Image" style="height:30px;max-width:60px;object-fit:cover;"/><?php endif; ?></td>
                                <td style="max-width:200px; overflow-x:auto;"><?php echo htmlspecialchars($product['description']); ?></td>
                                <td>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-small"><i class="fas fa-edit"></i></a>
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


