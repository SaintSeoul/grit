<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Handle form submission for adding/editing categories
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    // Validation
    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        if ($category_id > 0) {
            // Update existing category
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $category_id);
            
            if ($stmt->execute()) {
                $message = 'Category updated successfully!';
            } else {
                $error = 'Failed to update category.';
            }
            $stmt->close();
        } else {
            // Add new category
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            
            if ($stmt->execute()) {
                $message = 'Category added successfully!';
            } else {
                $error = 'Failed to add category.';
            }
            $stmt->close();
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Check if category has products
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        $error = 'Cannot delete category with existing products. Please reassign products first.';
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            $message = 'Category deleted successfully!';
        } else {
            $error = 'Failed to delete category.';
        }
        $stmt->close();
    }
}

// Fetch categories
$categoriesQuery = "SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.created_at DESC";
$categoriesResult = $conn->query($categoriesQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Manage Categories</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add/Edit Category Form -->
            <div class="product-form">
                <h3>Add New Category</h3>
                <form method="POST" action="">
                    <input type="hidden" name="category_id" id="category_id" value="">
                    
                    <div class="form-group">
                        <label for="name">Category Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Save Category</button>
                        <button type="button" id="cancel-btn" class="btn-secondary" style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Categories List -->
            <h3 style="margin-top: 30px;">Existing Categories</h3>
            <?php if ($categoriesResult->num_rows > 0): ?>
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td><?php echo $category['product_count']; ?></td>
                                <td class="action-buttons">
                                    <a href="#" class="btn-secondary edit-category" 
                                       data-id="<?php echo $category['id']; ?>"
                                       data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                       data-description="<?php echo htmlspecialchars($category['description']); ?>">Edit</a>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <a href="?delete=<?php echo $category['id']; ?>" class="btn-cart" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                    <?php else: ?>
                                        <button class="btn-cart" disabled title="Category has products, cannot delete">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No categories found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Edit category functionality
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Populate form with category data
                document.getElementById('category_id').value = this.dataset.id;
                document.getElementById('name').value = this.dataset.name;
                document.getElementById('description').value = this.dataset.description;
                
                // Change form heading
                document.querySelector('.product-form h3').textContent = 'Edit Category';
                
                // Show cancel button
                document.getElementById('cancel-btn').style.display = 'inline-block';
                
                // Scroll to form
                document.querySelector('.product-form').scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Cancel edit functionality
        document.getElementById('cancel-btn').addEventListener('click', function() {
            // Reset form
            document.getElementById('category_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
            
            // Reset form heading
            document.querySelector('.product-form h3').textContent = 'Add New Category';
            
            // Hide cancel button
            this.style.display = 'none';
        });
    </script>
</body>
</html>