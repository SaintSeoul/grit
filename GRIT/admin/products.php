<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Handle form submission for adding/editing products
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    // Validation
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0 || $stock < 0) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        // File upload handling
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            // Check if image file is actual image
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    $image = $fileName; // Store just the filename, not the full path
                } else {
                    $error = 'Sorry, there was an error uploading your file.';
                }
            } else {
                $error = 'File is not an image.';
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $error = 'Error uploading file. Please try again.';
        }
        
        if (empty($error)) {
            if ($product_id > 0) {
                // Update existing product
                if ($image) {
                    // Update with new image
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, image = ? WHERE id = ?");
                    $stmt->bind_param("sssiiisi", $name, $description, $price, $category_id, $stock, $image, $product_id);
                } else {
                    // Update without changing image
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock = ? WHERE id = ?");
                    $stmt->bind_param("sssiii", $name, $description, $price, $category_id, $stock, $product_id);
                }
                    
                if ($stmt->execute()) {
                    $message = 'Product updated successfully!';
                } else {
                    $error = 'Failed to update product.';
                }
                $stmt->close();
            } else {
                // Add new product
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdiss", $name, $description, $price, $category_id, $stock, $image);
                
                if ($stmt->execute()) {
                    $message = 'Product added successfully!';
                } else {
                    $error = 'Failed to add product.';
                }
                $stmt->close();
            }
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $message = 'Product deleted successfully!';
    } else {
        $error = 'Failed to delete product.';
    }
    $stmt->close();
}

// Fetch products with category names
$productsQuery = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
$productsResult = $conn->query($productsQuery);

// Fetch categories for dropdown
$categoriesQuery = "SELECT * FROM categories ORDER BY name ASC";
$categoriesResult = $conn->query($categoriesQuery);

// Product statistics
$productCountStmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$productCountStmt->execute();
$productCount = $productCountStmt->get_result()->fetch_assoc()['total'];
$productCountStmt->close();

$lowStockStmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE stock < 5 AND stock > 0");
$lowStockStmt->execute();
$lowStockCount = $lowStockStmt->get_result()->fetch_assoc()['total'];
$lowStockStmt->close();

$outOfStockStmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE stock = 0");
$outOfStockStmt->execute();
$outOfStockCount = $outOfStockStmt->get_result()->fetch_assoc()['total'];
$outOfStockStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Manage Products</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Product Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Products</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $productCount; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Low Stock (< 5)</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $lowStockCount; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Out of Stock</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $outOfStockCount; ?></p>
                </div>
            </div>
            
            <!-- Add/Edit Product Form -->
            <div class="product-form">
                <h3>Add New Product</h3>
                <form method="POST" action="" enctype="multipart/form-data" id="product-form">
                    <input type="hidden" name="product_id" id="product_id" value="">
                    
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₱) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php 
                            $categoriesResult->data_seek(0); // Reset pointer
                            while ($category = $categoriesResult->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity *</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small style="color: #666;">Leave blank to keep current image</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Save Product</button>
                        <button type="button" id="cancel-btn" class="btn-secondary" style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Products List -->
            <h3 style="margin-top: 30px;">Existing Products</h3>
            <?php if ($productsResult->num_rows > 0): ?>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $productsResult->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image']): ?>
                                        <img src="../assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php if ($product['stock'] == 0): ?>
                                        <span style="color: red; font-weight: bold;">Out of Stock</span>
                                    <?php elseif ($product['stock'] < 5): ?>
                                        <span style="color: orange; font-weight: bold;"><?php echo $product['stock']; ?> (Low)</span>
                                    <?php else: ?>
                                        <?php echo $product['stock']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['stock'] == 0): ?>
                                        <span style="background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em;">OUT OF STOCK</span>
                                    <?php elseif ($product['stock'] < 5): ?>
                                        <span style="background: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; font-size: 0.8em;">LOW STOCK</span>
                                    <?php else: ?>
                                        <span style="background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em;">IN STOCK</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="btn-secondary edit-product" 
                                       data-id="<?php echo $product['id']; ?>"
                                       data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                       data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                       data-price="<?php echo $product['price']; ?>"
                                       data-category="<?php echo $product['category_id']; ?>"
                                       data-stock="<?php echo $product['stock']; ?>">Edit</a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="btn-cart" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Edit product functionality
        document.querySelectorAll('.edit-product').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Populate form with product data
                document.getElementById('product_id').value = this.dataset.id;
                document.getElementById('name').value = this.dataset.name;
                document.getElementById('description').value = this.dataset.description;
                document.getElementById('price').value = this.dataset.price;
                document.getElementById('category_id').value = this.dataset.category;
                document.getElementById('stock').value = this.dataset.stock;
                
                // Change form heading
                document.querySelector('.product-form h3').textContent = 'Edit Product';
                
                // Show cancel button
                document.getElementById('cancel-btn').style.display = 'inline-block';
                
                // Scroll to form
                document.querySelector('.product-form').scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Cancel edit functionality
        document.getElementById('cancel-btn').addEventListener('click', function() {
            // Reset form
            document.getElementById('product_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('price').value = '';
            document.getElementById('category_id').value = '';
            document.getElementById('stock').value = '';
            document.getElementById('image').value = '';
            
            // Reset form heading
            document.querySelector('.product-form h3').textContent = 'Add New Product';
            
            // Hide cancel button
            this.style.display = 'none';
        });
        
        // Form validation
        document.getElementById('product-form').addEventListener('submit', function(e) {
            const price = document.getElementById('price').value;
            const stock = document.getElementById('stock').value;
            
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than zero.');
                return;
            }
            
            if (stock < 0) {
                e.preventDefault();
                alert('Stock quantity cannot be negative.');
                return;
            }
        });
    </script>
</body>
</html>