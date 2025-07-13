<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

// Get categories for filter
$categories = getCategories($conn);

// Get products based on filters
$products = getProducts($conn, $categoryId, $search);

// Get current category info
$currentCategory = null;
if ($categoryId) {
    $currentCategory = getCategoryById($conn, $categoryId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - CATFE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo $isLoggedIn ? 'logged-in' : ''; ?>">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Logo" style="height:30px;">
                    <span>CATFE</span>
                </a>
            </div>

            <!-- Centered menu links -->
            <div class="nav-center-menu">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <a href="products.php" class="nav-link active">PRODUCTS</a>
                <a href="map.php" class="nav-link">MAP</a>
            </div>

            <!-- Right icons (cart & profile) -->
            <div class="nav-right-icons">
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <div class="profile-dropdown">
                    <a href="#" class="nav-link" id="profile-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-content" id="profile-dropdown">
                        <a href="profile.php">My Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
                <?php if($isAdmin): ?>
                    <a href="admin/" class="nav-link">ADMIN</a>
                <?php endif; ?>
            </div>

            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Products Top Banner Section -->
    <!-- <section class="products-banner">
    <div class="banner-overlay">
        <h2>All Products</h2>
    </div>
    </section> -->

    <!-- Hero Section Products Top Banner -->
    <section class="hero-section" style="position: relative; overflow: hidden;">
        <img src="assets/images/coffee-product-new.jpg" alt="Hero Image" style="width: 100%; height: auto; object-fit: cover;">
        <div class="hero-overlay" style="
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 2rem;
        ">
            <h1 style="font-size: 3rem; margin-bottom: 1rem;">All Products</h1>
            <p style="font-size: 1.2rem;">Behind every brew is a dedication to quality and a love for sharing warmth.</p>
        </div>
    </section>

    <!-- search bar -->
    <!-- <div class="product-search-container">
        <input type="text" placeholder="Search products..." class="product-search-input">
        <button type="submit" class="product-search-button">
            <i class="fas fa-search"></i>
        </button>
    </div> -->

    <!-- Dynamic Search Bar (Same Design as Static) -->
    <div class="product-search-container">
        <form method="GET" action="products.php" class="search-form" style="display: contents;">
            <input type="text" 
                name="search" 
                placeholder="Search products..." 
                class="product-search-input"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="product-search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Product Categories Filter -->
    <!-- <section class="products-categories">
        <div class="categories-container">
            <span class="category active">All</span>
            <span class="category">Coffee</span>
            <span class="category">Tea</span>
            <span class="category">Frappé</span>
            <span class="category">Juice</span>
            <span class="category">Pastries</span>
        </div>
    </section> -->

    <section class="products-categories">
        <div class="categories-container">
            <!-- All Category Tab -->
            <span class="category <?= (empty($_GET['category'])) ? 'active' : '' ?>" 
                onclick="window.location.href='products.php'">
                All
            </span>
            
            <!-- Dynamic Category Tabs -->
            <?php foreach($categories as $category): ?>
                <span class="category <?= ($_GET['category'] ?? '') == $category['id'] ? 'active' : '' ?>"
                    onclick="window.location.href='products.php?category=<?= $category['id'] ?>'">
                    <?= htmlspecialchars($category['name']) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </section>
        
    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
        <!-- <h2>Our Products</h2> -->

        <!-- Search and Filter -->
        <!-- <div class="filters-container">
            <form method="GET" action="products.php" class="filters-form">
            <div class="filter-group">
                <input type="text" placeholder="Search products..." />
            </div>

            <div class="filter-group">
                <select>
                <option value="">All Categories</option>
                <option value="1">Coffee</option>
                <option value="2">Tea</option>
                <option value="3">Desserts</option>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
                </button>
                <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
                </a>
            </div>
            </form>
        </div> -->

        <!-- Category Info -->
        <!-- <div class="category-info">
            <h3><i class="fas fa-mug-hot"></i> Coffee</h3>
            <p>Freshly brewed coffee with aromatic beans for your perfect morning.</p>
        </div> -->

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if(empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search criteria or browse all categories.</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                onerror="this.src='assets/images/products/default.jpg'">
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="product-price">RM <?= number_format($product['price'], 2) ?></div>
                            <?php if($isLoggedIn): ?>
                                <button class="btn btn-primary add-to-cart" data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="fas fa-sign-in-alt"></i> Login to Add
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick Category Links -->
        <!-- <div class="quick-categories">
            <h3>Browse by Category</h3>
            <div class="category-links">
            <a href="#" class="btn btn-outline active"><i class="fas fa-mug-hot"></i> Coffee</a>
            <a href="#" class="btn btn-outline"><i class="fas fa-leaf"></i> Tea</a>
            <a href="#" class="btn btn-outline"><i class="fas fa-ice-cream"></i> Desserts</a>
            </div>
        </div>
        </div> -->
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section" style="padding-right: 3em;">
                    <!-- <h3>CATFE</h3> -->
                     <h3 style="display: flex; align-items: center; gap: 8px;">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 30px;">
                        CATFE
                    </h3>
                    <p>Sip, unwind, and enjoy the gentle company of cats.
                        Catfe brings comfort, quality coffee, and calm all in one cozy space.</p>
                </div>
                <div class="footer-section" style="padding-left: 5em;">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="map.php">Map</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-map-marker-alt" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> Jalan Sunsuria, Bandar Sunsuria, 43900 Sepang, Selangor</p>
                    <p><i class="fas fa-phone" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> 60123456789</p>
                    <p><i class="fas fa-envelope" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> catfe@example.com</p>
                    <p><i class="fas fa-clock" style="margin-right: 0.5rem; margin-bottom: 0.5rem;"></i> Open Daily: 7AM - 9PM</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Catfe. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById("profile-icon").addEventListener("click", function(e){
            e.preventDefault();
            var dropdown = document.getElementById("profile-dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });

        // Close dropdown when clicking outside
        window.addEventListener("click", function(e){
            if (!e.target.matches('#profile-icon, #profile-icon *')) {
                var dropdown = document.getElementById("profile-dropdown");
                if (dropdown) dropdown.style.display = "none";
            }
        });
        </script>
</body>
</html> 