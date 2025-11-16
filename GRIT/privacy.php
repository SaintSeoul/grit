<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Privacy Policy</h2>
        
        <div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            <p><strong>Last Updated:</strong> <?php echo date('F j, Y'); ?></p>
            
            <h3>Introduction</h3>
            <p><?php echo SITE_NAME; ?> ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
            
            <h3>Information We Collect</h3>
            <p>We may collect personal information that you voluntarily provide to us when you register on our site, place an order, subscribe to our newsletter, or otherwise contact us. This information may include:</p>
            <ul>
                <li>Name</li>
                <li>Email address</li>
                <li>Phone number</li>
                <li>Shipping address</li>
                <li>Payment information</li>
            </ul>
            
            <h3>How We Use Your Information</h3>
            <p>We may use the information we collect for various purposes, including:</p>
            <ul>
                <li>To process and fulfill your orders</li>
                <li>To send you information and updates about your orders</li>
                <li>To improve our website and customer service</li>
                <li>To send periodic emails about promotions, new products, and other information</li>
            </ul>
            
            <h3>Protection of Your Information</h3>
            <p>We implement a variety of security measures to maintain the safety of your personal information. All supplied sensitive information is transmitted via Secure Socket Layer (SSL) technology and then encrypted into our databases.</p>
            
            <h3>Cookies</h3>
            <p>We use cookies to enhance your experience on our site. Cookies help us remember your preferences and provide you with a more personalized experience.</p>
            
            <h3>Third-Party Disclosure</h3>
            <p>We do not sell, trade, or otherwise transfer your personally identifiable information to outside parties without your consent, except as necessary to fulfill your requests or as required by law.</p>
            
            <h3>Your Consent</h3>
            <p>By using our site, you consent to our privacy policy.</p>
            
            <h3>Changes to Our Privacy Policy</h3>
            <p>If we decide to change our privacy policy, we will post those changes on this page. Policy changes will apply only to information collected after the date of the change.</p>
            
            <h3>Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
            <p>Email: info@gritbacolod.com<br>
            Address: Bacolod City, Philippines</p>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>