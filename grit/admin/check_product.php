<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    echo "Invalid product ID";
    exit();
}

// Get product details
$productSql = "SELECT * FROM products WHERE id = ?";
$productStmt = $conn->prepare($productSql);
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();

if ($productResult->num_rows == 0) {
    echo "Product not found";
    exit();
}

$product = $productResult->fetch_assoc();
$productStmt->close();

echo "<h1>Product Details</h1>";
echo "<pre>";
print_r($product);
echo "</pre>";

if ($product['image']) {
    echo "<h2>Product Image</h2>";
    echo "<img src='/grit/assets/images/" . $product['image'] . "' alt='Product Image' style='max-width: 300px; height: auto;'>";
    echo "<p>Image file path: /grit/assets/images/" . $product['image'] . "</p>";
    
    // Check if file exists
    $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/grit/assets/images/' . $product['image'];
    echo "<p>File exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "</p>";
    if (file_exists($imagePath)) {
        echo "<p>File size: " . filesize($imagePath) . " bytes</p>";
    }
}
?>