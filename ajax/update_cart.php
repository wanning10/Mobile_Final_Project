<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
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
if ($quantity < 0 || $quantity > 99) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit();
}

// Update cart
if (updateCartItem($conn, $_SESSION['user_id'], $productId, $quantity)) {
    // Get updated cart data
    $cartItems = getCartItems($conn, $_SESSION['user_id']);
    $cartTotal = getCartTotal($conn, $_SESSION['user_id']);
    updateCartCount($conn, $_SESSION['user_id']);
    
    // Generate cart HTML
    $cartHtml = '';
    foreach ($cartItems as $item) {
        $cartHtml .= '
            <div class="cart-item" data-product-id="' . $item['product_id'] . '">
                <div class="cart-item-image">
                    <img src="' . htmlspecialchars($item['image_url']) . '" 
                         alt="' . htmlspecialchars($item['name']) . '">
                </div>
                <div class="cart-item-details">
                    <h3>' . htmlspecialchars($item['name']) . '</h3>
                    <p class="cart-item-description">' . htmlspecialchars($item['description']) . '</p>
                    <div class="cart-item-price">$' . number_format($item['price'], 2) . ' each</div>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-controls">
                        <label for="quantity-' . $item['product_id'] . '">Quantity:</label>
                        <input type="number" 
                               id="quantity-' . $item['product_id'] . '" 
                               class="cart-quantity" 
                               data-product-id="' . $item['product_id'] . '"
                               value="' . $item['quantity'] . '" 
                               min="1" max="99">
                    </div>
                    <div class="cart-item-total">
                        Total: $' . number_format($item['price'] * $item['quantity'], 2) . '
                    </div>
                    <button class="btn btn-secondary remove-from-cart" 
                            data-product-id="' . $item['product_id'] . '">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'cart_html' => $cartHtml,
        'total' => $cartTotal,
        'cart_count' => $_SESSION['cart_count']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}
?> 