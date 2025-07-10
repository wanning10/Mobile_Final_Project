<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $is_available = $_POST['is_available'] ?? 1;
    $is_featured = $_POST['is_featured'] ?? 0;

    // Handle image upload
    $image_url = null;
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

    $data = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'category_id' => $category_id,
        'stock_quantity' => $stock_quantity,
        'is_available' => $is_available,
        'is_featured' => $is_featured,
        'image_url' => $image_url
    ];

    if (createProduct($conn, $data)) {
        $_SESSION['success'] = "Product created successfully!";
    } else {
        $_SESSION['error'] = "Failed to create product.";
    }

    header('Location: index.php'); // Redirect back to admin dashboard
    exit();
}
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>
