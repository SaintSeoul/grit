<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Register user
            $user_id = registerUser($username, $email, $password);
            
            if ($user_id) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo $site_title; ?></title>
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
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="active">Register</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Register Section -->
    <section class="section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join GRIT to access exclusive streetwear collections</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <p><a href="index.php">Back to Home</a></p>
                    </div>
                </form>
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