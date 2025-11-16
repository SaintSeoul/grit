<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get admin profile info
$admin_username = $_SESSION['username'];
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get admin-specific stats
// Total orders
$orderCountSql = "SELECT COUNT(*) as count FROM orders";
$orderCountResult = $conn->query($orderCountSql);
$orderCount = $orderCountResult->num_rows > 0 ? $orderCountResult->fetch_assoc()['count'] : 0;

// Total products
$productCountSql = "SELECT COUNT(*) as count FROM products";
$productCountResult = $conn->query($productCountSql);
$productCount = $productCountResult->num_rows > 0 ? $productCountResult->fetch_assoc()['count'] : 0;

// Total users
$userCountSql = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
$userCountResult = $conn->query($userCountSql);
$userCount = $userCountResult->num_rows > 0 ? $userCountResult->fetch_assoc()['count'] : 0;

// Total revenue
$revenueSql = "SELECT SUM(total_amount) as total FROM orders";
$revenueResult = $conn->query($revenueSql);
$revenue = $revenueResult->num_rows > 0 ? $revenueResult->fetch_assoc()['total'] : 0;
$revenue = $revenue ? $revenue : 0;

$message = '';
$error = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - GRIT</title>
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
                    <li><a href="analytics.php">Analytics</a></li>
                    <li><a href="messages.php">Messages</a></li>
                </ul>
                <ul>
                    <li><a href="../index.php">Shop</a></li>
                    <li><a href="../index.php#products">View Products</a></li>
                    <li><a href="../cart.php">View Cart</a></li>
                </ul>
                <ul>
                    <li><a href="admin_profile.php" class="active">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Admin Profile</h1>
            </div>

            <?php if ($message): ?>
                <div class="content-card">
                    <div style="background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); padding: 15px; border-radius: 4px; border: 1px solid var(--success-color);">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="content-card">
                    <div style="background-color: rgba(244, 67, 54, 0.2); color: var(--danger-color); padding: 15px; border-radius: 4px; border: 1px solid var(--danger-color);">
                        <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <h2>Admin Information</h2>
                <div class="form-group">
                    <label>Username</label>
                    <div class="form-control"><?php echo htmlspecialchars($admin_username); ?></div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <div class="form-control"><?php echo htmlspecialchars($admin_email); ?></div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <div class="form-control">Administrator</div>
                </div>
            </div>

            <div class="content-card">
                <h2>System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>$<?php echo number_format($revenue, 2); ?></h3>
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
                        <h3><?php echo $userCount; ?></h3>
                        <p>Customer Count</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>