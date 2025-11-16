<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle order status update
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            $message = "Order status updated successfully!";
        } else {
            $error = "Error updating order status. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Invalid status selected.";
    }
}

// Get all orders with user information
$sql = "SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
$result = $conn->query($sql);

// Get order statistics
$totalOrdersSql = "SELECT COUNT(*) as total FROM orders";
$totalOrdersResult = $conn->query($totalOrdersSql);
$totalOrders = $totalOrdersResult->fetch_assoc()['total'];

// Get total revenue
$totalRevenueSql = "SELECT SUM(total_amount) as total FROM orders";
$totalRevenueResult = $conn->query($totalRevenueSql);
$totalRevenue = $totalRevenueResult->fetch_assoc()['total'];
$totalRevenue = $totalRevenue ? $totalRevenue : 0;

// Get orders by status
$statusStatsSql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$statusStatsResult = $conn->query($statusStatsSql);
$statusStats = [];
while ($row = $statusStatsResult->fetch_assoc()) {
    $statusStats[$row['status']] = $row['count'];
}

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
    <title>Manage Orders - GRIT Admin</title>
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
                    <li><a href="orders.php" class="active">Orders</a></li>
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
                    <li><a href="admin_profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Orders</h1>
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

            <!-- Order Statistics -->
            <div class="content-card">
                <h2>Order Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $currency_symbol . number_format($totalRevenue, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo isset($statusStats['pending']) ? $statusStats['pending'] : 0; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo isset($statusStats['delivered']) ? $statusStats['delivered'] : 0; ?></h3>
                        <p>Delivered Orders</p>
                    </div>
                </div>
            </div>

            <!-- Orders by Status -->
            <div class="content-card">
                <h2>Orders by Status</h2>
                <div class="stats-grid">
                    <?php foreach ($statusStats as $status => $count): ?>
                    <div class="stat-card">
                        <h3><?php echo $count; ?></h3>
                        <p><?php echo ucfirst($status); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['username'] ?: 'Guest'; ?></td>
                                    <td><?php echo $currency_symbol . number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select">
                                                <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No orders found</td>
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