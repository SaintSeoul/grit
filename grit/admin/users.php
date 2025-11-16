<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get all users
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

// Get user statistics
$totalUsersSql = "SELECT COUNT(*) as total FROM users";
$totalUsersResult = $conn->query($totalUsersSql);
$totalUsers = $totalUsersResult->fetch_assoc()['total'];

// Get admin users count
$adminUsersSql = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$adminUsersResult = $conn->query($adminUsersSql);
$adminUsers = $adminUsersResult->fetch_assoc()['total'];

// Get customer users count
$customerUsersSql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$customerUsersResult = $conn->query($customerUsersSql);
$customerUsers = $customerUsersResult->fetch_assoc()['total'];

// Get recent users (last 30 days)
$recentUsersSql = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$recentUsersResult = $conn->query($recentUsersSql);
$recentUsers = $recentUsersResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - GRIT Admin</title>
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
                    <li><a href="users.php" class="active">Users</a></li>
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
                    <li><a href="admin_profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Users</h1>
            </div>

            <!-- User Statistics -->
            <div class="content-card">
                <h2>User Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $adminUsers; ?></h3>
                        <p>Admin Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $customerUsers; ?></h3>
                        <p>Customers</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $recentUsers; ?></h3>
                        <p>New (30 days)</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <a href="admins.php" class="btn btn-primary">Manage Admin Users</a>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo ucfirst($user['role']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No users found</td>
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