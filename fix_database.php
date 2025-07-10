<?php
// Database Fix Script for Cozy Beverage App
// Run this script to fix the missing icon column

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_NAME', 'cozy_beverage_db');
define('DB_PASS', '');

echo "<h2>Fixing Cozy Beverage Database...</h2>";

try {
    // Create connection
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Check if icon column exists in categories
    $stmt = $conn->query("SHOW COLUMNS FROM categories LIKE 'icon'");
    $iconExists = $stmt->rowCount() > 0;
    
    if (!$iconExists) {
        // Add icon column to categories table
        $conn->exec("ALTER TABLE categories ADD COLUMN icon VARCHAR(50) DEFAULT 'fas fa-coffee' AFTER image");
        echo "<p>✓ Added icon column to categories table</p>";
        
        // Update existing categories with appropriate icons
        $categoryIcons = [
            'Coffee' => 'fas fa-coffee',
            'Tea' => 'fas fa-mug-hot',
            'Smoothies' => 'fas fa-blender',
            'Juices' => 'fas fa-glass-whiskey',
            'Hot Drinks' => 'fas fa-mug-hot'
        ];
        
        $stmt = $conn->prepare("UPDATE categories SET icon = ? WHERE name = ?");
        foreach ($categoryIcons as $name => $icon) {
            $stmt->execute([$icon, $name]);
        }
        echo "<p>✓ Updated existing categories with icons</p>";
    } else {
        echo "<p>✓ Icon column already exists in categories</p>";
    }
    
    // Check if image_url column exists in products
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'image_url'");
    $imageUrlExists = $stmt->rowCount() > 0;
    
    if (!$imageUrlExists) {
        // Add image_url column to products table
        $conn->exec("ALTER TABLE products ADD COLUMN image_url VARCHAR(255) AFTER category_id");
        echo "<p>✓ Added image_url column to products table</p>";
        
        // Copy data from image column if it exists
        $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
        if ($stmt->rowCount() > 0) {
            $conn->exec("UPDATE products SET image_url = image");
            echo "<p>✓ Copied data from image to image_url column</p>";
        }
    } else {
        echo "<p>✓ Image_url column already exists in products</p>";
    }
    
    // Check if is_featured column exists in products
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'is_featured'");
    $isFeaturedExists = $stmt->rowCount() > 0;
    
    if (!$isFeaturedExists) {
        // Add is_featured column to products table
        $conn->exec("ALTER TABLE products ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER is_available");
        echo "<p>✓ Added is_featured column to products table</p>";
    } else {
        echo "<p>✓ Is_featured column already exists in products</p>";
    }
    
    echo "<h3>🎉 Database fix completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 