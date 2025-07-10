<?php
// Helper functions for Cozy Beverage App

// User Management Functions
function registerUser($conn, $username, $email, $password) {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($conn, $username, $password) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function getUserById($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

function updateUserProfile($conn, $userId, $email, $currentPassword, $newPassword = null) {
    try {
        $user = getUserById($conn, $userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$email, $hashedPassword, $userId]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            return $stmt->execute([$email, $userId]);
        }
    } catch(PDOException $e) {
        return false;
    }
}

function deleteUser($conn, $userId) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    } catch(PDOException $e) {
        return false;
    }
}

// Category Functions
function getCategories($conn, $limit = null) {
    try {
        $sql = "SELECT * FROM categories ORDER BY name";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getCategoryById($conn, $categoryId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

function createCategory($conn, $name, $description = '', $image = '', $icon = 'fas fa-coffee') {
    try {
        $stmt = $conn->prepare("INSERT INTO categories (name, description, image, icon) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $description, $image, $icon]);
    } catch(PDOException $e) {
        return false;
    }
}

function updateCategory($conn, $categoryId, $name, $description = '', $image = '', $icon = 'fas fa-coffee') {
    try {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image = ?, icon = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $image, $icon, $categoryId]);
    } catch(PDOException $e) {
        return false;
    }
}

function searchCategories($conn, $keyword) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE name LIKE ? OR description LIKE ? ORDER BY name");
        $like = "%" . $keyword . "%";
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}


// Product Functions
function getProducts($conn, $categoryId = null, $search = null, $limit = null) {
    try {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY p.name";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getFeaturedProducts($conn, $limit = 6) {
    try {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.is_featured = 1 
                               ORDER BY p.created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getProductById($conn, $productId) {
    try {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}


function createProduct($conn, $data) {
    try {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image_url, stock_quantity, is_available, is_featured)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['image_url'],
            $data['stock_quantity'],
            $data['is_available'],
            $data['is_featured']
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function updateProduct($conn, $id, $data) {
    try {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, image_url=?, stock_quantity=?, is_available=?, is_featured=?
                                WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['image_url'],
            $data['stock_quantity'],
            $data['is_available'],
            $data['is_featured'],
            $id
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function deleteProduct($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        return false;
    }
}


function toggleProductAvailability($conn, $productId, $status) {
    try {
        $stmt = $conn->prepare("UPDATE products SET is_available = ? WHERE id = ?");
        return $stmt->execute([$status, $productId]);
    } catch(PDOException $e) {
        return false;
    }
}

// Cart Functions
function addToCart($conn, $userId, $productId, $quantity = 1) {
    try {
        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            return $stmt->execute([$newQuantity, $userId, $productId]);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $productId, $quantity]);
        }
    } catch(PDOException $e) {
        return false;
    }
}

function getCartItems($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image_url, p.description 
                               FROM cart c 
                               JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function updateCartItem($conn, $userId, $productId, $quantity) {
    try {
        if ($quantity <= 0) {
            return removeFromCart($conn, $userId, $productId);
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$quantity, $userId, $productId]);
    } catch(PDOException $e) {
        return false;
    }
}

function removeFromCart($conn, $userId, $productId) {
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    } catch(PDOException $e) {
        return false;
    }
}

function clearCart($conn, $userId) {
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        return $stmt->execute([$userId]);
    } catch(PDOException $e) {
        return false;
    }
}

function getCartTotal($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total 
                               FROM cart c 
                               JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

function getCartCount($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}

// Order Functions
function createOrder($conn, $userId, $totalAmount, $shippingAddress, $shippingPhone, $paymentMethod = 'Credit Card') {
    try {
        $conn->beginTransaction();
        
        // Generate unique order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_phone, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $orderNumber, $totalAmount, $shippingAddress, $shippingPhone, $paymentMethod]);
        $orderId = $conn->lastInsertId();
        
        // Get cart items
        $cartItems = getCartItems($conn, $userId);
        
        if (empty($cartItems)) {
            throw new Exception('Cart is empty');
        }
        
        // Create order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price']]);
        }
        
        // Clear cart
        clearCart($conn, $userId);
        
        // Update cart count in session
        updateCartCount($conn, $userId);
        
        $conn->commit();
        return $orderId;
    } catch(Exception $e) {
        $conn->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return false;
    }
}

function getUserOrders($conn, $userId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getOrderById($conn, $orderId, $userId = null) {
    try {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $params = [$orderId];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

function getOrderItems($conn, $orderId) {
    try {
        $stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Admin Functions
function getAllUsers($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

function getAllOrders($conn) {
    try {
        $stmt = $conn->prepare("SELECT o.*, u.username FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               ORDER BY o.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Utility Functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

// Update cart count in session
function updateCartCount($conn, $userId) {
    if ($userId) {
        $_SESSION['cart_count'] = getCartCount($conn, $userId);
    } else {
        $_SESSION['cart_count'] = 0;
    }
}
?> 