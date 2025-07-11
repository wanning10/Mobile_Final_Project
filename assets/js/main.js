// Main JavaScript for Cozy Beverage Web App

document.addEventListener('DOMContentLoaded', function() {
    // Modal logic for Add Product
    const showBtn = document.getElementById('show-add-product');
    const modal = document.getElementById('addProductModal');
    const closeBtn = document.getElementById('closeAddProduct');
    if (showBtn && modal && closeBtn) {
        showBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Modal logic for Add Category
    const showCatBtn = document.getElementById('show-add-category');
    const catModal = document.getElementById('addCategoryModal');
    const closeCatBtn = document.getElementById('closeAddCategory');
    if (showCatBtn && catModal && closeCatBtn) {
        showCatBtn.addEventListener('click', function() {
            catModal.style.display = 'flex';
        });
        closeCatBtn.addEventListener('click', function() {
            catModal.style.display = 'none';
        });
        window.addEventListener('click', function(e) {
            if (e.target === catModal) {
                catModal.style.display = 'none';
            }
        });
    }

    // Mobile Navigation Toggle
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }
    
    // Add to Cart Functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            addToCart(productId);
        });
    });
    
    // Cart Quantity Updates
    const quantityInputs = document.querySelectorAll('.cart-quantity');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(this.value);
            updateCartQuantity(productId, quantity);
        });
    });
    
    // Remove from Cart
    const removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            removeFromCart(productId);
        });
    });
    
    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Search Functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            performSearch(this.value);
        }, 300));
    }
    
    // Category Filter
    const categorySelect = document.getElementById('category-filter');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterByCategory(this.value);
        });
    }
    
    // Initialize Map if on map page
    if (window.location.pathname.includes('map.php')) {
        initializeMap();
    }
    
    // Audio/Video Controls
    initializeMediaControls();
});

// Add to Cart Function
function addToCart(productId) {
    if (!isLoggedIn()) {
        showAlert('Please login to add items to cart', 'warning');
        return;
    }
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Item added to cart successfully!', 'success');
            updateCartCount(data.cart_count);
        } else {
            showAlert(data.message || 'Failed to add item to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while adding to cart', 'error');
    });
}

// Update Cart Quantity
function updateCartQuantity(productId, quantity) {
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data);
        } else {
            showAlert(data.message || 'Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating cart', 'error');
    });
}

// Remove from Cart
function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(data);
                showAlert('Item removed from cart', 'success');
            } else {
                showAlert(data.message || 'Failed to remove item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while removing item', 'error');
        });
    }
}

// Update Cart Display
function updateCartDisplay(data) {
    const cartContainer = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const cartCount = document.querySelector('.cart-badge');
    
    if (cartContainer && data.cart_html) {
        cartContainer.innerHTML = data.cart_html;
    }
    
    if (cartTotal && data.total) {
        cartTotal.textContent = '$' + parseFloat(data.total).toFixed(2);
    }
    
    if (cartCount) {
        cartCount.textContent = data.cart_count || 0;
    }
    
    updateCartCount(data.cart_count);
}

// Update Cart Count
function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.textContent = count || 0;
        if (count > 0) {
            cartBadge.style.display = 'inline';
        } else {
            cartBadge.style.display = 'none';
        }
    }
}

// Form Validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
        
        // Email validation
        if (input.type === 'email' && input.value) {
            if (!isValidEmail(input.value)) {
                showFieldError(input, 'Please enter a valid email address');
                isValid = false;
            }
        }
        
        // Password validation
        if (input.type === 'password' && input.value) {
            if (input.value.length < 6) {
                showFieldError(input, 'Password must be at least 6 characters');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

// Email Validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show Field Error
function showFieldError(input, message) {
    clearFieldError(input);
    input.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '0.25rem';
    input.parentNode.appendChild(errorDiv);
}

// Clear Field Error
function clearFieldError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Show Alert
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Insert at the top of the page
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Search Functionality
function performSearch(query) {
    if (query.length < 2) return;
    
    fetch(`ajax/search_products.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            updateProductsDisplay(data.products);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

// Filter by Category
function filterByCategory(categoryId) {
    const url = categoryId ? `products.php?category=${categoryId}` : 'products.php';
    window.location.href = url;
}

// Update Products Display
function updateProductsDisplay(products) {
    const productsGrid = document.querySelector('.products-grid');
    if (!productsGrid) return;
    
    if (products.length === 0) {
        productsGrid.innerHTML = '<p class="no-results">No products found</p>';
        return;
    }
    
    let html = '';
    products.forEach(product => {
        html += `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.name}">
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                    <button class="btn btn-primary add-to-cart" data-product-id="${product.id}">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
        `;
    });
    
    productsGrid.innerHTML = html;
    
    // Reattach event listeners
    const addToCartButtons = productsGrid.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            addToCart(productId);
        });
    });
}

// Initialize Map
function initializeMap() {
    // Cozy Beverage Shop Location (example coordinates)
    const shopLocation = [40.7128, -74.0060]; // New York coordinates
    
    const map = L.map('map').setView(shopLocation, 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add shop marker
    const shopMarker = L.marker(shopLocation).addTo(map);
    shopMarker.bindPopup('<b>Cozy Beverage Shop</b><br>123 Coffee Street<br>New York, NY 10001').openPopup();
    
    // Get user location if permission granted
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLocation = [position.coords.latitude, position.coords.longitude];
            const userMarker = L.marker(userLocation).addTo(map);
            userMarker.bindPopup('<b>Your Location</b>').openPopup();
            
            // Draw route to shop
            const route = L.polyline([userLocation, shopLocation], {color: 'red'}).addTo(map);
            
            // Fit map to show both markers
            map.fitBounds(L.latLngBounds([userLocation, shopLocation]));
        }, function(error) {
            console.log('Geolocation error:', error);
        });
    }
}

// Initialize Media Controls
function initializeMediaControls() {
    const audio = document.getElementById('cozy-audio');
    const video = document.getElementById('hero-video');
    
    if (audio) {
        // Auto-play with user interaction
        document.addEventListener('click', function() {
            if (audio.paused) {
                audio.play().catch(e => console.log('Audio autoplay prevented'));
            }
        }, { once: true });
    }
    
    if (video) {
        // Video controls
        video.addEventListener('loadedmetadata', function() {
            console.log('Video loaded:', video.duration, 'seconds');
        });
        
        video.addEventListener('error', function() {
            console.log('Video error:', video.error);
        });
    }
}

// Utility Functions
function isLoggedIn() {
    return document.body.classList.contains('logged-in');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Loading States
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Loading...';
    button.disabled = true;
    return originalText;
}

function hideLoading(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
});

// Prevent form resubmission
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
} 