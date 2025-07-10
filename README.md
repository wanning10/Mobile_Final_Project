# Cozy Beverage Web App

A comprehensive mobile-responsive web application for a beverage shop with full e-commerce functionality, user management, and location tracking.

## Features

### 🛍️ E-Commerce Features
- **Product Catalog**: Browse products by categories (Tea, Coffee, Bread, Snacks)
- **Shopping Cart**: Add, update, and remove items with real-time updates
- **Checkout System**: Complete order process with shipping information
- **Order Management**: Track order status and view order history

### 👤 User Management
- **User Registration**: Create new accounts with email verification
- **User Login/Logout**: Secure authentication system
- **Profile Management**: Update email and password
- **Account Deletion**: Users can delete their own accounts
- **Admin Panel**: Complete CRUD operations for all users

### 🗺️ Location & Media Features
- **OpenStreetMap Integration**: Interactive map showing shop location
- **User Location Tracking**: Get directions from user's current location
- **Audio/Video Integration**: Background music and promotional videos
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile

### 🔍 Search & Filter
- **Product Search**: Search products by name or description
- **Category Filter**: Filter products by category
- **Real-time Updates**: AJAX-powered cart and search functionality

### 🎨 Design & UX
- **Modern UI**: Clean, professional design with smooth animations
- **Mobile-First**: Responsive design that adapts to all screen sizes
- **Accessibility**: WCAG compliant with proper focus management
- **Loading States**: Visual feedback for all user interactions

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL (via XAMPP)
- **Maps**: OpenStreetMap with Leaflet.js
- **Icons**: Font Awesome 6.0
- **Server**: XAMPP (Apache + MySQL)

## Installation & Setup

### Prerequisites
- XAMPP (or similar local server stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Download and Setup
1. Download or clone this project to your XAMPP `htdocs` folder
2. Start XAMPP Control Panel
3. Start Apache and MySQL services

### Step 2: Database Setup
1. Open your web browser and navigate to `http://localhost/phpmyadmin`
2. The database will be created automatically when you first visit the application
3. Default admin credentials:
   - Username: `admin`
   - Password: `admin123`

### Step 3: Access the Application
1. Open your web browser
2. Navigate to `http://localhost/CozyBeverageApp`
3. The application will automatically create the database and tables

## File Structure

```
CozyBeverageApp/
├── index.php                 # Main homepage
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # Logout functionality
├── products.php              # Product catalog
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout process
├── order_confirmation.php    # Order confirmation
├── map.php                   # Location map
├── about.php                 # About page
├── profile.php               # User profile
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── functions.php         # Helper functions
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── main.js           # Main JavaScript
│   ├── images/               # Product images
│   └── media/                # Audio/video files
├── ajax/
│   ├── add_to_cart.php       # AJAX cart handlers
│   ├── update_cart.php
│   └── remove_from_cart.php
└── admin/
    └── index.php             # Admin dashboard
```

## Database Schema

### Users Table
- `id` (Primary Key)
- `username` (Unique)
- `email` (Unique)
- `password` (Hashed)
- `is_admin` (Boolean)
- `created_at`, `updated_at` (Timestamps)

### Categories Table
- `id` (Primary Key)
- `name`
- `description`
- `icon` (Font Awesome class)

### Products Table
- `id` (Primary Key)
- `name`
- `description`
- `price`
- `image_url`
- `category_id` (Foreign Key)
- `is_featured` (Boolean)
- `stock_quantity`
- `created_at`, `updated_at` (Timestamps)

### Cart Table
- `id` (Primary Key)
- `user_id` (Foreign Key)
- `product_id` (Foreign Key)
- `quantity`
- `created_at` (Timestamp)

### Orders Table
- `id` (Primary Key)
- `user_id` (Foreign Key)
- `total_amount`
- `status` (Enum: pending, confirmed, shipped, delivered, cancelled)
- `created_at` (Timestamp)

### Order Items Table
- `id` (Primary Key)
- `order_id` (Foreign Key)
- `product_id` (Foreign Key)
- `quantity`
- `price`

## Usage Guide

### For Customers
1. **Registration**: Create a new account or login with existing credentials
2. **Browse Products**: View products by category or search for specific items
3. **Add to Cart**: Click "Add to Cart" on any product
4. **Manage Cart**: Update quantities or remove items from cart
5. **Checkout**: Complete the checkout process with shipping information
6. **Track Orders**: View order history and status in your profile

### For Administrators
1. **Login**: Use admin credentials (admin/admin123)
2. **Dashboard**: View statistics and recent orders
3. **Manage Products**: Add, edit, or delete products
4. **Manage Categories**: Organize products by categories
5. **Manage Users**: View and manage user accounts
6. **View Orders**: Track all customer orders

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Prevention**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **CSRF Protection**: Form validation and token checking
- **Input Validation**: Server-side validation for all user inputs

## Responsive Design

The application is fully responsive and works on:
- **Desktop**: Full-featured experience with hover effects
- **Tablet**: Optimized layout for medium screens
- **Mobile**: Touch-friendly interface with mobile navigation

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Customization

### Adding New Products
1. Access the admin panel
2. Navigate to Products management
3. Add new products with images, descriptions, and pricing

### Modifying Categories
1. Access the admin panel
2. Navigate to Categories management
3. Add or modify product categories

### Changing Shop Location
1. Edit `map.php` file
2. Update the `shopLocation` coordinates
3. Update the address information

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`

2. **Images Not Loading**
   - Ensure `assets/images/` directory exists
   - Check file permissions

3. **Map Not Loading**
   - Check internet connection (required for OpenStreetMap tiles)
   - Ensure JavaScript is enabled

4. **Cart Not Working**
   - Ensure user is logged in
   - Check browser console for JavaScript errors

### Performance Tips

1. **Optimize Images**: Compress product images for faster loading
2. **Enable Caching**: Configure browser caching for static assets
3. **Database Indexing**: Add indexes to frequently queried columns
4. **CDN Usage**: Consider using CDN for external libraries

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support or questions:
- Email: info@cozybeverage.com
- Phone: (555) 123-4567

## Changelog

### Version 1.0.0 (2024)
- Initial release
- Complete e-commerce functionality
- User management system
- Admin panel
- Location tracking
- Responsive design
- Audio/video integration

---

**Enjoy your Cozy Beverage experience! ☕** 