<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Handle adding new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if username or email already exists
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $insertSql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "Admin user created successfully!";
                // Clear form data
                $username = $email = '';
            } else {
                $error = "Error creating admin user: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Handle admin deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prevent deleting the main admin account (ID 1)
    if ($delete_id == 1) {
        $error = "Cannot delete the main admin account.";
    } else {
        $delete_sql = "DELETE FROM users WHERE id = ? AND role = 'admin'";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Admin user deleted successfully!";
            } else {
                $error = "Admin user not found or could not be deleted.";
            }
        } else {
            $error = "Error deleting admin user: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get all admin users
$sql = "SELECT id, username, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - GRIT Admin</title>
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
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="admins.php" class="active">Admins</a></li>
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
                <h1 class="page-title">Manage Admins</h1>
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

            <!-- Add Admin Form -->
            <div class="content-card">
                <h2>Add New Admin</h2>
                <form method="POST">
                    <input type="hidden" name="add_admin" value="1">
                    <div class="form-row">
                        <div class="form-col">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <small style="color: var(--secondary-color);">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-col">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </form>
            </div>

            <!-- Admin Users List -->
            <div class="content-card">
                <h2>Admin Users</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($admin = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $admin['id']; ?></td>
                                    <td><?php echo $admin['username']; ?></td>
                                    <td><?php echo $admin['email']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <?php if ($admin['id'] != 1): // Prevent deleting main admin ?>
                                            <a href="?delete_id=<?php echo $admin['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this admin user?')">Delete</a>
                                        <?php else: ?>
                                            <span>Main Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No admin users found</td>
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