<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Test | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/mobile_fix.css">
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
                    <div class="cart-icon">
                        <a href="cart.php">🛒 <span id="cart-count">0</span></a>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">Category 1</a></li>
                    <li><a href="#">Category 2</a></li>
                    <li><a href="#">Category 3</a></li>
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

    <div class="container">
        <h2>Mobile Interface Test</h2>
        
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <h3>Mobile Menu Test</h3>
            <p>Resize your browser window to test the mobile menu functionality. The hamburger icon should appear on smaller screens, and clicking it should toggle the navigation menu.</p>
            
            <h3>Responsive Grid Test</h3>
            <p>The product grid below should adapt to different screen sizes:</p>
            
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image">
                        <div class="no-image">Product 1</div>
                    </div>
                    <div class="product-info">
                        <h3>Test Product 1</h3>
                        <p>This is a test product description.</p>
                        <div class="product-price">₱25.99</div>
                        <div class="product-actions">
                            <a href="#" class="btn-secondary">View Details</a>
                            <button class="btn-cart">Add to Cart</button>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <div class="no-image">Product 2</div>
                    </div>
                    <div class="product-info">
                        <h3>Test Product 2</h3>
                        <p>This is a test product description.</p>
                        <div class="product-price">₱39.99</div>
                        <div class="product-actions">
                            <a href="#" class="btn-secondary">View Details</a>
                            <button class="btn-cart">Add to Cart</button>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <div class="no-image">Product 3</div>
                    </div>
                    <div class="product-info">
                        <h3>Test Product 3</h3>
                        <p>This is a test product description.</p>
                        <div class="product-price">₱19.99</div>
                        <div class="product-actions">
                            <a href="#" class="btn-secondary">View Details</a>
                            <button class="btn-cart">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>