<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "Category deleted successfully!";
    } else {
        $error = "Error deleting category: " . $conn->error;
    }
    $stmt->close();
}

// Handle form submission for adding new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $description);
    
    if ($stmt->execute()) {
        $message = "Category added successfully!";
    } else {
        $error = "Error adding category: " . $conn->error;
    }
    $stmt->close();
}

// Get all categories
$sql = "SELECT * FROM categories ORDER BY name";
$result = $conn->query($sql);

// Get category statistics
$totalCategoriesSql = "SELECT COUNT(*) as total FROM categories";
$totalCategoriesResult = $conn->query($totalCategoriesSql);
$totalCategories = $totalCategoriesResult->fetch_assoc()['total'];

// Get categories with products
$categoriesWithProductsSql = "SELECT COUNT(DISTINCT c.id) as total FROM categories c JOIN products p ON c.id = p.category_id";
$categoriesWithProductsResult = $conn->query($categoriesWithProductsSql);
$categoriesWithProducts = $categoriesWithProductsResult->fetch_assoc()['total'];

// Get categories without products
$categoriesWithoutProducts = $totalCategories - $categoriesWithProducts;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - GRIT Admin</title>
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
                    <li><a href="products.php">Products</a></li>
                    <li><a href="categories.php" class="active">Categories</a></li>
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
                <h1 class="page-title">Manage Categories</h1>
            </div>

            <!-- Category Statistics -->
            <div class="content-card">
                <h2>Category Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $totalCategories; ?></h3>
                        <p>Total Categories</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $categoriesWithProducts; ?></h3>
                        <p>With Products</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $categoriesWithoutProducts; ?></h3>
                        <p>Without Products</p>
                    </div>
                </div>
            </div>

            <?php if (isset($message)): ?>
                <div class="content-card">
                    <div style="background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); padding: 15px; border-radius: 4px; border: 1px solid var(--success-color);">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="content-card">
                    <div style="background-color: rgba(244, 67, 54, 0.2); color: var(--danger-color); padding: 15px; border-radius: 4px; border: 1px solid var(--danger-color);">
                        <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Add Category Form -->
            <div class="content-card">
                <h2>Add New Category</h2>
                <form method="POST">
                    <input type="hidden" name="add_category" value="1">
                    <div class="form-row">
                        <div class="form-col">
                            <label for="name">Category Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>

            <!-- Categories List -->
            <div class="content-card">
                <h2>Existing Categories</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($category = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo $category['name']; ?></td>
                                    <td><?php echo $category['description'] ?: 'No description'; ?></td>
                                    <td class="action-buttons">
                                        <a href="?delete_id=<?php echo $category['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this category? This will not delete products in this category.')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No categories found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>