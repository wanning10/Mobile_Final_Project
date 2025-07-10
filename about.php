<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Cozy Beverage</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php echo $isLoggedIn ? 'logged-in' : ''; ?>">
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
                <a href="index.php" class="nav-link">Home</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Cart
                    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="map.php" class="nav-link">Map</a>
                <a href="about.php" class="nav-link active">About</a>
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

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <h2><i class="fas fa-info-circle"></i> About Cozy Beverage</h2>
            
            <div class="about-content">
                <div class="about-story">
                    <h3>Our Story</h3>
                    <p>Founded in 2020, Cozy Beverage began as a small dream to create a warm, welcoming space where people could enjoy quality beverages in a relaxed atmosphere. What started as a simple coffee shop has grown into a beloved community gathering place.</p>
                    
                    <p>Our passion for exceptional beverages and genuine hospitality drives everything we do. We source the finest ingredients from local and international suppliers, ensuring every cup tells a story of quality and care.</p>
                    
                    <p>At Cozy Beverage, we believe that great beverages have the power to bring people together, spark conversations, and create lasting memories. Whether you're starting your day with a perfectly brewed coffee or winding down with a soothing tea, we're here to make your experience special.</p>
                </div>
                
                <div class="about-mission">
                    <h3>Our Mission</h3>
                    <p>To provide exceptional beverages and create a welcoming environment where every customer feels at home. We strive to:</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Serve the highest quality beverages</li>
                        <li><i class="fas fa-check"></i> Create a warm and inviting atmosphere</li>
                        <li><i class="fas fa-check"></i> Support local communities and suppliers</li>
                        <li><i class="fas fa-check"></i> Provide excellent customer service</li>
                        <li><i class="fas fa-check"></i> Maintain sustainable business practices</li>
                    </ul>
                </div>
            </div>
            
            <div class="values-section">
                <h3>Our Values</h3>
                <div class="values-grid">
                    <div class="value-card">
                        <i class="fas fa-heart"></i>
                        <h4>Passion</h4>
                        <p>We're passionate about creating the perfect beverage experience for every customer.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-leaf"></i>
                        <h4>Sustainability</h4>
                        <p>We're committed to environmentally friendly practices and sustainable sourcing.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-users"></i>
                        <h4>Community</h4>
                        <p>We believe in building strong relationships with our customers and local community.</p>
                    </div>
                    <div class="value-card">
                        <i class="fas fa-star"></i>
                        <h4>Quality</h4>
                        <p>We never compromise on quality, from our ingredients to our service.</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-section">
                <h3>Contact Information</h3>
                <div class="contact-grid">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Address</h4>
                            <p>123 Coffee Street<br>New York, NY 10001</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>(555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@cozybeverage.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Hours</h4>
                            <p>Monday - Sunday<br>7:00 AM - 9:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="features-section">
                <h3>What We Offer</h3>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-coffee"></i>
                        <h4>Premium Coffee</h4>
                        <p>Freshly roasted coffee beans from around the world</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mug-hot"></i>
                        <h4>Artisan Tea</h4>
                        <p>Carefully selected teas for every taste and mood</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bread-slice"></i>
                        <h4>Fresh Pastries</h4>
                        <p>Daily baked goods made with love</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i>
                        <h4>Free WiFi</h4>
                        <p>Stay connected while you relax</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-parking"></i>
                        <h4>Free Parking</h4>
                        <p>Convenient parking for our customers</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wheelchair"></i>
                        <h4>Accessible</h4>
                        <p>Wheelchair accessible for everyone</p>
                    </div>
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
</body>
</html> 