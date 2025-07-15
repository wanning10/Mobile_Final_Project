<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

$categories = getCategories($conn);

$featuredProducts = getRandomProducts($conn, 6);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CATFE - Welcome</title>
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
                    <img src="assets/images/logo.png" alt="Logo" style="height:30px;">
                    <span>CATFE</span>
                </a>
            </div>

            <!-- Centered menu links -->
            <div class="nav-center-menu">
                <a href="index.php" class="nav-link active">HOME</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <a href="products.php" class="nav-link">PRODUCTS</a>
                <a href="map.php" class="nav-link">MAP</a>
            </div>

            <!-- Right icons (cart & profile) -->
            <div class="nav-right-icons">
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
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
                <?php if ($isAdmin): ?>
                    <!-- <a href="../admin/index.php" class="nav-link">ADMIN</a> -->
                    <!-- Admin Dropdown -->
                    <div class="admin-dropdown">
                        <!-- <a href="index.php" class="admin-toggle nav-link active">
                            <span>ADMIN</span>
                        </a> -->
                        <button class="admin-toggle nav-link" href="#">
                            <!-- <i class="fas fa-user-cog"></i> -->
                            <span>ADMIN</span>
                        </button>
                        <div class="admin-dropdown-menu">
                            <a href="admin/index.php">Dashboard</a>
                            <a href="admin/products.php">Manage Products</a>
                            <a href="admin/categories.php">Manage Categories</a>
                            <a href="admin/users.php">Manage Users</a>
                            <a href="admin/orders.php">Manage Orders</a>
                        </div>
                    </div>
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
        <video autoplay muted loop playsinline id="hero-video">
            <source src="assets/media/home-hero.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay">
            <div class="hero-left">
                <h1>Welcome to CATFE</h1>
                <p>Discover the finest selection of coffees, teas, and delicious pastries.</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn btn-primary">Explore Products</a>
                    <a href="about.php" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-right">
                <button id="toggle-video" class="play-pause-btn">
                    <i class="fas fa-pause"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Featured Categories (Static Version) -->
    <!-- <section class="featured-categories">
        <div class="container">
            <h2>Our Categories</h2>
            <p class="section-subtitle">A variety of cozy selections made just for you</p>
            <div class="categories-grid">
                <a href="products.php?category=coffee" class="category-card">
                    <img src="assets/images/categories/cat-coffee.png" alt="Coffee" class="category-image">
                    <h3>Coffee</h3>
                    <p>Freshly brewed with premium beans</p>
                </a>

                <a href="products.php?category=tea" class="category-card">
                    <img src="assets/images/categories/cat-tea.png" alt="Tea" class="category-image">
                    <h3>Tea</h3>
                    <p>Aromatic blends for every mood</p>
                </a>

                <a href="products.php?category=frappe" class="category-card">
                    <img src="assets/images/categories/cat-frappe.png" alt="Frappe" class="category-image">
                    <h3>Frappe</h3>
                    <p>Cold, creamy and refreshing</p>
                </a>

                <a href="products.php?category=juices" class="category-card">
                    <img src="assets/images/categories/cat-juices.png" alt="Juices" class="category-image">
                    <h3>Juices</h3>
                    <p>Freshly squeezed for daily vitality</p>
                </a>

                <a href="products.php?category=pastries" class="category-card">
                    <img src="assets/images/categories/cat-pastries.png" alt="Pastries" class="category-image">
                    <h3>Pastries</h3>
                    <p>Sweet and buttery baked delights</p>
                </a>
            </div>
        </div>
    </section> -->

    <!-- Featured Categories (Dynamic Version) -->
    <section class="featured-categories">
        <div class="container">
            <h2>Our Categories</h2>
            <p class="section-subtitle">A variety of cozy selections made just for you</p>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?= $category['id'] ?>" class="category-card">
                        <img 
                            src="<?= htmlspecialchars($category['image']) ?>" 
                            alt="<?= htmlspecialchars($category['name']) ?>" 
                            class="category-image"
                            onerror="this.src='assets/images/categories/default.png';"
                        >
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products (Dynamic) -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <p class="section-subtitle">A variety of cozy selections made just for you</p>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
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
            </div>
        </div>
    </section>

    <!-- Audio Player Section -->
    <section class="audio-section">
        <div class="container">
            <h2>Cozy Atmosphere</h2>
            <p class="section-subtitle">Relaxing background music to enhance your browsing experience</p>
            <div class="audio-player">
                <audio id="cozy-audio" controls>
                    <source src="assets/media/apple-tree.mp3" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <div class="audio-info">
                    <p>Apple Tree by Lukrembo. Source: https://freetouse.com/music.</p>
                </div>
            </div>
        </div>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const video = document.getElementById("hero-video");
        const toggleBtn = document.getElementById("toggle-video");
        const icon = toggleBtn.querySelector("i");

        toggleBtn.addEventListener("click", () => {
            if (video.paused) {
                video.play();
                icon.classList.remove("fa-play");
                icon.classList.add("fa-pause");
            } else {
                video.pause();
                icon.classList.remove("fa-pause");
                icon.classList.add("fa-play");
            }
        });
    </script>

    <script>
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');

        adminToggle.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Optional: Close when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.admin-dropdown')) {
                dropdownMenu.style.display = 'none';
            }
        });
    </script>

    <script>
        document.getElementById("profile-icon").addEventListener("click", function(e) {
            e.preventDefault();
            var dropdown = document.getElementById("profile-dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });

        // Close dropdown when clicking outside
        window.addEventListener("click", function(e) {
            if (!e.target.matches('#profile-icon, #profile-icon *')) {
                var dropdown = document.getElementById("profile-dropdown");
                if (dropdown) dropdown.style.display = "none";
            }
        });
    </script>

    <script>
        document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var productId = this.getAttribute('data-product-id');
                fetch('ajax/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'product_id=' + encodeURIComponent(productId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update cart badge
                            let badge = document.querySelector('.cart-badge');
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'cart-badge';
                                document.querySelector('.fa-shopping-cart').parentNode.appendChild(badge);
                            }
                            badge.textContent = data.cart_count;
                            alert('Added to cart!');
                        } else {
                            alert('Failed to add to cart: ' + data.message);
                        }
                    });
            });
        });
    </script>



</body>
<?php if (isset($_GET['logout'])): ?>
    <script>
        window.onload = function() {
            alert('You have logged out successfully.');
        }
    </script>
<?php endif; ?>

</html>