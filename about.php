<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle contact form submission
$contactSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $message = sanitizeInput($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_messages (first_name, last_name, email, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$firstName, $lastName, $email, $message])) {
        $contactSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - CATFE</title>
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
                <a href="about.php" class="nav-link active">ABOUT</a>
                <a href="products.php" class="nav-link">PRODUCTS</a>
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
    <section class="hero-section" style="position: relative; overflow: hidden;">
        <img src="assets/images/coffee-shop-2.jpg" alt="Hero Image" style="width: 100%; height: auto; object-fit: cover;">
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
            <h1 style="font-size: 3rem; margin-bottom: 1rem;">Every cup has a story.</h1>
            <p style="font-size: 1.2rem;">Behind every brew is a dedication to quality and a love for sharing warmth.</p>
        </div>
    </section>

    <!-- About Short Intro Section -->
    <section class="about-intro">
    <div class="container">
        <div class="intro-logo">
        <img src="assets/images/logo.png" alt="Logo" style="height:80px;">
        </div>
        <h2>Welcome to a New Era of Coffee</h2>
        <p>For many, coffee is part of daily life.<br>
        But at Catfe, we believe your coffee moments can be more than just routine.<br>
        They can be calming, joyful, and shared with our loving cats.<br>
        Specialty coffee often feels like a luxury reserved for rare occasions.<br>
        Catfe make great coffee and heartwarming cat companionship accessible every day.<br>
        Come sip, relax, and experience a purrfect blend of comfort and happiness.
        </p>
    </div>
    </section>

    <!-- Beans Section -->
    <section class="beans-section">
    <div class="container beans-container">
        <div class="beans-image">
        <img src="assets/images/coffee_bean.webp" alt="Coffee Beans">
        </div>
        <div class="beans-text">
        <h2>We Are Serious About Our Beans</h2>
        <p>At Catfe, every cup starts with the finest beans. We partner with experienced local roasters who source premium Arabica, Robusta, and Liberica beans directly from trusted farms worldwide. Our beans are roasted in small batches to ensure maximum freshness and flavour in every sip.</p>
        <p>We believe great coffee deserves great care – that’s why our roasting partners uphold strict quality standards, holding certifications such as ISO22000, HACCP, and Halal. When you visit Catfe, you’re not just enjoying coffee, you’re experiencing dedication, expertise, and the purest taste, alongside our lovely feline friends.</p>
        </div>
    </div>
    </section>

    <!-- Customer Experience Section -->
    <section class="customer-experience">
    <div class="container">
        <h2>About Catfe <span><img src="assets/images/paw.png" alt="Paw" style="height:40px;"></span></h2>
        
        <div class="experience-grid">

        <div class="experience-card">
            <img src="assets/images/coffee-affordable.webp" alt="Specialty Coffee Made Affordable">
            <h3>Specialty Coffee & Cat Comfort</h3>
            <p>At Catfe, we brew specialty coffee sourced from premium beans, roasted locally to ensure freshness in every cup. While you enjoy your latte or cappuccino, our friendly cats roam freely, offering comfort, warmth, and a touch of purr therapy to your day.</p>
        </div>

        <div class="experience-card">
            <img src="assets/images/cat-cafe-cozy.jpg" alt="Quality & Convenience">
            <h3>Quality, Care & Companionship</h3>
            <p>We pride ourselves on creating a cozy environment where quality coffee meets feline companionship. Whether you’re here to work, relax, or spend time with our cats, each cup is handcrafted to perfection and served with care by our passionate baristas.</p>
        </div>

        <div class="experience-card">
            <img src="assets/images/online-to-offline.webp" alt="Online To Offline">
            <h3>Cafe To Community</h3>
            <p>Ordering from Catfe is easy. Browse our menu online, pre-order your favourite drink, and drop by to unwind with our cats, or have it delivered to your doorstep if you’re on the go. Experience Catfe anywhere, anytime – your comfort and happiness matter to us.</p>
        </div>

        </div>
    </div>
    </section>

    <!-- Innovative Coffee Section -->
    <!-- <section class="innovative-coffee">
    <div class="innovative-overlay">
        <h2>Innovative Coffee in an Innovative Era</h2>
        <p>Driven by technology, our retail model is built upon our Mobile App and store network.</p>
    </div>
    </section> -->

    <!-- Contact Us Section -->
    <section class="contact-section">
    <h2><span><img src="assets/images/cat-envelope.png" alt="Paw" style="height:70px; vertical-align:middle;"></span> Drop us a Message</h2>
    <div class="contact-container">
        <div class="contact-info">
            <img src="assets/images/contact-us.webp" alt="Contact">
        </div>
        <form class="contact-form" method="post" action="">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="What's your email?" required>
        <textarea name="message" placeholder="Your questions..." required></textarea>
        <button type="submit" name="contact_submit">Send Message</button>
        </form>
    </div>
    </section>

    <?php if ($contactSuccess): ?>
<script>
    window.onload = function() {
        alert("The message has been sent.");
    }
</script>
<?php endif; ?>


    <!-- About Section
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
    </section> -->

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
    <script>
        const adminToggle = document.querySelector('.admin-toggle');
        const dropdownMenu = document.querySelector('.admin-dropdown-menu');

        adminToggle.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Optional: Close when clicking outside
        window.addEventListener('click', function (e) {
            if (!e.target.closest('.admin-dropdown')) {
                dropdownMenu.style.display = 'none';
            }
        });
    </script>
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