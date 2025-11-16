<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get categories for dropdown
$categorySql = "SELECT id, name FROM categories ORDER BY name";
$categoryResult = $conn->query($categorySql);

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
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        // Check for upload errors
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
                            $image = $fileName;
                        } else {
                            $error = 'Error uploading image. Check directory permissions and PHP upload settings.';
                            // Log detailed error information
                            error_log('File upload error: ' . print_r($_FILES['image'], true));
                            error_log('Upload directory: ' . $uploadDir);
                            error_log('Destination file: ' . $uploadFile);
                            error_log('Directory writable: ' . (is_writable($uploadDir) ? 'Yes' : 'No'));
                        }
                    } else {
                        $error = 'File is not a valid image. Only JPG, PNG, and GIF files are allowed.';
                    }
                }
            }
        }
    }
    
    // If no errors, insert product into database
    if (empty($error)) {
        $sql = "INSERT INTO products (name, description, price, category_id, image, stock) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $description, $price, $category_id, $image, $stock);
        
        if ($stmt->execute()) {
            $message = "Product added successfully!";
            // Clear form data
            $name = $description = $price = $category_id = $stock = '';
        } else {
            $error = "Error adding product: " . $conn->error;
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
    <title>Add Product - GRIT Admin</title>
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
                <h1 class="page-title">Add New Product</h1>
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
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-col">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categoryResult = $conn->query($categorySql);
                                if ($categoryResult->num_rows > 0): ?>
                                    <?php while($category = $categoryResult->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
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
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?php echo isset($price) ? $price : ''; ?>" required>
                        </div>
                        
                        <div class="form-col">
                            <label for="stock">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo isset($stock) ? $stock : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>