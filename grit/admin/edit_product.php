<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    redirect('products.php');
}

// Get categories for dropdown
$categorySql = "SELECT id, name FROM categories ORDER BY name";
$categoryResult = $conn->query($categorySql);

// Get product details
$productSql = "SELECT * FROM products WHERE id = ?";
$productStmt = $conn->prepare($productSql);
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();

if ($productResult->num_rows == 0) {
    redirect('products.php');
}

$product = $productResult->fetch_assoc();
error_log('Product data: ' . print_r($product, true));
$productStmt->close();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    
    // Handle file upload
    $image = $product['image']; // Keep existing image by default
    
    // Check if a file was actually selected for upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle file upload errors
        if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'File is too large. Maximum file size is 5MB.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'File was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = 'Missing a temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error = 'Failed to write file to disk.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error = 'File upload stopped by extension.';
                    break;
                default:
                    $error = 'Unknown upload error.';
                    break;
            }
        } else {
            // Check file size (max 5MB)
            if ($_FILES['image']['size'] > 5000000) {
                $error = 'File is too large. Maximum file size is 5MB.';
            } else {
                $uploadDir = dirname(__DIR__) . '/assets/images/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadFile = $uploadDir . $fileName;
                
                // Ensure upload directory exists
                if (!is_dir($uploadDir)) {
                    $error = 'Upload directory does not exist: ' . $uploadDir;
                } elseif (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable: ' . $uploadDir;
                } else {
                    // Check if image file is actual image
                    $check = getimagesize($_FILES['image']['tmp_name']);
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($_FILES['image']['tmp_name']);
                    
                    if ($check !== false && in_array($fileType, $allowedTypes)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                            $image = $fileName; // Update image to the new filename
                        } else {
                            $error = 'Error uploading image. Check directory permissions and PHP upload settings.';
                        }
                    } else {
                        $error = 'File is not a valid image. Only JPG, PNG, and GIF files are allowed.';
                    }
                }
            }
        }
    } 
    // If no new file was uploaded or there was an error, keep the existing image (this is already set above)

    if (empty($error)) {
        // Update product in database
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdisii', $name, $description, $price, $category_id, $image, $stock, $product_id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            // Refresh product data
            $productStmt = $conn->prepare($productSql);
            $productStmt->bind_param("i", $product_id);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();
            $productStmt->close();
        } else {
            $error = "Error updating product: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - GRIT Admin</title>
    <link rel="icon" type="image/x-icon" href="../gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>GRIT ADMIN</h2>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="products.php" class="active">Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="admins.php">Admins</a></li>
                    <li><a href="analytics.php">Analytics</a></li>
                    <li><a href="messages.php">Messages</a></li>
                </ul>
                <ul>
                    <li><a href="../index.php">Shop</a></li>
                    <li><a href="../index.php#products">View Products</a></li>
                    <li><a href="../cart.php">View Cart</a></li>
                </ul>
                <ul>
                    <li><a href="admin_profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Edit Product</h1>
                <a href="products.php" class="btn btn-secondary">Back to Products</a>
            </div>

            <?php if ($message): ?>
                <div class="content-card">
                    <div style="background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); padding: 15px; border-radius: 4px; border: 1px solid var(--success-color);">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="content-card">
                    <div style="background-color: rgba(244, 67, 54, 0.2); color: var(--danger-color); padding: 15px; border-radius: 4px; border: 1px solid var(--danger-color);">
                        <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-col">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="form-col">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categoryResult = $conn->query($categorySql);
                                if ($categoryResult->num_rows > 0): ?>
                                    <?php while($category = $categoryResult->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="price">Price (<?php echo $currency_symbol; ?>)</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                        </div>
                        
                        <div class="form-col">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo $product['stock']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" class="form-control">
                            <?php if ($product['image']): ?>
                                <div style="margin-top: 10px;">
                                    <p>Current image:</p>
                                    <img src="../assets/images/<?php echo $product['image']; ?>" alt="Product Image" style="max-width: 200px; height: auto;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>