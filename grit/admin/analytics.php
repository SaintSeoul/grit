<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all statistics
// Users
$userCountSql = "SELECT COUNT(*) as total FROM users";
$userCountResult = $conn->query($userCountSql);
$userCount = $userCountResult->fetch_assoc()['total'];

$adminCountSql = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$adminCountResult = $conn->query($adminCountSql);
$adminCount = $adminCountResult->fetch_assoc()['total'];

$customerCountSql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$customerCountResult = $conn->query($customerCountSql);
$customerCount = $customerCountResult->fetch_assoc()['total'];

// Products
$productCountSql = "SELECT COUNT(*) as total FROM products";
$productCountResult = $conn->query($productCountSql);
$productCount = $productCountResult->fetch_assoc()['total'];

$categoryCountSql = "SELECT COUNT(*) as total FROM categories";
$categoryCountResult = $conn->query($categoryCountSql);
$categoryCount = $categoryCountResult->fetch_assoc()['total'];

$inventoryValueSql = "SELECT SUM(price * stock) as total FROM products";
$inventoryValueResult = $conn->query($inventoryValueSql);
$inventoryValue = $inventoryValueResult->fetch_assoc()['total'];
$inventoryValue = $inventoryValue ? $inventoryValue : 0;

// Orders
$orderCountSql = "SELECT COUNT(*) as total FROM orders";
$orderCountResult = $conn->query($orderCountSql);
$orderCount = $orderCountResult->fetch_assoc()['total'];

$revenueSql = "SELECT SUM(total_amount) as total FROM orders";
$revenueResult = $conn->query($revenueSql);
$revenue = $revenueResult->fetch_assoc()['total'];
$revenue = $revenue ? $revenue : 0;

// Get recent orders (last 30 days)
$recentOrdersSql = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date";
$recentOrdersResult = $conn->query($recentOrdersSql);
$revenueData = [];
while ($row = $recentOrdersResult->fetch_assoc()) {
    $revenueData[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - GRIT Admin</title>
    <link rel="icon" type="image/x-icon" href="../gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>GRIT ADMIN</h2>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="admins.php">Admins</a></li>
                    <li><a href="analytics.php" class="active">Analytics</a></li>
                    <li><a href="messages.php">Messages</a></li>
                </ul>
                <ul>
                    <li><a href="../index.php">Shop</a></li>
                    <li><a href="../index.php#products">View Products</a></li>
                    <li><a href="../cart.php">View Cart</a></li>
                </ul>
                <ul>
                    <li><a href="admin_profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Analytics Overview</h1>
            </div>

            <!-- Overall Statistics -->
            <div class="content-card">
                <h2>Overall Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $currency_symbol . number_format($revenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $orderCount; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $productCount; ?></h3>
                        <p>Total Products</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $currency_symbol . number_format($inventoryValue, 2); ?></h3>
                        <p>Inventory Value</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $userCount; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $categoryCount; ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="content-card">
                <h2>User Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $adminCount; ?></h3>
                        <p>Admin Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $customerCount; ?></h3>
                        <p>Customers</p>
                    </div>
                </div>
            </div>

            <!-- Revenue Trend (Last 30 Days) -->
            <div class="content-card">
                <h2>Revenue Trend (Last 30 Days) in <?php echo $currency_symbol; ?></h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($revenueData) > 0): ?>
                                <?php foreach($revenueData as $data): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($data['date'])); ?></td>
                                    <td><?php echo $currency_symbol . number_format($data['revenue'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">No revenue data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>