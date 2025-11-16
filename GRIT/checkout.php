<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user profile for shipping information
$stmt = $conn->prepare("SELECT u.email, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_profile = $user_result->fetch_assoc();
$stmt->close();

// Fetch cart items with stock validation
$stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate total and check stock
$total = 0;
$hasStockIssues = false;
$stockIssues = [];

while ($item = $cart_items->fetch_assoc()) {
    if ($item['quantity'] > $item['stock']) {
        $hasStockIssues = true;
        $stockIssues[] = "Only {$item['stock']} {$item['name']} available in stock.";
    }
    $total += $item['price'] * $item['quantity'];
}
$cart_items->data_seek(0); // Reset pointer

// Check if payment_method column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
$hasPaymentMethodColumn = $checkColumn->num_rows > 0;

$error = '';
$success = '';
$payment_method = 'cod'; // Default to COD
$order_id = 0;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cart_items->num_rows > 0 && !$hasStockIssues) {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $country = sanitizeInput($_POST['country']);
    $payment_method = sanitizeInput($_POST['payment_method'] ?? 'cod');
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($country)) {
        $error = 'Please fill in all required fields.';
    } elseif (empty($payment_method)) {
        $error = 'Please select a payment method.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Create order with payment method
            $shipping_address = "$address, $city, $country";
            
            if ($hasPaymentMethodColumn) {
                // Column exists, use the enhanced query
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $user_id, $total, $shipping_address, $payment_method);
            } else {
                // Column doesn't exist, use the original query
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
                $stmt->bind_param("ids", $user_id, $total, $shipping_address);
            }
            
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            // Add order items and update stock
            $cart_items->data_seek(0); // Reset pointer
            while ($item = $cart_items->fetch_assoc()) {
                // Add order item
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                $stmt->close();
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt->execute();
                $stmt->close();
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            $success = 'Order placed successfully! Thank you for your purchase.';
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = 'Failed to place order. Please try again. Error: ' . $e->getMessage();
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
    <title>Checkout | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile_fix.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Checkout</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                <h3>Order Confirmation</h3>
                <p>Your order has been placed successfully!</p>
                <p>Order ID: <strong>#<?php echo $order_id ?? 'N/A'; ?></strong></p>
                <p>Total Amount: <strong>₱<?php echo number_format($total, 2); ?></strong></p>
                <?php if ($hasPaymentMethodColumn && in_array($payment_method, ['bank', 'credit'])): ?>
                    <p>Please verify your payment by clicking the button below.</p>
                    <a href="payment_verification.php?order_id=<?php echo $order_id; ?>" class="btn-primary" style="margin: 20px 0;">Verify Payment</a>
                <?php elseif ($hasPaymentMethodColumn && $payment_method === 'cod'): ?>
                    <p>You will pay upon delivery of your order.</p>
                <?php endif; ?>
                <p>We'll send you an email confirmation shortly.</p>
                <a href="index.php" class="btn-secondary" style="margin-top: 20px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php if ($cart_items->num_rows > 0): ?>
                <?php if ($hasStockIssues): ?>
                    <div class="alert alert-error">
                        <strong>Stock Issues:</strong>
                        <ul>
                            <?php foreach ($stockIssues as $issue): ?>
                                <li><?php echo $issue; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p>Please update your cart quantities or remove items with insufficient stock.</p>
                        <a href="cart.php" class="btn-primary">Go to Cart</a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 30px;">
                        <!-- Order Summary -->
                        <div style="flex: 1; min-width: 300px;">
                            <h3>Order Summary</h3>
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
                                    <?php while ($item = $cart_items->fetch_assoc()): ?>
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
                            
                            <div style="margin-top: 20px; text-align: right;">
                                <h3>Total: ₱<?php echo number_format($total, 2); ?></h3>
                            </div>
                        </div>
                        
                        <!-- Shipping & Payment Information -->
                        <div style="flex: 1; min-width: 300px;">
                            <h3>Shipping & Payment Information</h3>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user_profile['email'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($user_profile['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address *</label>
                                    <textarea id="address" name="address" required><?php echo htmlspecialchars($user_profile['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required value="<?php echo htmlspecialchars($user_profile['city'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Country *</label>
                                    <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($user_profile['country'] ?? 'Philippines'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Payment Method *</label>
                                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                                        <label style="display: flex; align-items: center;">
                                            <input type="radio" name="payment_method" value="cod" <?php echo $payment_method === 'cod' ? 'checked' : ''; ?> required>
                                            <span style="margin-left: 10px;">Cash on Delivery</span>
                                        </label>
                                        <label style="display: flex; align-items: center;">
                                            <input type="radio" name="payment_method" value="bank" <?php echo $payment_method === 'bank' ? 'checked' : ''; ?> required>
                                            <span style="margin-left: 10px;">Bank Transfer</span>
                                        </label>
                                        <label style="display: flex; align-items: center;">
                                            <input type="radio" name="payment_method" value="credit" <?php echo $payment_method === 'credit' ? 'checked' : ''; ?> required>
                                            <span style="margin-left: 10px;">Credit Card</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn-primary" style="width: 100%;">Place Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
                <a href="index.php" class="btn-primary">Continue Shopping</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>