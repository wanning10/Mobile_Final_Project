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
    <title>Location - CATFE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            <div class="nav-menu" id="nav-menu">
            <!-- Centered menu links -->
            <!-- <div class="nav-center-menu"> -->
                <a href="index.php" class="nav-link">HOME</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <a href="products.php" class="nav-link">PRODUCTS</a>
                <a href="map.php" class="nav-link active">MAP</a>
            <!-- </div> -->

            <!-- Right icons (cart & profile) -->
            <!-- <div class="nav-right-icons"> -->
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($isLoggedIn): ?>
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
                <?php else: ?>
                    <a href="login.php" class="nav-link">
                        LOGIN
                    </a>
                <?php endif; ?>
            <!-- </div> -->
            </div>

            <div class="nav-toggle" id="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <!-- <h2><i class="fas fa-map-marker-alt"></i> Find Us</h2> -->
            <!-- Map Title -->
            <h2 class="map-title">
                Store Locator
            </h2>
            <!-- Map Description -->
            <p class="map-description">
                Check the map below to visit us and make your day purrfect.
            </p>
            
            <div class="location-info">
            <div class="shop-card">
                <div class="shop-details">
                    <img src="assets/images/logo.png" alt="Catfe Logo" class="shop-logo">
                    <h3>CATFE</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jalan Sunsuria, Bandar Sunsuria, 43900 Sepang, Selangor</p>
                    <p><i class="fas fa-phone"></i> 60123456789</p>
                    <p><i class="fas fa-envelope"></i> catfe@example.com</p>
                    <p><i class="fas fa-clock"></i> Open Daily: 7AM - 9PM</p>
                </div>

                <div class="map-controls">
                    <button id="get-location" class="btn btn-primary">
                        <i class="fas fa-location-arrow"></i> Get My Location
                    </button>
                    <button id="show-route" class="btn btn-secondary">
                        <i class="fas fa-route"></i> Show Route
                    </button>
                </div>
            </div>

            <div class="map-container">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
            </div>
            
            <div class="location-features">
                <div class="feature">
                    <i class="fas fa-parking"></i>
                    <h4>Free Parking</h4>
                    <p>Convenient parking available</p>
                </div>
                <div class="feature">
                    <i class="fas fa-wifi"></i>
                    <h4>Free WiFi</h4>
                    <p>Stay connected while you relax</p>
                </div>
                <div class="feature">
                    <i class="fas fa-wheelchair"></i>
                    <h4>Accessible</h4>
                    <p>Wheelchair accessible entrance</p>
                </div>
                <div class="feature">
                    <i class="fas fa-bicycle"></i>
                    <h4>Bike Friendly</h4>
                    <p>Bike racks available</p>
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
                <!-- <div class="footer-section" style="padding-left: 5em;"> -->
                <div class="footer-section quick-links">
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

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeMap();
    });

    function initializeMap() {
        // Cozy Beverage Shop New Location: Jalan Sunsuria, Bandar Sunsuria, 43900 Sepang, Selangor
        const shopLocation = [2.8268, 101.6682];

        // Initialize map centered at shop location
        // const map = L.map('map').setView(shopLocation, 17);

        const map = L.map('map', {
            center: shopLocation,
            zoom: 17,
            zoomControl: false // disable default
        });

        // Add zoom control to bottom right
        L.control.zoom({
            position: 'bottomright' // or 'topright', 'bottomleft'
        }).addTo(map);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add custom shop marker
        const shopIcon = L.divIcon({
            html: '<i class="fas fa-coffee" style="color: #8B4513; font-size: 24px;"></i>',
            className: 'custom-marker',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
        });

        const shopMarker = L.marker(shopLocation, {icon: shopIcon}).addTo(map);
        shopMarker.bindPopup(`
            <div style="text-align: center;">
                <h3 style="color: #8B4513; margin: 0 0 10px 0;">CATFE</h3>
                <p style="margin: 5px 0;"><i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> Jalan Sunsuria, Bandar Sunsuria, <br>43900 Sepang, Selangor</p>
                <p style="margin: 5px 0;"><i class="fas fa-phone" style="margin-right: 0.5rem;"></i> 60123456789</p>
                <p style="margin: 5px 0;"><i class="fas fa-clock" style="margin-right: 0.5rem;"></i> Open Daily: 7AM - 9PM</p>
            </div>
        `).openPopup();

        // Handle "Get My Location" button
        document.getElementById('get-location').addEventListener('click', function() {
            const btn = this;
            if (navigator.geolocation) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting Location...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = [position.coords.latitude, position.coords.longitude];

                    // Add user marker
                    const userIcon = L.divIcon({
                        html: '<i class="fas fa-user" style="color: #007bff; font-size: 20px;"></i>',
                        className: 'user-marker',
                        iconSize: [25, 25],
                        iconAnchor: [12, 25]
                    });
                    const userMarker = L.marker(userLocation, {icon: userIcon}).addTo(map);
                    userMarker.bindPopup('<b>Your Location</b>').openPopup();

                    // Draw route to shop
                    const route = L.polyline([userLocation, shopLocation], {
                        color: '#8B4513',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(map);

                    // Fit map to both points
                    map.fitBounds(L.latLngBounds([userLocation, shopLocation]));

                    // Calculate distance
                    const distance = calculateDistance(userLocation[0], userLocation[1], shopLocation[0], shopLocation[1]);
                    btn.innerHTML = `<i class="fas fa-check"></i> Distance: ${distance.toFixed(2)} km`;
                }, function(error) {
                    btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Location Unavailable';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-location-arrow"></i> Get My Location';
                        btn.disabled = false;
                    }, 3000);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });

        // Handle "Show Route" button
        document.getElementById('show-route').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = [position.coords.latitude, position.coords.longitude];
                    // Open Google Maps Directions
                    const url = `https://www.google.com/maps/dir/?api=1&origin=${userLocation[0]},${userLocation[1]}&destination=${shopLocation[0]},${shopLocation[1]}`;
                    window.open(url, '_blank');
                }, function() {
                    alert('Unable to access your location.');
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });
    }

    // Haversine formula for distance in km
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2-lat1) * Math.PI/180;
        const dLon = (lon2-lon1) * Math.PI/180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    </script>
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