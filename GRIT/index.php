<?php
require_once 'config.php';

$conn = getDBConnection();

// Fetch products
$productsQuery = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
$productsResult = $conn->query($productsQuery);

// Fetch categories
$categoriesQuery = "SELECT * FROM categories";
$categoriesResult = $conn->query($categoriesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> | <?php echo SITE_TAGLINE; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile_fix.css">
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
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <h1><a href="index.php"><?php echo SITE_NAME; ?></a></h1>
                    <p><?php echo SITE_TAGLINE; ?></p>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search...">
                        <button>🔍</button>
                    </div>
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="profile.php">Profile</a>
                            <a href="logout.php">Logout</a>
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php">Admin Dashboard</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                            <a href="register.php">Register</a>
                        <?php endif; ?>
                    </div>
                    <div class="cart-icon">
                        <a href="cart.php">🛒 <span id="cart-count">0</span></a>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php 
                    // Store categories in an array to avoid consuming the result set
                    $categories = [];
                    while ($category = $categoriesResult->fetch_assoc()) {
                        $categories[] = $category;
                    }
                    foreach ($categories as $category): ?>
                        <li><a href="category.php?id=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                
                <!-- Mobile menu toggle -->
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>THE STRENGTH TO KEEP GOING</h2>
                <p>Premium quality apparel and accessories for those who push boundaries</p>
                <a href="#products" class="btn-primary">Shop Now</a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products" id="products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php 
                // Reset products result set pointer
                $productsResult->data_seek(0);
                while ($product = $productsResult->fetch_assoc()): ?>
                    <div class="product-card <?php echo $product['stock'] == 0 ? 'out-of-stock' : ''; ?>">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo $product['category_name']; ?></span>
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
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3>GRIT</h3>
                    <p><?php echo SITE_TAGLINE; ?></p>
                    <p>Premium quality apparel and accessories for those who push boundaries.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <p>Bacolod City, Philippines</p>
                    <p>Email: info@gritbacolod.com</p>
                    <p>Phone: (034) xxx-xxxx</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
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