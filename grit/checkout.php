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
    <title>Checkout - <?php echo $site_title; ?></title>
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

    <!-- Checkout Section -->
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Checkout</h1>
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <div class="content-card">
                        <h3>Billing Information</h3>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" class="form-control" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" id="postal_code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" class="form-control" required>
                                <option value="">Select Country</option>
                                <option value="Philippines" selected>Philippines</option>
                                <option value="USA">United States</option>
                                <option value="Canada">Canada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="content-card">
                        <h3>Payment Method</h3>
                        <div class="form-group">
                            <label>
                                <input type="radio" name="payment" value="cod" checked> 
                                Cash on Delivery
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="radio" name="payment" value="card"> 
                                Credit/Debit Card
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="radio" name="payment" value="gcash"> 
                                GCash
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-summary">
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
                    
                    <button class="btn btn-primary btn-block">Place Order</button>
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