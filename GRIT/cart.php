<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Prevent admins from shopping in admin mode
if (isAdmin() && strpos($_SERVER['HTTP_REFERER'], '/admin/') !== false) {
    // Admin is trying to shop while in admin panel
    redirect('admin/dashboard.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        // Check if product is already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($cart_item = $result->fetch_assoc()) {
            // Update quantity
            $new_quantity = $cart_item['quantity'] + $quantity;
            $stmt2 = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt2->bind_param("ii", $new_quantity, $cart_item['id']);
            $stmt2->execute();
            $stmt2->close();
        } else {
            // Add new item to cart
            $stmt2 = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt2->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt2->execute();
            $stmt2->close();
        }
        
        $stmt->close();
    }
    
    redirect('cart.php');
}

// Handle removing items from cart
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    redirect('cart.php');
}

// Handle updating quantities
if (isset($_POST['update_cart'])) {
    $hasErrors = false;
    $errorMessages = [];
    
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        $cart_id = (int)$cart_id;
        $quantity = (int)$quantity;
        
        // Validate quantity
        if ($quantity < 0) {
            $hasErrors = true;
            $errorMessages[] = "Quantity cannot be negative.";
            continue;
        }
        
        if ($quantity > 0) {
            // Check product stock
            $stmt = $conn->prepare("SELECT p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($product = $result->fetch_assoc()) {
                if ($quantity > $product['stock']) {
                    $hasErrors = true;
                    $errorMessages[] = "Quantity exceeds available stock for some items.";
                } else {
                    $stmt2 = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    $stmt2->bind_param("iii", $quantity, $cart_id, $user_id);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
            $stmt->close();
        } else {
            // Remove item if quantity is 0
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    if ($hasErrors) {
        $error = implode(" ", $errorMessages);
    } else {
        $message = "Cart updated successfully!";
    }
    
    // Redirect to avoid resubmission
    redirect('cart.php');
}

// Fetch cart items with stock information
$stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate total
$total = 0;
$subtotal = 0;
while ($item = $cart_items->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
    $total += $item['price'] * $item['quantity'];
}
$cart_items->data_seek(0); // Reset pointer

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile_fix.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Your Shopping Cart</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($cart_items->num_rows > 0): ?>
            <form method="POST" action="">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $cart_items->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <?php if ($item['image']): ?>
                                            <img src="assets/images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 80px; height: 80px; object-fit: cover; margin-right: 15px;">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <?php if ($item['quantity'] > $item['stock']): ?>
                                                <br><span style="color: red; font-size: 0.9em;">Only <?php echo $item['stock']; ?> in stock</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" value="<?php echo min($item['quantity'], $item['stock']); ?>" min="0" max="<?php echo $item['stock']; ?>" style="width: 60px;">
                                </td>
                                <td>₱<?php echo number_format($item['price'] * min($item['quantity'], $item['stock']), 2); ?></td>
                                <td>
                                    <a href="?remove=<?php echo $item['cart_id']; ?>" class="btn-secondary" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button type="submit" name="update_cart" class="btn-secondary">Update Cart</button>
                    <div>
                        <h3>Subtotal: ₱<?php echo number_format($subtotal, 2); ?></h3>
                        <p style="font-size: 0.9em; color: #666;">Shipping and taxes calculated at checkout</p>
                        <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn-primary">Continue Shopping</a>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>