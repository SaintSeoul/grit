<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Fetch dashboard statistics
// Total products
$productStmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$productStmt->execute();
$productResult = $productStmt->get_result();
$totalProducts = $productResult->fetch_assoc()['total'];
$productStmt->close();

// Total orders
$orderStmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$totalOrders = $orderResult->fetch_assoc()['total'];
$orderStmt->close();

// Total users (customers only)
$userStmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$userStmt->execute();
$userResult = $userStmt->get_result();
$totalCustomers = $userResult->fetch_assoc()['total'];
$userStmt->close();

// Total admins
$adminStmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$totalAdmins = $adminResult->fetch_assoc()['total'];
$adminStmt->close();

// Total revenue
$revenueStmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$revenueStmt->execute();
$revenueResult = $revenueStmt->get_result();
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;
$revenueStmt->close();

// Recent orders
$recentOrdersStmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recentOrdersStmt->execute();
$recentOrdersResult = $recentOrdersStmt->get_result();
$recentOrdersStmt->close();

// Handle adding new admin
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = sanitizeInput($_POST['admin_username']);
    $email = sanitizeInput($_POST['admin_email']);
    $password = $_POST['admin_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Create user profile entry
                $new_user_id = $stmt->insert_id;
                $profile_stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
                $profile_stmt->bind_param("i", $new_user_id);
                $profile_stmt->execute();
                $profile_stmt->close();
                
                $message = 'Admin user added successfully!';
                // Refresh admin count
                $adminStmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
                $adminStmt->execute();
                $adminResult = $adminStmt->get_result();
                $totalAdmins = $adminResult->fetch_assoc()['total'];
                $adminStmt->close();
            } else {
                $error = 'Failed to add admin user.';
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Prevent shopping in admin dashboard */
        .product-card {
            opacity: 0.3;
            pointer-events: none;
        }
        .product-actions {
            display: none;
        }
        .btn-cart, .btn-secondary {
            display: none;
        }
        /* Additional styles to make it clear this is admin mode */
        body {
            background-color: #f8f9fa;
        }
        .admin-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <h2 style="color: #d4af37; padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="../index.php">View Site</a></li>
                <li><a href="../register.php" target="_blank">Register New User</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Admin Dashboard</h2>
            <div class="admin-warning">
                <p>ADMIN MODE: Shopping is disabled in the admin panel. To shop items, please <a href="../index.php">visit the main site</a>.</p>
            </div>
            <div class="alert alert-info">
                <p>You are currently in admin mode. All shopping functionality is disabled to prevent accidental purchases.</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Products</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $totalProducts; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Orders</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $totalOrders; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Customers</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $totalCustomers; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Admins</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;"><?php echo $totalAdmins; ?></p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); text-align: center; grid-column: span 1;">
                    <h3 style="margin: 0 0 10px; color: #666;">Total Revenue</h3>
                    <p style="font-size: 2rem; margin: 0; color: #222;">₱<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>
            
            <!-- Add Admin Form -->
            <div class="product-form" style="margin-bottom: 30px;">
                <h3>Add New Admin</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="admin_username">Username *</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Email *</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Password *</label>
                        <input type="password" id="admin_password" name="admin_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_admin" class="btn-primary">Add Admin</button>
                        <a href="../register.php" target="_blank" class="btn-secondary">Register Customer</a>
                    </div>
                </form>
            </div>
            
            <!-- Recent Orders -->
            <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="btn-secondary">View All Orders</a>
                </div>
                
                <?php if ($recentOrdersResult->num_rows > 0): ?>
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recentOrdersResult->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span style="padding: 5px 10px; border-radius: 3px; background: 
                                            <?php 
                                            switch ($order['status']) {
                                                case 'pending': echo '#ffc107'; break;
                                                case 'processing': echo '#17a2b8'; break;
                                                case 'shipped': echo '#007bff'; break;
                                                case 'delivered': echo '#28a745'; break;
                                                case 'cancelled': echo '#dc3545'; break;
                                                default: echo '#6c757d';
                                            }
                                            ?>;
                                            color: white;">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                <h3>Quick Actions</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                    <a href="products.php" class="btn-primary" style="text-align: center; padding: 15px;">Manage Products</a>
                    <a href="categories.php" class="btn-primary" style="text-align: center; padding: 15px;">Manage Categories</a>
                    <a href="users.php" class="btn-primary" style="text-align: center; padding: 15px;">Manage Users</a>
                    <a href="orders.php" class="btn-primary" style="text-align: center; padding: 15px;">Manage Orders</a>
                    <a href="../register.php" target="_blank" class="btn-secondary" style="text-align: center; padding: 15px;">Register New User</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>