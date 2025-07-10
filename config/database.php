<?php
// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'cozy_beverage_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection without database first
try {
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Try to connect to the specific database
    $conn->exec("USE " . DB_NAME);
} catch(PDOException $e) {
    // If database doesn't exist, show setup instructions
    if ($e->getCode() == 1049) {
        die("Database '" . DB_NAME . "' does not exist. Please run setup_database.php first to create the database and tables.");
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}

// Create database and tables if they don't exist
function createDatabase() {
    global $conn;
    
    try {
        // Create database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $conn->exec("USE " . DB_NAME);
        
        // Create users table
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Create categories table
        $conn->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'fas fa-coffee',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create products table
        $conn->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(255),
            category_id INT,
            is_featured TINYINT(1) DEFAULT 0,
            stock_quantity INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");
        
        // Create cart table
        $conn->exec("CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            product_id INT,
            quantity INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        
        // Create orders table
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create order_items table
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
        
        // Insert default admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@cozybeverage.com', $adminPassword, 1]);
        
        // Insert default categories
        $categories = [
            ['Tea', 'Premium selection of teas from around the world', 'fas fa-mug-hot'],
            ['Coffee', 'Freshly roasted coffee beans and brews', 'fas fa-coffee'],
            ['Bread', 'Freshly baked bread and pastries', 'fas fa-bread-slice'],
            ['Snacks', 'Delicious snacks and treats', 'fas fa-cookie-bite']
        ];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description, icon) VALUES (?, ?, ?)");
        foreach($categories as $category) {
            $stmt->execute($category);
        }
        
        // Insert sample products
        $products = [
            ['Earl Grey Tea', 'Classic black tea with bergamot oil', 5.99, 'assets/images/earl-grey.jpg', 1, 1],
            ['Green Tea', 'Refreshing Japanese green tea', 4.99, 'assets/images/green-tea.jpg', 1, 1],
            ['Espresso', 'Strong Italian espresso', 3.99, 'assets/images/espresso.jpg', 2, 1],
            ['Cappuccino', 'Creamy Italian cappuccino', 4.49, 'assets/images/cappuccino.jpg', 2, 1],
            ['Sourdough Bread', 'Artisan sourdough bread', 6.99, 'assets/images/sourdough.jpg', 3, 1],
            ['Croissant', 'Buttery French croissant', 3.49, 'assets/images/croissant.jpg', 3, 0],
            ['Chocolate Chip Cookies', 'Homemade chocolate chip cookies', 2.99, 'assets/images/cookies.jpg', 4, 1],
            ['Brownie', 'Rich chocolate brownie', 3.99, 'assets/images/brownie.jpg', 4, 0]
        ];
        
        $stmt = $conn->prepare("INSERT IGNORE INTO products (name, description, price, image_url, category_id, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
        foreach($products as $product) {
            $stmt->execute($product);
        }
        
    } catch(PDOException $e) {
        die("Database setup failed: " . $e->getMessage());
    }
}

// Initialize database
createDatabase();
?> 