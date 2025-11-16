<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Handle form submission for updating order status
$message = '';
$error = '';

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
            
            $message = 'Order status updated successfully!';
        } else {
            throw new Exception('Failed to update order status.');
        }
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
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
                $message = 'Payment verified successfully! Order status updated to processing.';
            } else {
                throw new Exception('Failed to verify payment.');
            }
            $stmt->close();
        } elseif ($action === 'reject') {
            // Reject payment and update order status to pending
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'rejected', status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                $message = 'Payment rejected. Customer has been notified.';
            } else {
                throw new Exception('Failed to reject payment.');
            }
            $stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete order items first
        $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete order
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        $message = 'Order deleted successfully!';
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error = 'Failed to delete order.';
    }
}

// Fetch orders with user information
$ordersQuery = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";
$ordersResult = $conn->query($ordersQuery);

// Calculate revenue statistics
$totalRevenueStmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$totalRevenueStmt->execute();
$totalRevenue = $totalRevenueStmt->get_result()->fetch_assoc()['total'] ?? 0;
$totalRevenueStmt->close();

$pendingRevenueStmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status = 'pending'");
$pendingRevenueStmt->execute();
$pendingRevenue = $pendingRevenueStmt->get_result()->fetch_assoc()['total'] ?? 0;
$pendingRevenueStmt->close();

$completedRevenueStmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'");
$completedRevenueStmt->execute();
$completedRevenue = $completedRevenueStmt->get_result()->fetch_assoc()['total'] ?? 0;
$completedRevenueStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Manage Orders</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Revenue Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Revenue</h3>
                    <p style="font-size: 1.5rem; margin: 0; color: #222;">₱<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Pending Revenue</h3>
                    <p style="font-size: 1.5rem; margin: 0; color: #222;">₱<?php echo number_format($pendingRevenue, 2); ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Completed Revenue</h3>
                    <p style="font-size: 1.5rem; margin: 0; color: #222;">₱<?php echo number_format($completedRevenue, 2); ?></p>
                </div>
            </div>
            
            <!-- Orders List -->
            <h3 style="margin-top: 30px;">Existing Orders</h3>
            <?php if ($ordersResult->num_rows > 0): ?>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $ordersResult->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 5px; border-radius: 3px; border: 1px solid #ddd;">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <?php 
                                    // Check if payment_method key exists
                                    $paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : 'cod';
                                    echo ucfirst(str_replace('_', ' ', $paymentMethod)) . '<br>';
                                    if (in_array($paymentMethod, ['bank', 'credit'])) {
                                        // Check if payment_status key exists
                                        $paymentStatus = isset($order['payment_status']) ? $order['payment_status'] : null;
                                        if ($paymentStatus === 'pending_verification') {
                                            echo '<span style="color: #ffc107;">Pending Verification</span>';
                                        } elseif ($paymentStatus === 'verified') {
                                            echo '<span style="color: #28a745;">Verified</span>';
                                        } elseif ($paymentStatus === 'rejected') {
                                            echo '<span style="color: #dc3545;">Rejected</span>';
                                        } else {
                                            echo '<span style="color: #6c757d;">Not Submitted</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-secondary">View</a>
                                    <?php 
                                    // Check if payment_method key exists
                                    $paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : 'cod';
                                    $paymentStatus = isset($order['payment_status']) ? $order['payment_status'] : null;
                                    if (in_array($paymentMethod, ['bank', 'credit']) && $paymentStatus === 'pending_verification'): ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="verify_payment" value="1" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">Verify</button>
                                            <input type="hidden" name="action" value="verify">
                                        </form>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="verify_payment" value="1" class="btn-cart" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to reject this payment?')">Reject</button>
                                            <input type="hidden" name="action" value="reject">
                                        </form>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $order['id']; ?>" class="btn-cart" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>