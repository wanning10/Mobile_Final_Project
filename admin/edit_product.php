<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get product ID
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

// Handle update product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];
    $is_available = $_POST['is_available'];
    $is_featured = $_POST['is_featured'];

    // Default to existing image
    $image_url = $product['image_url'];

    // If new image is uploaded, handle it
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    // Prepare data for update
    $data = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'category_id' => $category_id,
        'image_url' => $image_url,
        'stock_quantity' => $stock_quantity,
        'is_available' => $is_available,
        'is_featured' => $is_featured
    ];

    // ✅ Call your function
    if (updateProduct($conn, $id, $data)) {
        $_SESSION['success'] = "Product updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update product.";
    }

    header("Location: index.php");
    exit();
}


// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: index.php");
    exit();
}

// Fetch categories
$categories = getCategories($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Edit Product</h2>
    <form action="edit_product.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>

        <label>Category</label>
        <select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>

        <label>Stock Quantity</label>
        <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" required><br>

        <label>Available</label>
        <select name="is_available">
            <option value="1" <?= $product['is_available'] ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= !$product['is_available'] ? 'selected' : '' ?>>No</option>
        </select><br>

        <label>Featured</label>
        <select name="is_featured">
            <option value="1" <?= $product['is_featured'] ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= !$product['is_featured'] ? 'selected' : '' ?>>No</option>
        </select><br>

        <label>Description</label><br>
        <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea><br>

        <label>Current Image:</label><br>
        <?php if ($product['image_url']): ?>
            <img src="../<?= $product['image_url'] ?>" width="100"><br>
        <?php endif; ?>

        <label>Change Image:</label>
        <input type="file" name="image"><br>

        <button type="submit">Update Product</button>
    </form>
</body>
</html>
