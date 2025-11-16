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
    <title>Contact Us - <?php echo $site_title; ?></title>
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

    <!-- Contact Section -->
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Contact Us</h1>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="content-card">
                        <h3>Get In Touch</h3>
                        <p>We'd love to hear from you! Reach out to us through any of the following channels:</p>
                        
                        <div class="contact-details">
                            <p><strong>Email:</strong> info@grit.com</p>
                            <p><strong>Phone:</strong> (034) 123-4567</p>
                            <p><strong>Address:</strong> Bacolod City, Negros Occidental</p>
                        </div>
                        
                        <div class="social-links">
                            <h4>Follow Us</h4>
                            <p>
                                <a href="#">Facebook</a> | 
                                <a href="#">Instagram</a> | 
                                <a href="#">Twitter</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <div class="content-card">
                        <h3>Send Us a Message</h3>
                        <form>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" class="form-control" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
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