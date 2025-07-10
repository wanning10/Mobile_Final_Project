<?php
// Database Setup Script for Cozy Beverage App
// Run this script once to set up the database

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_NAME', 'cozy_beverage_db');
define('DB_PASS', '');

echo "<h2>Setting up Cozy Beverage Database...</h2>";

try {
    // Create connection without database first
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Database '" . DB_NAME . "' created successfully</p>";
    
    // Select the database
    $conn->exec("USE " . DB_NAME);
    echo "<p>✓ Database selected</p>";
    
    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p>✓ Users table created</p>";
    
    // Create categories table
    $conn->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        icon VARCHAR(50) DEFAULT 'fas fa-coffee',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✓ Categories table created</p>";
    
    // Create products table
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category_id INT,
        image_url VARCHAR(255),
        stock_quantity INT DEFAULT 0,
        is_available BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    echo "<p>✓ Products table created</p>";
    
    // Create cart table
    $conn->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        session_id VARCHAR(255),
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "<p>✓ Cart table created</p>";
    
    // Create orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        shipping_phone VARCHAR(20),
        payment_method VARCHAR(50),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "<p>✓ Orders table created</p>";
    
    // Create order_items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT,
        product_name VARCHAR(200) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )");
    echo "<p>✓ Order items table created</p>";
    
    // Insert default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, first_name, last_name, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@cozybeverage.com', $admin_password, 'Admin', 'User', 1]);
    echo "<p>✓ Default admin user created (username: admin, password: admin123)</p>";
    
    // Insert sample categories
    $categories = [
        ['Coffee', 'Premium coffee blends and single-origin beans', 'coffee.jpg', 'fas fa-coffee'],
        ['Tea', 'Fine teas from around the world', 'tea.jpg', 'fas fa-mug-hot'],
        ['Smoothies', 'Fresh fruit and vegetable smoothies', 'smoothies.jpg', 'fas fa-blender'],
        ['Juices', 'Freshly squeezed juices', 'juices.jpg', 'fas fa-glass-whiskey'],
        ['Hot Drinks', 'Hot chocolate, lattes, and more', 'hot-drinks.jpg', 'fas fa-mug-hot']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description, image, icon) VALUES (?, ?, ?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<p>✓ Sample categories inserted</p>";
    
    // Insert sample products
    $products = [
        ['Espresso Shot', 'Single shot of premium espresso', 3.50, 1, 'espresso.jpg', 50, 1],
        ['Cappuccino', 'Classic cappuccino with steamed milk', 4.50, 1, 'cappuccino.jpg', 30, 1],
        ['Green Tea', 'Organic green tea with antioxidants', 2.50, 2, 'green-tea.jpg', 100, 0],
        ['Chai Latte', 'Spiced chai tea with steamed milk', 4.00, 2, 'chai-latte.jpg', 25, 1],
        ['Berry Blast Smoothie', 'Mixed berries with yogurt', 5.50, 3, 'berry-smoothie.jpg', 20, 1],
        ['Tropical Paradise', 'Mango, pineapple, and coconut', 6.00, 3, 'tropical-smoothie.jpg', 15, 0],
        ['Orange Juice', 'Freshly squeezed orange juice', 3.00, 4, 'orange-juice.jpg', 40, 1],
        ['Apple Juice', 'Fresh apple juice', 2.75, 4, 'apple-juice.jpg', 35, 0],
        ['Hot Chocolate', 'Rich hot chocolate with whipped cream', 4.25, 5, 'hot-chocolate.jpg', 30, 1],
        ['Caramel Latte', 'Espresso with caramel and steamed milk', 5.00, 5, 'caramel-latte.jpg', 25, 1]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO products (name, description, price, category_id, image_url, stock_quantity, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "<p>✓ Sample products inserted</p>";
    
    echo "<h3>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 