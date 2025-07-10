<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$productId = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

// Validate quantity
if ($quantity <= 0 || $quantity > 99) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit();
}

// Check if product exists
$product = getProductById($conn, $productId);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Add to cart
if (addToCart($conn, $_SESSION['user_id'], $productId, $quantity)) {
    // Update cart count in session
    updateCartCount($conn, $_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cart_count' => $_SESSION['cart_count']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
}
?> 