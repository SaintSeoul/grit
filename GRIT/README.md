# GRIT E-commerce Website

A fully functional e-commerce website built with PHP, MySQL, HTML, and CSS. The website features a modern design inspired by thisisneverthat with role-based access control for admin and customer users.

## Features

### Frontend
- Responsive design with mobile-friendly interface
- Product catalog with categories
- Shopping cart functionality
- User registration and login
- User profile management
- Checkout process

### Backend
- Admin dashboard with statistics
- Product management (add, edit, delete)
- Category management
- User management
- Order management
- Role-based access control (admin/customer)

## Technology Stack
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: Apache (XAMPP)

## Setup Instructions

1. **Database Setup**:
   - Create a database named `grit` in phpMyAdmin
   - Import the `database.sql` file to create tables and sample data
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

2. **File Setup**:
   - Place all files in your XAMPP htdocs directory (e.g., `c:\xampp\htdocs\grit`)
   - Ensure Apache and MySQL services are running

3. **Configuration**:
   - Database configuration is in `config.php`
   - Update database credentials if needed

## Directory Structure
```
grit/
├── admin/           # Admin dashboard files
├── assets/          # CSS, JS, and image files
│   ├── css/
│   ├── js/
│   └── images/
├── config.php       # Database and site configuration
├── database.sql     # Database schema and sample data
├── index.php        # Homepage
├── login.php        # User login
├── register.php     # User registration
├── profile.php      # User profile
├── cart.php         # Shopping cart
├── checkout.php     # Checkout process
├── product.php      # Product details
├── category.php     # Category listing
├── about.php        # About page
├── contact.php      # Contact page
└── privacy.php      # Privacy policy
```

## Default Credentials
- **Admin**: 
  - Username: `admin`
  - Password: `admin123`
- **Customer**: 
  - Register a new account

## Brand Information
- **Name**: GRIT
- **Tagline**: the strength to keep going
- **Location**: Bacolod City, Philippines

## License
This project is for educational purposes only.