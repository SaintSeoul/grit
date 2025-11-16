<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle order update requests
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_update'])) {
    $order_id = (int)$_POST['order_id'];
    $update_message = sanitizeInput($_POST['update_message']);
    
    // Check if messages table exists, create it if not
    $checkTable = $conn->query("SHOW TABLES LIKE 'messages'");
    if ($checkTable->num_rows == 0) {
        $createTableSQL = "
        CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_id INT,
            message TEXT NOT NULL,
            sender_role ENUM('user', 'admin') NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
        )";
        if (!$conn->query($createTableSQL)) {
            $error = 'Failed to create messages table. Please contact administrator.';
        }
    }
    
    // Insert message if no error occurred
    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, order_id, message, sender_role, created_at) VALUES (?, ?, ?, 'user', NOW())");
        $stmt->bind_param("iis", $user_id, $order_id, $update_message);
        
        if ($stmt->execute()) {
            $message = 'Update request sent to admin successfully!';
        } else {
            $error = 'Failed to send update request. Please try again. Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    // Verify that the order belongs to the current user and is not already cancelled
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $currentStatus = $order['status'];
        
        // Only allow cancellation if order is not already cancelled, delivered, or shipped
        if ($currentStatus !== 'cancelled' && $currentStatus !== 'delivered' && $currentStatus !== 'shipped') {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Update order status to cancelled
                $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $updateStmt->bind_param("i", $order_id);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Restore stock for each item in the order
                $itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $itemsStmt->bind_param("i", $order_id);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                
                // Restore stock for each item
                while ($item = $itemsResult->fetch_assoc()) {
                    $updateStockStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                    $updateStockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                    $updateStockStmt->execute();
                    $updateStockStmt->close();
                }
                
                $itemsStmt->close();
                
                // Commit transaction
                $conn->commit();
                $message = 'Order cancelled successfully. Stock has been restored.';
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                $error = 'Failed to cancel order. Please try again.';
            }
        } else {
            $error = 'This order cannot be cancelled at this stage.';
        }
    } else {
        $error = 'Order not found or you do not have permission to cancel it.';
    }
    $stmt->close();
}

// Check for admin replies to user messages
$adminReplies = [];
$replyStmt = $conn->prepare("
    SELECT m.*, o.id as order_id 
    FROM messages m 
    LEFT JOIN orders o ON m.order_id = o.id 
    WHERE m.user_id = ? AND m.sender_role = 'admin' AND m.is_read = FALSE
    ORDER BY m.created_at DESC
");
$replyStmt->bind_param("i", $user_id);
$replyStmt->execute();
$replyResult = $replyStmt->get_result();

while ($reply = $replyResult->fetch_assoc()) {
    $adminReplies[] = $reply;
}
$replyStmt->close();

// Mark admin replies as read when user views them
if (!empty($adminReplies)) {
    $markReadStmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE user_id = ? AND sender_role = 'admin' AND is_read = FALSE");
    $markReadStmt->bind_param("i", $user_id);
    $markReadStmt->execute();
    $markReadStmt->close();
}

// Fetch user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>My Orders</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($adminReplies)): ?>
            <div class="alert alert-success">
                <h3>New Messages from Admin</h3>
                <?php foreach ($adminReplies as $reply): ?>
                    <div style="background: #e9f7ef; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #28a745;">
                        <p><strong>Admin Reply<?php if ($reply['order_id']): ?> for Order #<?php echo $reply['order_id']; ?><?php endif; ?>:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                        <p style="font-size: 0.8rem; color: #6c757d; margin: 5px 0 0;"><?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($ordersResult->num_rows > 0): ?>
            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?php echo $order['id']; ?></h3>
                        <span class="order-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                    
                    <div class="order-details">
                        <p><strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                        <p><strong>Total:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', isset($order['payment_method']) ? $order['payment_method'] : 'cod')); ?></p>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-secondary" onclick="toggleUpdateForm(<?php echo $order['id']; ?>)">Request Update</button>
                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-primary">View Details</a>
                        <?php 
                        $paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : 'cod';
                        if (in_array($paymentMethod, ['bank', 'credit']) && $order['status'] !== 'cancelled'): ?>
                            <a href="payment_verification.php?order_id=<?php echo $order['id']; ?>" class="btn-cart">Verify Payment</a>
                        <?php endif; ?>
                        <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered' && $order['status'] !== 'shipped'): ?>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this order? Stock will be restored.')">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="cancel_order" class="btn-cart">Cancel Order</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div id="update-form-<?php echo $order['id']; ?>" class="update-form" style="display: none;">
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <div class="form-group">
                                <label for="update_message_<?php echo $order['id']; ?>">Message to Admin:</label>
                                <textarea id="update_message_<?php echo $order['id']; ?>" name="update_message" required placeholder="Enter your request for update..."></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="request_update" class="btn-primary">Send Request</button>
                                <button type="button" class="btn-secondary" onclick="toggleUpdateForm(<?php echo $order['id']; ?>)">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't placed any orders yet.</p>
            <p><a href="index.php" class="btn-primary">Start Shopping</a></p>
        <?php endif; ?>
        
        <p><a href="index.php">← Back to Home</a></p>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        function toggleUpdateForm(orderId) {
            var form = document.getElementById('update-form-' + orderId);
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>