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
    <title>Your Cart - <?php echo $site_title; ?></title>
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

    <!-- Cart Section -->
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Your Shopping Cart</h1>
            </div>
            
            <div class="cart-content">
                <div class="cart-items">
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="assets/images/black-hoodie.jpg" alt="Bacolod Underground Hoodie">
                        </div>
                        <div class="cart-item-details">
                            <h3>Bacolod Underground Hoodie</h3>
                            <p>Size: Large</p>
                        </div>
                        <div class="cart-item-quantity">
                            <label for="quantity">Quantity:</label>
                            <select id="quantity" class="form-control" style="width: auto; display: inline-block;">
                                <option>1</option>
                                <option selected>2</option>
                                <option>3</option>
                                <option>4</option>
                            </select>
                        </div>
                        <div class="cart-item-total">
                            <strong>₱1,598.00</strong>
                        </div>
                        <div class="cart-item-remove">
                            <button class="btn btn-outline">Remove</button>
                        </div>
                    </div>
                    
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="assets/images/leather-wallet.jpg" alt="Silay Leather Wallet">
                        </div>
                        <div class="cart-item-details">
                            <h3>Silay Leather Wallet</h3>
                            <p>Color: Black</p>
                        </div>
                        <div class="cart-item-quantity">
                            <label for="quantity2">Quantity:</label>
                            <select id="quantity2" class="form-control" style="width: auto; display: inline-block;">
                                <option selected>1</option>
                                <option>2</option>
                                <option>3</option>
                            </select>
                        </div>
                        <div class="cart-item-total">
                            <strong>₱599.00</strong>
                        </div>
                        <div class="cart-item-remove">
                            <button class="btn btn-outline">Remove</button>
                        </div>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₱2,197.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>₱99.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>₱187.00</span>
                        </div>
                        <div class="summary-row total">
                            <strong>Total</strong>
                            <strong>₱2,483.00</strong>
                        </div>
                    </div>
                    
                    <div class="summary-actions">
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                        <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                    </div>
                </div>
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