<?php
require_once '../config.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../gritfavicon.jpg" type="image/jpeg">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>GRIT ADMIN</h2>
                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle">☰</button>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="analytics.php">Analytics</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Products</h1>
                <a href="add_product.php" class="btn btn-primary">Add New Product</a>
            </div>
            
            <!-- Products Table -->
            <div class="content-card">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src="../assets/images/black-hoodie.jpg" alt="Bacolod Underground Hoodie" class="table-image"></td>
                            <td>Bacolod Underground Hoodie</td>
                            <td>Streetwear</td>
                            <td>₱799.00</td>
                            <td>20</td>
                            <td class="action-buttons">
                                <a href="edit_product.php?id=1" class="btn btn-outline">Edit</a>
                                <button class="btn btn-outline">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="../assets/images/denim-jacket.jpg" alt="Negros Street Denim" class="table-image"></td>
                            <td>Negros Street Denim</td>
                            <td>Streetwear</td>
                            <td>₱1,299.00</td>
                            <td>15</td>
                            <td class="action-buttons">
                                <a href="edit_product.php?id=2" class="btn btn-outline">Edit</a>
                                <button class="btn btn-outline">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="../assets/images/leather-wallet.jpg" alt="Silay Leather Wallet" class="table-image"></td>
                            <td>Silay Leather Wallet</td>
                            <td>Accessories</td>
                            <td>₱599.00</td>
                            <td>30</td>
                            <td class="action-buttons">
                                <a href="edit_product.php?id=3" class="btn btn-outline">Edit</a>
                                <button class="btn btn-outline">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td><img src="../assets/images/sneakers.jpg" alt="Bacolod Canvas Sneakers" class="table-image"></td>
                            <td>Bacolod Canvas Sneakers</td>
                            <td>Footwear</td>
                            <td>₱1,499.00</td>
                            <td>25</td>
                            <td class="action-buttons">
                                <a href="edit_product.php?id=4" class="btn btn-outline">Edit</a>
                                <button class="btn btn-outline">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle for admin sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>