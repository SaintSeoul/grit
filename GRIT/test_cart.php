<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch cart count
$cartStmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$cartStmt->bind_param("i", $user_id);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartCount = $cartResult->fetch_assoc()['total'] ?? 0;
$cartStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Test | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Cart Functionality Test</h2>
        
        <div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            <h3>Current Cart Status</h3>
            <p>Items in your cart: <strong><?php echo $cartCount; ?></strong></p>
            
            <h3>Test Functions</h3>
            <p>Use the buttons below to test cart functionality:</p>
            
            <div style="margin: 20px 0;">
                <button id="test-add" class="btn-primary">Add Test Item to Cart</button>
                <button id="test-update" class="btn-secondary" style="margin-left: 10px;">Update Cart Count</button>
            </div>
            
            <div id="result" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 3px; display: none;"></div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        document.getElementById('test-add').addEventListener('click', function() {
            // This would normally add a real product
            // For testing, we'll just show a notification
            showNotification('Test item would be added to cart');
            updateCartCount();
        });
        
        document.getElementById('test-update').addEventListener('click', function() {
            updateCartCount();
            showNotification('Cart count updated');
        });
        
        function showNotification(message) {
            const result = document.getElementById('result');
            result.textContent = message;
            result.style.display = 'block';
            
            setTimeout(() => {
                result.style.display = 'none';
            }, 3000);
        }
        
        function updateCartCount() {
            fetch('header.php')
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const cartCountElement = doc.getElementById('cart-count');
                if (cartCountElement) {
                    const cartCount = cartCountElement.textContent;
                    const headerCartCount = document.getElementById('cart-count');
                    if (headerCartCount) {
                        headerCartCount.textContent = cartCount;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
        }
    </script>
</body>
</html>