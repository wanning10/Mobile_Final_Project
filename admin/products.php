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

// Get products with category names
$products = getProducts($conn);

error_log("Debug message from PHP");
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

        <script>
            console.log("Data :" + <?php echo json_encode($products); ?>);
        </script>
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


    <h2><i class="fas fa-box-open"></i> Manage Products</h2>

<!-- Trigger Button -->
 <div class="center">
    <button class="btn btn-primary" id="show-form">Create Product</button>
 </div>


<!-- Popup Form -->
<div class="popup" id="product-popup">
    <div class="popup-inner">
        <!-- <span class="close-btn" id="close-form">&times;</span> -->
        <h2>Create New Product</h2>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-element">
                <label for="name">Product Name</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-element">
                <label for="category">Category</label>
                <select name="category_id" id="category" required>
                    <?php foreach (getCategories($conn) as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-element">
                <label for="price">Price ($)</label>
                <input type="number" step="0.01" name="price" id="price" required>
            </div>

            <div class="form-element">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" required>
            </div>

            <div class="form-element">
                <label for="is_available">Available</label>
                <select name="is_available" id="is_available">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="form-element">
                <label for="is_featured">Featured</label>
                <select name="is_featured" id="is_featured">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="form-element">
                <label for="image">Product Image (optional)</label>
                <input type="file" name="image" id="image" accept="image/*">
            </div>

            <div class="form-element">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-success">Create Product</button>
        </form>
    </div>
</div>

<h3>Product List</h3>

<table class="product-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price ($)</th>
            <th>Stock</th>
            <th>Available</th>
            <th>Featured</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="60">
                        <?php else: ?>
                            <span>No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                    <td><?= number_format($product['price'], 2) ?></td>
                    <td><?= (int)$product['stock_quantity'] ?></td>
                    <td><?= $product['is_available'] ? 'Yes' : 'No' ?></td>
                    <td><?= $product['is_featured'] ? 'Yes' : 'No' ?></td>
                    <td>
                        <!-- Placeholder actions -->
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">No products found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


</body>


