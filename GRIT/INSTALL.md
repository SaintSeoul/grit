# Installation Guide for GRIT E-commerce Website

## Prerequisites
- XAMPP installed on your system
- Apache and MySQL services running

## Installation Steps

### 1. Copy Files
1. Copy the entire `grit` folder to your XAMPP htdocs directory:
   - Windows: `C:\xampp\htdocs\grit`
   - Make sure the full path is: `C:\xampp\htdocs\grit\index.php`

### 2. Database Setup
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
4. Create a new database named `grit`
5. Select the `grit` database
6. Click on the "Import" tab
7. Choose the `database.sql` file from the project directory
8. Click "Go" to import the database

### 3. Verify Installation
1. Open your browser and navigate to: `http://localhost/grit`
2. You should see the homepage of the GRIT e-commerce website
3. Test the database connection by visiting: `http://localhost/grit/test_db.php`

### 4. Admin Access
1. Go to: `http://localhost/grit/login.php`
2. Login with admin credentials:
   - Username: `admin`
   - Password: `admin123`
3. After login, you'll be redirected to the admin dashboard at: `http://localhost/grit/admin/dashboard.php`

## Troubleshooting

### Common Issues
1. **404 Error**: Make sure XAMPP Apache service is running and files are in the correct directory
2. **Database Connection Error**: 
   - Verify MySQL service is running
   - Check database credentials in `config.php`
   - Ensure database `grit` exists and is imported correctly
3. **Permission Denied**: Make sure the htdocs folder has proper read permissions

### File Permissions
- All files should be readable by the web server
- The `assets/images` directory should be writable for product image uploads

## Default User Accounts
- **Admin User**:
  - Username: `admin`
  - Password: `admin123`
- **Sample Customer**: Register a new account through the website

## Testing
1. **Frontend Testing**:
   - Browse products
   - Add items to cart
   - Proceed through checkout
   - Register and login as customer

2. **Backend Testing**:
   - Login as admin
   - Add/edit/delete products
   - Manage categories
   - View orders
   - Manage users

## Mobile Responsiveness
- Test the website on different screen sizes
- Verify the mobile menu toggle works
- Check product grid responsiveness
- Test all forms on mobile devices

## Security Notes
- Change the default admin password after installation
- The database.sql file contains a hashed password for the admin user
- Never expose the database.sql file publicly
- The .htaccess file prevents direct access to sensitive files

## Support
For issues with installation or usage, please refer to the README.md file or contact the development team.