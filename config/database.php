<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cozy_beverage_db');
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
            )"
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
            ['Coffee', 'Premium coffee blends and single-origin beans', 'coffee.jpg', 'fas fa-coffee'],
            ['Tea', 'Fine teas from around the world', 'tea.jpg', 'fas fa-mug-hot'],
            ['Smoothies', 'Fresh fruit and vegetable smoothies', 'smoothies.jpg', 'fas fa-blender'],
            ['Juices', 'Freshly squeezed juices', 'juices.jpg', 'fas fa-glass-whiskey'],
            ['Hot Drinks', 'Hot chocolate, lattes, and more', 'hot-drinks.jpg', 'fas fa-mug-hot'],
            ['Snacks', 'Delicious snacks to pair with your drinks', 'snacks.jpg', 'fas fa-cookie-bite'],
        ];
        
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO categories (name, description, image, icon) VALUES (?, ?, ?, ?)"
        );
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        
        // Products
        $products = [
            ['Espresso Shot', 'Single shot of premium espresso', 3.50, 1, 'espresso.jpg', 50, 1, 1],
            ['Green Tea', 'Organic green tea with antioxidants', 2.50, 2, 'green-tea.jpg', 100, 1, 0],
            ['Cappuccino', 'Classic cappuccino with steamed milk', 4.50, 1, 'cappuccino.jpg', 30, 1, 1],
            ['Green Tea', 'Organic green tea with antioxidants', 2.50, 2, 'green-tea.jpg', 100, 1, 1],
            ['Chai Latte', 'Spiced chai tea with steamed milk', 4.00, 2, 'chai-latte.jpg', 25, 1, 1],
            ['Berry Blast Smoothie', 'Mixed berries with yogurt', 5.50, 3, 'berry-smoothie.jpg', 20, 1, 1],
            ['Tropical Paradise', 'Mango, pineapple, and coconut', 6.00, 3, 'tropical-smoothie.jpg', 15, 1, 0],
            ['Orange Juice', 'Freshly squeezed orange juice', 3.00, 4, 'orange-juice.jpg', 40, 1, 1],
            ['Apple Juice', 'Fresh apple juice', 2.75, 4, 'apple-juice.jpg', 35, 1, 0],
            ['Hot Chocolate', 'Rich hot chocolate with whipped cream', 4.25, 5, 'hot-chocolate.jpg', 30, 1, 1],
            ['Caramel Latte', 'Espresso with caramel and steamed milk', 5.00, 5, 'caramel-latte.jpg', 25, 1, 1]
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