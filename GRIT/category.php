<?php
require_once 'config.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    redirect('index.php');
}

$conn = getDBConnection();

// Fetch category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$categoryResult = $stmt->get_result();

if ($categoryResult->num_rows === 0) {
    redirect('index.php');
}

$category = $categoryResult->fetch_assoc();
$stmt->close();

// Fetch products in this category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$productsResult = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (isset($category) && is_array($category) && isset($category['name'])) ? htmlspecialchars($category['name']) : 'Category'; ?> | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Out of stock tinting */
        .product-card.out-of-stock {
            opacity: 0.5;
            position: relative;
        }
        
        .product-card.out-of-stock::after {
            content: "OUT OF STOCK";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 3px;
            z-index: 2;
        }
        
        .product-card.out-of-stock .btn-cart {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .product-card.out-of-stock .btn-cart:hover {
            background: #ccc;
            color: inherit;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2><?php echo (isset($category) && is_array($category) && isset($category['name'])) ? htmlspecialchars($category['name']) : 'Category'; ?></h2>
        <p><?php echo (isset($category) && is_array($category) && isset($category['description'])) ? htmlspecialchars($category['description']) : ''; ?></p>
        
        <?php if ($productsResult->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $productsResult->fetch_assoc()): ?>
                    <div class="product-card <?php echo $product['stock'] == 0 ? 'out-of-stock' : ''; ?>">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><?php echo substr($product['description'], 0, 100); ?>...</p>
                            <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-secondary">View Details</a>
                                <?php if (isLoggedIn()): ?>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button class="btn-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                                    <?php else: ?>
                                        <button class="btn-cart" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn-cart">Add to Cart</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No products found in this category.</p>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Prevent adding out of stock items to cart
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.btn-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Check if button is disabled
                    if (this.hasAttribute('disabled')) {
                        showNotification('This item is out of stock.');
                        return;
                    }
                    
                    const productId = this.getAttribute('data-id');
                    if (productId) {
                        addToCart(productId);
                    }
                });
            });
        });
        
        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.backgroundColor = '#d4af37';
            notification.style.color = '#000';
            notification.style.padding = '15px 20px';
            notification.style.borderRadius = '3px';
            notification.style.zIndex = '9999';
            notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
            notification.style.fontWeight = 'bold';
            notification.id = 'notification';
            
            // Add to body
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    </script>
</body>
</html>