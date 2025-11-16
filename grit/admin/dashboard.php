<?php
require_once '../config.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../login.php');
}

// Get user profile
$user_profile = getUserProfile($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $site_title; ?></title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
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
                <h1 class="page-title">Dashboard</h1>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>₱24,830</h3>
                    <p>Total Revenue</p>
                </div>
                
                <div class="stat-card">
                    <h3>128</h3>
                    <p>Total Orders</p>
                </div>
                
                <div class="stat-card">
                    <h3>42</h3>
                    <p>Products Sold</p>
                </div>
                
                <div class="stat-card">
                    <h3>24</h3>
                    <p>New Customers</p>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="content-card">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1001</td>
                            <td>John Doe</td>
                            <td>2023-06-15</td>
                            <td>₱2,483.00</td>
                            <td><span class="status pending">Pending</span></td>
                            <td class="action-buttons">
                                <a href="order_details.php?id=1001" class="btn btn-outline">View</a>
                            </td>
                        </tr>
                        <tr>
                            <td>#1002</td>
                            <td>Jane Smith</td>
                            <td>2023-06-14</td>
                            <td>₱1,899.00</td>
                            <td><span class="status shipped">Shipped</span></td>
                            <td class="action-buttons">
                                <a href="order_details.php?id=1002" class="btn btn-outline">View</a>
                            </td>
                        </tr>
                        <tr>
                            <td>#1003</td>
                            <td>Robert Johnson</td>
                            <td>2023-06-13</td>
                            <td>₱3,250.00</td>
                            <td><span class="status delivered">Delivered</span></td>
                            <td class="action-buttons">
                                <a href="order_details.php?id=1003" class="btn btn-outline">View</a>
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