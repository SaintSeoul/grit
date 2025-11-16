<?php
require_once 'config.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('index.php');
}

$conn = getDBConnection();

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('index.php');
}

$product = $result->fetch_assoc();
$stmt->close();

// Handle adding to cart
$message = '';

if (isset($_POST['add_to_cart']) && isLoggedIn()) {
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0 && $quantity <= $product['stock']) {
        $user_id = $_SESSION['user_id'];
        
        // Check if product is already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();
        
        if ($cart_item = $cart_result->fetch_assoc()) {
            // Update quantity
            $new_quantity = $cart_item['quantity'] + $quantity;
            if ($new_quantity <= $product['stock']) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                $stmt->execute();
                $message = 'Product quantity updated in cart!';
            } else {
                $message = 'Requested quantity exceeds available stock.';
            }
        } else {
            // Add new item to cart
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt->execute();
            $message = 'Product added to cart!';
        }
        
        $stmt->close();
    } else {
        $message = 'Invalid quantity or insufficient stock.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile_fix.css">
    <style>
        /* Out of stock styling */
        .out-of-stock-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 18px;
            border-radius: 5px;
            z-index: 2;
        }
        
        .product-image-container {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-top: 30px;">
            <!-- Product Image -->
            <div style="flex: 1; min-width: 300px;" class="product-image-container">
                <?php if ($product['image']): ?>
                    <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 100%; max-width: 500px; height: auto;">
                <?php else: ?>
                    <div class="no-image" style="width: 100%; height: 400px; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                        No Image Available
                    </div>
                <?php endif; ?>
                
                <?php if ($product['stock'] == 0): ?>
                    <div class="out-of-stock-overlay">OUT OF STOCK</div>
                <?php endif; ?>
            </div>
            
            <!-- Product Details -->
            <div style="flex: 1; min-width: 300px;">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                
                <p><strong>Stock Available:</strong> 
                    <?php if ($product['stock'] == 0): ?>
                        <span style="color: red; font-weight: bold;">Out of Stock</span>
                    <?php else: ?>
                        <?php echo $product['stock']; ?> items
                    <?php endif; ?>
                </p>
                
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="" id="add-to-cart-form">
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" style="width: 80px;" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <button type="submit" name="add_to_cart" class="btn-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <button type="button" class="btn-cart" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <p><a href="login.php" class="btn-primary">Login to Add to Cart</a></p>
                <?php endif; ?>
                
                <p style="margin-top: 20px;"><a href="index.php">← Back to Products</a></p>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Handle add to cart button click
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartForm = document.getElementById('add-to-cart-form');
            if (addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const quantity = parseInt(document.getElementById('quantity').value);
                    const maxStock = parseInt(document.getElementById('quantity').max);
                    
                    if (quantity > maxStock) {
                        alert('Quantity cannot exceed available stock of ' + maxStock);
                        return;
                    }
                    
                    // Submit form normally for server-side processing
                    this.submit();
                });
            }
        });
    </script>
</body>
</html>