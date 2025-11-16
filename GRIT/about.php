<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>About <?php echo SITE_NAME; ?></h2>
        
        <div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h3>Our Story</h3>
            <p><?php echo SITE_NAME; ?> was born from a simple idea: to create apparel and accessories that embody the strength to keep going. In a world full of challenges and obstacles, we believe that true grit is what separates those who achieve their goals from those who give up.</p>
            
            <p>Founded in Bacolod City, Philippines, our brand draws inspiration from the resilience and determination of our community. We design products that not only look great but also remind you of your inner strength every time you wear them.</p>
            
            <h3 style="margin-top: 30px;">Our Mission</h3>
            <p>Our mission is to empower individuals to push beyond their limits and embrace challenges as opportunities for growth. We believe that with the right mindset and quality gear, anyone can achieve greatness.</p>
            
            <h3 style="margin-top: 30px;">Quality & Craftsmanship</h3>
            <p>Every product we create is designed with attention to detail and built to last. We use premium materials and work with skilled artisans to ensure that each piece meets our high standards of quality and durability.</p>
            
            <h3 style="margin-top: 30px;">Sustainability</h3>
            <p>We are committed to reducing our environmental impact through sustainable practices. From sourcing eco-friendly materials to minimizing waste in our production processes, we strive to make a positive difference.</p>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>