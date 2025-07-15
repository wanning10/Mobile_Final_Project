<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to remove items from cart']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$productId = (int)$input['product_id'];

// Remove from cart
if (removeFromCart($conn, $_SESSION['user_id'], $productId)) {
    // Get updated cart data
    $cartItems = getCartItems($conn, $_SESSION['user_id']);
    $cartTotal = getCartTotal($conn, $_SESSION['user_id']);
    updateCartCount($conn, $_SESSION['user_id']);
    
    // Generate cart HTML
    // $cartHtml = '';
    // foreach ($cartItems as $item) {
    //     $cartHtml .= '
    //         <div class="cart-item" data-product-id="' . $item['product_id'] . '">
    //             <div class="cart-item-info">
    //                 <img src="' . htmlspecialchars($item['image_url']) . '" 
    //                     alt="' . htmlspecialchars($item['name']) . '">
    //                 <div>
    //                     <h3>' . htmlspecialchars($item['name']) . '</h3>
    //                     <p class="product-category">' . htmlspecialchars($item['category'] ?? 'Bakery') . '</p>
    //                 </div>
    //             </div>
    //             <div class="cart-item-price">RM ' . number_format($item['price'], 2) . '</div>
    //             <div class="quantity-controls">
    //                 <button class="quantity-btn minus" data-product-id="' . $item['product_id'] . '">-</button>
    //                 <input type="number" 
    //                     class="cart-quantity" 
    //                     data-product-id="' . $item['product_id'] . '"
    //                     value="' . $item['quantity'] . '" 
    //                     min="1" max="99">
    //                 <button class="quantity-btn plus" data-product-id="' . $item['product_id'] . '">+</button>
    //             </div>
    //             <div class="cart-item-total">
    //                 RM ' . number_format($item['price'] * $item['quantity'], 2) . '
    //                 <button class="remove-from-cart" data-product-id="' . $item['product_id'] . '">
    //                     <i class="fas fa-trash"></i>
    //                 </button>
    //             </div>
    //         </div>';
    // }

    if (empty($cartItems)) {
        $cartHtml = '
        <div class="empty-cart-container">
            <div class="empty-cart-content">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="empty-cart-title">Your Cart is Empty</h2>
                <p class="empty-cart-message">Looks like you haven\'t added any items yet</p>
                <a href="products.php" class="btn btn-primary empty-cart-button">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
            </div>
        </div>';
    }
    else {
        $cartHtml = '
        <div class="cart-header">
            <div>Product</div>
            <div>Price</div>
            <div>Quantity</div>
            <div>Total</div>
        </div>';

        foreach ($cartItems as $item) {
            $cartHtml .= '
                <div class="cart-item" data-product-id="' . $item['product_id'] . '">
                    <div class="cart-item-info">
                        <img src="' . htmlspecialchars($item['image_url']) . '" 
                            alt="' . htmlspecialchars($item['name']) . '">
                        <div>
                            <h3>' . htmlspecialchars($item['name']) . '</h3>
                            <p class="product-category">' . htmlspecialchars($item['category'] ?? 'Bakery') . '</p>
                        </div>
                    </div>
                    <div class="cart-item-price">RM ' . number_format($item['price'], 2) . '</div>
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" data-product-id="' . $item['product_id'] . '">-</button>
                        <input type="number" 
                            class="cart-quantity" 
                            data-product-id="' . $item['product_id'] . '"
                            value="' . $item['quantity'] . '" 
                            min="1" max="99">
                        <button class="quantity-btn plus" data-product-id="' . $item['product_id'] . '">+</button>
                    </div>
                    <div class="cart-item-total">
                        RM ' . number_format($item['price'] * $item['quantity'], 2) . '
                        <button class="remove-from-cart" data-product-id="' . $item['product_id'] . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>';
        }
    }
        
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_html' => $cartHtml,
        'total' => $cartTotal,
        'is_empty' => empty($cartItems), 
        'cart_count' => $_SESSION['cart_count']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
}
?> 