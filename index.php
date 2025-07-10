<?php
session_start();
// require_once 'config/database.php';
require_once 'includes/functions.php';
require_once __DIR__.'/config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cozy Beverage - Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <i class="fas fa-coffee"></i>
                    <span>Cozy Beverage</span>
                </a>
            </div>
            <div class="nav-menu" id="nav-menu">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Cart
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="map.php" class="nav-link">Map</a>
                <a href="about.php" class="nav-link">About</a>
                <?php if($isLoggedIn): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <?php if($isAdmin): ?>
                        <a href="admin/" class="nav-link">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Cozy Beverage</h1>
            <p>Discover the finest selection of teas, coffees, and delicious snacks</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary">Explore Products</a>
                <a href="about.php" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-media">
            <video id="hero-video" controls muted>
                <source src="assets/media/cozy-intro.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured-categories">
        <div class="container">
            <h2>Our Categories</h2>
            <div class="categories-grid">
                <?php
                $categories = getCategories($conn, 4); // Get first 4 categories
                foreach($categories as $category):
                ?>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo $category['icon']; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-outline">View Products</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php
                $featuredProducts = getFeaturedProducts($conn, 6); // Get 6 featured products
                foreach($featuredProducts as $product):
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Audio Player Section -->
    <section class="audio-section">
        <div class="container">
            <h2>Cozy Atmosphere</h2>
            <div class="audio-player">
                <audio id="cozy-audio" controls>
                    <source src="assets/media/cozy-background.mp3" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <div class="audio-info">
                    <p>Relaxing background music to enhance your browsing experience</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Cozy Beverage</h3>
                    <p>Your perfect companion for a relaxing beverage experience.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="map.php">Location</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: info@cozybeverage.com</p>
                    <p>Phone: (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Cozy Beverage. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html> 