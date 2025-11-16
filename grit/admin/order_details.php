<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

// Get order details
$orderSql = "SELECT o.*, u.username, u.email, up.first_name, up.last_name, up.phone, up.address, up.city, up.country 
             FROM orders o 
             LEFT JOIN users u ON o.user_id = u.id 
             LEFT JOIN user_profiles up ON o.user_id = up.user_id 
             WHERE o.id = ?";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("i", $order_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows == 0) {
    redirect('orders.php');
}

$order = $orderResult->fetch_assoc();
$orderStmt->close();

// Get order items
$itemsSql = "SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$itemsStmt = $conn->prepare($itemsSql);
$itemsStmt->bind_param("i", $order_id);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$itemsStmt->close();

// Handle order status update
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $status = sanitizeInput($_POST['status']);
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            $message = "Order status updated successfully!";
            // Refresh order data
            $orderStmt = $conn->prepare($orderSql);
            $orderStmt->bind_param("i", $order_id);
            $orderStmt->execute();
            $orderResult = $orderStmt->get_result();
            $order = $orderResult->fetch_assoc();
            $orderStmt->close();
        } else {
            $error = "Error updating order status. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Invalid status selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - GRIT Admin</title>
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
                <h1 class="page-title">Order Details</h1>
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
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
                <h2>Order #<?php echo $order['id']; ?></h2>
                
                <div class="order-details-grid">
                    <div class="order-info">
                        <h3>Order Information</h3>
                        <p><strong>Status:</strong> 
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </p>
                        <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                        <p><strong>Total Amount:</strong> <?php echo $currency_symbol . number_format($order['total_amount'], 2); ?></p>
                    </div>
                    
                    <div class="customer-info">
                        <h3>Customer Information</h3>
                        <p><strong>Name:</strong> <?php echo ($order['first_name'] || $order['last_name']) ? $order['first_name'] . ' ' . $order['last_name'] : ($order['username'] ?: 'Guest'); ?></p>
                        <p><strong>Email:</strong> <?php echo $order['email'] ?: 'N/A'; ?></p>
                        <p><strong>Phone:</strong> <?php echo $order['phone'] ?: 'N/A'; ?></p>
                        <p><strong>Address:</strong> <?php echo $order['address'] ?: 'N/A'; ?></p>
                        <p><strong>City:</strong> <?php echo $order['city'] ?: 'N/A'; ?></p>
                        <p><strong>Country:</strong> <?php echo $order['country'] ?: 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h3>Order Items</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $itemsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $item['product_name']; ?></td>
                                <td><?php echo $currency_symbol . number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo $currency_symbol . number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>