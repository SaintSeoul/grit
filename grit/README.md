# GRIT E-commerce Website

A fully functional e-commerce website with role-based admin login, built with PHP, MySQL, HTML, and CSS.

## Features

- Dark theme design similar to Covernat
- Role-based user authentication (admin/customer)
- Admin dashboard for product management
- Add, edit, and delete products
- Responsive design
- Secure login system

## Installation

1. **Database Setup**:
   - Open phpMyAdmin
   - Create a new database named `grit`
   - Import the `database.sql` file to set up tables and sample data

2. **Configuration**:
   - Ensure your XAMPP server is running
   - Place all files in your XAMPP htdocs directory (e.g., `c:\xampp\htdocs\grit\`)
   - The database configuration is set in `config.php` (default: root user with no password)

3. **Admin Access**:
   - Visit `http://localhost/grit/login.php`
   - Use the default admin credentials:
     - Username: `admin`
     - Password: `admin123`

4. **Frontend Access**:
   - Visit `http://localhost/grit/` to view the customer-facing website

## File Structure

```
grit/
├── admin/
│   ├── dashboard.php
│   ├── products.php
│   ├── add_product.php
│   └── edit_product.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── config.php
├── database.sql
├── index.php
├── login.php
└── logout.php
```

## Admin Dashboard Features

- Dashboard overview with product statistics
- Product management (add, edit, delete)
- Category management
- Order management
- User management

## Security Features

- Password hashing using bcrypt
- Prepared statements to prevent SQL injection
- Session management
- Input sanitization

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache)

## Customization

You can customize the look and feel by modifying the CSS file at `assets/css/style.css`. The design uses CSS variables for easy theme customization.