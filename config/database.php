<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'catfe');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $conn;
    private $isNewInstall = false;
    
    public function __construct() {
        try {
            // First try to connect without specifying database
            $this->conn = new PDO(
                "mysql:host=".DB_HOST,
                DB_USER, 
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $this->initializeDatabase();
            
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function initializeDatabase() {
        try {
            // Check if database exists
            $stmt = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".DB_NAME."'");
            
            if ($stmt->rowCount() == 0) {
                $this->isNewInstall = true;
                $this->conn->exec("CREATE DATABASE ".DB_NAME." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            $this->conn->exec("USE ".DB_NAME);
            $this->createTables();
            
            if ($this->isNewInstall) {
                $this->insertInitialData();
                $this->showSetupSuccess();
            }
            
        } catch(PDOException $e) {
            die("Database initialization failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $tables = [
            "users" => "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                phone VARCHAR(20),
                address TEXT,
                is_admin BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "categories" => "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                image VARCHAR(255),
                icon VARCHAR(50) DEFAULT 'fas fa-coffee',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "products" => "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) UNIQUE NOT NULL,
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
            )",

            "cart" => "CREATE TABLE IF NOT EXISTS cart (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                session_id VARCHAR(255),
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )",    

            "orders" => "CREATE TABLE IF NOT EXISTS orders (
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
            )",
    
            "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT,
                product_name VARCHAR(200) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )",

            "contact_messages" => "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
        ];
        
        foreach ($tables as $sql) {
            $this->conn->exec($sql);
        }
    }
    
    private function insertInitialData() {
        // Admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $this->conn->prepare(
            "INSERT IGNORE INTO users (username, email, password, first_name, last_name, is_admin) 
            VALUES (?, ?, ?, ?, ?, ?)"
        )->execute(['admin', 'admin@cozybeverage.com', $adminPassword, 'Admin', 'User', 1]);
        
        // Categories
        $categories = [
            ['Coffee', 'Freshly brewed with premium beans', 'assets/images/categories/cat-coffee.png', 'fas fa-coffee'],
            ['Tea', 'Aromatic blends for every mood', 'assets/images/categories/cat-tea.png', 'fas fa-mug-hot'],
            ['Frappé', 'Cold, creamy and refreshing', 'assets/images/categories/cat-frappe.png', 'fas fa-blender'],
            ['Juices', 'Freshly squeezed for daily vitality', 'assets/images/categories/cat-juices.png', 'fas fa-glass-whiskey'],
            ['Pastries', 'Sweet and buttery baked delights', 'assets/images/categories/cat-pastries.png', 'fas fa-cookie-bite']
        ];
        
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO categories (name, description, image, icon) VALUES (?, ?, ?, ?)"
        );
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        
        // Products
        $products = [
            ['Blonde Vanilla Latte', 'Extra-smooth Blonde Espresso, velvety steamed milk and vanilla syrup come together to create a delightful new twist on a beloved espresso classic.', 17.50, 1, 'assets/images/products/coffee/vanilla-latte.png', 50, 1, 1],
            ['Caffè Americano', 'Espresso shots topped with hot water create a light layer of crema culminating in this wonderfully rich cup with depth and nuance.', 15.00, 1, 'assets/images/products/coffee/iced-americano.png', 45, 1, 1],
            ['Cappuccino', 'Dark, rich espresso lies in wait under a smoothed and stretched layer of thick milk foam. An alchemy of barista artistry and craft.', 16.50, 1, 'assets/images/products/coffee/cappucino.png', 40, 1, 1],
            ['Iced Matcha Latte', 'Smooth and creamy, this vibrant green tea latte is handcrafted with our new unsweetened matcha, milk, and classic syrup and served with ice.', 18.90, 2, 'assets/images/products/tea/iced-matcha-latte.png', 35, 1, 1],
            ['Iced Black Tea Lemonade', 'Smooth, refreshing Mighty Leaf Summer Solstice iced tea layered with tart and sweet lemonade.', 16.90, 2, 'assets/images/products/tea/iced-blacktea-lemonade.png', 30, 1, 0],
            ['Caramel Frappé', 'Rich caramel adds some indulgence to our double-strength Baridi Blend cold brew. We top this coffee-forward whipped refreshment with whipped cream and a drizzle of caramel sauce.', 16.90, 3, 'assets/images/products/frappe/caramel-frappe.png', 25, 1, 1],
            ['Mocha Frappé', 'Double-strength Baridi Cold Brew and Peet\'s housemade chocolate sauce are whipped with milk and ice for sweet refreshment topped with whipped cream.', 18.50, 3, 'assets/images/products/frappe/mocha-frappe.png', 20, 1, 1],
            ['Orange Juice', 'Sweet strawberry, passionfruit, and açaí flavors balanced with the delightful zing of lemonade, served over ice with freeze-dried strawberry pieces.', 7.50, 4, 'assets/images/products/juice/orange-juice.png', 40, 1, 0],
            ['Strawberry Açaí Lemonade Refresher', 'Sweet strawberry, passionfruit, and açaí flavors balanced with the delightful zing of lemonade, served over ice with freeze-dried strawberry pieces.', 9.50, 4, 'assets/images/products/juice/strawberry-lemonade.png', 35, 1, 1],
            ['Chocolate Croissant', 'Two generous pieces of chocolate wrapped in a butter croissant with soft, flaky layers and a golden-brown crust.', 12.50, 5, 'assets/images/products/pastries/chocolate-croissant.png', 30, 1, 1],
            ['Baked Apple Croissant', 'Layers of croissant dough wrapped around a warm apple filling, topped with sugar and baked to a golden finish.', 10.00, 5, 'assets/images/products/pastries/baked-apple-croissant.png', 25, 1, 0],
        ];

        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO products (name, description, price, category_id, image_url, stock_quantity, is_available, is_featured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        foreach ($products as $product) {
            $stmt->execute($product);
        }
    }
    
    private function showSetupSuccess() {
        if (php_sapi_name() !== 'cli') {
            echo '<div style="padding:20px;background:#d4edda;color:#155724;border-radius:5px;margin:20px;">';
            echo '<h3>🎉 Database Setup Complete!</h3>';
            echo '<p><strong>Admin credentials:</strong></p>';
            echo '<ul>';
            echo '<li>Username: admin</li>';
            echo '<li>Password: admin123</li>';
            echo '</ul>';
            echo '<p>Please change the admin password immediately.</p>';
            echo '</div>';
        } else {
            echo "Database setup complete.\n";
            echo "Admin username: admin\n";
            echo "Admin password: admin123\n";
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Initialize database
$database = new Database();
$conn = $database->getConnection();