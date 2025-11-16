<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

$conn = getDBConnection();

// Handle form submission for updating order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitizeInput($_POST['status']);
    
    // Check if the order status is being changed to cancelled
    // First, get the current status
    $currentStatusStmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $currentStatusStmt->bind_param("i", $order_id);
    $currentStatusStmt->execute();
    $currentStatusResult = $currentStatusStmt->get_result();
    $currentStatus = $currentStatusResult->fetch_assoc()['status'];
    $currentStatusStmt->close();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            // If order is being cancelled and wasn't already cancelled, restore stock
            if ($status === 'cancelled' && $currentStatus !== 'cancelled') {
                // Get order items to restore stock
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
            }
            
            // Commit transaction
            $conn->commit();
            // Redirect to avoid resubmission
            header("Location: order_details.php?id=" . $order_id . "&message=Order status updated successfully!");
            exit();
        } else {
            throw new Exception('Failed to update order status.');
        }
        $stmt->close();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $order_id = (int)$_POST['order_id'];
    $action = sanitizeInput($_POST['action']); // 'verify' or 'reject'
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        if ($action === 'verify') {
            // Verify payment and update order status to processing
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'verified', status = 'processing' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                // Commit transaction
                $conn->commit();
                header("Location: order_details.php?id=" . $order_id . "&message=Payment verified successfully! Order status updated to processing.");
                exit();
            } else {
                throw new Exception('Failed to verify payment.');
            }
            $stmt->close();
        } elseif ($action === 'reject') {
            // Reject payment and update order status to pending
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'rejected', status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                // Commit transaction
                $conn->commit();
                header("Location: order_details.php?id=" . $order_id . "&message=Payment rejected. Customer has been notified.");
                exit();
            } else {
                throw new Exception('Failed to reject payment.');
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, u.username, u.email, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN user_profiles up ON u.id = up.user_id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    redirect('orders.php');
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
    <title>Order Details | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Order Details</h2>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 30px;">
                <!-- Order Information -->
                <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h3>Order Information</h3>
                    <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span style="padding: 5px 10px; border-radius: 3px; background: 
                            <?php 
                            switch ($order['status']) {
                                case 'pending': echo '#ffc107'; break;
                                case 'processing': echo '#17a2b8'; break;
                                case 'shipped': echo '#007bff'; break;
                                case 'delivered': echo '#28a745'; break;
                                case 'cancelled': echo '#dc3545'; break;
                                default: echo '#6c757d';
                            }
                            ?>;
                            color: white;">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', isset($order['payment_method']) ? $order['payment_method'] : 'cod')); ?></p>
                    <?php if (isset($order['payment_method']) && in_array($order['payment_method'], ['bank', 'credit'])): ?>
                        <p><strong>Payment Status:</strong> 
                            <?php 
                            if (isset($order['payment_status'])) {
                                if ($order['payment_status'] === 'pending_verification') {
                                    echo '<span style="color: #ffc107;">Pending Verification</span>';
                                } elseif ($order['payment_status'] === 'verified') {
                                    echo '<span style="color: #28a745;">Verified</span>';
                                } elseif ($order['payment_status'] === 'rejected') {
                                    echo '<span style="color: #dc3545;">Rejected</span>';
                                } else {
                                    echo '<span style="color: #6c757d;">Not Submitted</span>';
                                }
                            } else {
                                echo '<span style="color: #6c757d;">Not Submitted</span>';
                            }
                            ?>
                        </p>
                        <?php if (isset($order['payment_status']) && ($order['payment_status'] === 'pending_verification' || $order['payment_status'] === 'verified' || $order['payment_status'] === 'rejected')): ?>
                            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?></p>
                            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($order['payment_date'] ?? 'N/A'); ?></p>
                            <p><strong>Amount Paid:</strong> ₱<?php echo number_format($order['amount_paid'] ?? 0, 2); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                    <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'shipped' && $order['status'] !== 'cancelled'): ?>
                        <form method="POST" action="" style="margin-top: 15px;" onsubmit="return confirm('Are you sure you want to cancel this order? Stock will be restored.')">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" style="padding: 5px; border-radius: 3px; border: 1px solid #ddd; margin-right: 10px;">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn-primary">Update Status</button>
                        </form>
                    <?php endif; ?>
                    <?php 
                    // Check if payment_method and payment_status exist
                    $paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : 'cod';
                    $paymentStatus = isset($order['payment_status']) ? $order['payment_status'] : null;
                    if (in_array($paymentMethod, ['bank', 'credit']) && $paymentStatus === 'pending_verification'): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            <h4>Payment Verification</h4>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="verify_payment" value="1" class="btn-primary" style="margin-right: 10px;">Verify Payment</button>
                                <input type="hidden" name="action" value="verify">
                            </form>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="verify_payment" value="1" class="btn-cart" onclick="return confirm('Are you sure you want to reject this payment?')">Reject Payment</button>
                                <input type="hidden" name="action" value="reject">
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Customer Information -->
                <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? $order['username'])); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                    <p><strong>Shipping Address:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
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
                                            <img src="../assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 10px;">
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
            
            <p><a href="orders.php" class="btn-secondary">← Back to Orders</a></p>
        </div>
    </div>
</body>
</html>