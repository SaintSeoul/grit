<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

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

// Check if payment method requires verification (with fallback for older records)
$paymentMethod = isset($order['payment_method']) ? $order['payment_method'] : 'cod';
$requiresVerification = in_array($paymentMethod, ['bank', 'credit']);

// Handle payment verification form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requiresVerification) {
    $transaction_id = sanitizeInput($_POST['transaction_id']);
    $payment_date = sanitizeInput($_POST['payment_date']);
    $amount_paid = (float)$_POST['amount_paid'];
    
    if (empty($transaction_id) || empty($payment_date) || $amount_paid <= 0) {
        $error = 'Please fill in all required fields.';
    } elseif (abs($amount_paid - $order['total_amount']) > 0.01) { // Allow small differences due to rounding
        $error = 'Amount paid does not match the order total.';
    } else {
        // Update order with payment verification details
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'pending_verification', transaction_id = ?, payment_date = ?, amount_paid = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $transaction_id, $payment_date, $amount_paid, $order_id);
        
        if ($stmt->execute()) {
            $message = 'Payment details submitted successfully! Our team will verify your payment shortly.';
            // Refresh order data
            $stmt->close();
            $stmt = $conn->prepare("SELECT o.*, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM orders o LEFT JOIN user_profiles up ON o.user_id = up.user_id WHERE o.id = ? AND o.user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            $orderResult = $stmt->get_result();
            $order = $orderResult->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Failed to submit payment details. Please try again.';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Payment Verification</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
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
                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', isset($order['payment_method']) ? $order['payment_method'] : 'cod')); ?></p>
                <p><strong>Payment Status:</strong> 
                    <?php 
                    if (isset($order['payment_status'])) {
                        if ($order['payment_status'] === 'pending_verification') {
                            echo '<span style="color: #ffc107;">Pending Verification</span>';
                        } elseif ($order['payment_status'] === 'verified') {
                            echo '<span style="color: #28a745;">Verified</span>';
                        } else {
                            echo '<span style="color: #6c757d;">Not Submitted</span>';
                        }
                    } else {
                        echo '<span style="color: #6c757d;">Not Required</span>';
                    }
                    ?>
                </p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
            </div>
            
            <!-- Payment Verification Form -->
            <?php if ($requiresVerification): ?>
                <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h3>Payment Details</h3>
                    <?php if (isset($order['payment_status']) && ($order['payment_status'] === 'pending_verification' || $order['payment_status'] === 'verified')): ?>
                        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?></p>
                        <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($order['payment_date'] ?? 'N/A'); ?></p>
                        <p><strong>Amount Paid:</strong> ₱<?php echo number_format($order['amount_paid'] ?? 0, 2); ?></p>
                        <?php if (isset($order['payment_status']) && $order['payment_status'] === 'verified'): ?>
                            <div class="alert alert-success">Payment has been verified!</div>
                        <?php else: ?>
                            <div class="alert alert-info">Payment details submitted. Awaiting verification.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Please provide your payment details below to verify your payment.</p>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="transaction_id">Transaction ID *</label>
                                <input type="text" id="transaction_id" name="transaction_id" required placeholder="Enter transaction reference number">
                            </div>
                            
                            <div class="form-group">
                                <label for="payment_date">Payment Date *</label>
                                <input type="date" id="payment_date" name="payment_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="amount_paid">Amount Paid (₱) *</label>
                                <input type="number" id="amount_paid" name="amount_paid" step="0.01" min="0" required value="<?php echo number_format($order['total_amount'], 2, '.', ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn-primary" style="width: 100%;">Submit Payment Details</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="flex: 1; min-width: 300px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h3>Payment Information</h3>
                    <p>No payment verification required for this payment method.</p>
                    <?php if ((isset($order['payment_method']) ? $order['payment_method'] : 'cod') === 'cod'): ?>
                        <p>You will pay upon delivery of your order.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <p><a href="user_orders.php" class="btn-secondary">← Back to Orders</a></p>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>