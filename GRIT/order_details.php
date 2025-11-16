<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('user_orders.php');
}

$conn = getDBConnection();

// Fetch order details (only if it belongs to the current user)
$stmt = $conn->prepare("SELECT o.*, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM orders o LEFT JOIN user_profiles up ON o.user_id = up.user_id WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    redirect('user_orders.php');
}

$order = $orderResult->fetch_assoc();
$stmt->close();

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderItemsResult = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Order Details</h2>
        
        <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 30px;">
            <!-- Order Information -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                <h3>Order Information</h3>
                <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="order-status <?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', isset($order['payment_method']) ? $order['payment_method'] : 'cod')); ?></p>
            </div>
            
            <!-- Shipping Information -->
            <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                <h3>Shipping Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                <p><strong>Shipping Address:</strong><br>
                <?php 
                $address = '';
                if (!empty($order['address'])) $address .= $order['address'] . ', ';
                if (!empty($order['city'])) $address .= $order['city'] . ', ';
                if (!empty($order['country'])) $address .= $order['country'];
                echo nl2br(htmlspecialchars($address));
                ?></p>
            </div>
        </div>
        
        <!-- Order Items -->
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h3>Order Items</h3>
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $orderItemsResult->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <?php if ($item['image']): ?>
                                        <img src="assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 10px;">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <p><a href="user_orders.php" class="btn-secondary">← Back to Orders</a></p>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>