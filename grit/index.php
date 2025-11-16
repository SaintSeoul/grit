<?php
require_once 'config.php';

// Get cart item count
$cart_count = getCartItemCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Underground Streetwear from Bacolod City</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="gritfavicon.jpg" type="image/jpeg">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>GRIT</h1>
                </div>
                
                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle">☰</button>
                
                <!-- Main Navigation -->
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Shop</a></li>
                        <li><a href="#">Collections</a></li>
                        <li><a href="contact_process.php">Contact</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="header-actions">
                        <a href="cart.php" class="btn btn-outline">
                            Cart <?php if ($cart_count > 0): ?>(<?php echo $cart_count; ?>)<?php endif; ?>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Bacolod's Underground Streetwear</h2>
                <p>Premium street fashion inspired by the vibrant culture of Negros Occidental</p>
                <a href="#" class="btn btn-primary">Shop Collection</a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <!-- Product 1 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/black-hoodie.jpg" alt="Bacolod Underground Hoodie">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Bacolod Underground Hoodie</h3>
                        <p class="product-price">₱799.00</p>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
                
                <!-- Product 2 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/denim-jacket.jpg" alt="Negros Street Denim">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Negros Street Denim</h3>
                        <p class="product-price">₱1,299.00</p>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
                
                <!-- Product 3 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/leather-wallet.jpg" alt="Silay Leather Wallet">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Silay Leather Wallet</h3>
                        <p class="product-price">₱599.00</p>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
                
                <!-- Product 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/sneakers.jpg" alt="Bacolod Canvas Sneakers">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Bacolod Canvas Sneakers</h3>
                        <p class="product-price">₱1,499.00</p>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title">About GRIT</h2>
                <p>GRIT represents the raw energy and creativity of Bacolod's underground scene. Our streetwear collection draws inspiration from the city's vibrant culture, combining urban aesthetics with premium quality materials.</p>
                <p>Each piece tells a story of resilience, passion, and the unbreakable spirit of Negros Occidental's youth.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>GRIT</h3>
                    <p>Underground Streetwear from Bacolod City</p>
                    <div class="social-links">
                        <a href="#">Facebook</a>
                        <a href="#">Instagram</a>
                        <a href="#">Twitter</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Shop</a></li>
                        <li><a href="contact_process.php">Contact</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p>Email: info@grit.com</p>
                    <p>Phone: (034) 123-4567</p>
                    <p>Address: Bacolod City, Negros Occidental</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 GRIT Streetwear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            
            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                });
                
                // Close menu when clicking on a link
                const navLinks = mainNav.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mainNav.classList.remove('active');
                    });
                });
            }
        });
    </script>
</body>
</html>