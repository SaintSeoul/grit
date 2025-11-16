<?php
require_once '../config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

// Get user profile
$profile = getUserProfile($_SESSION['user_id']);

// Initialize variables
$first_name = $profile ? $profile['first_name'] : '';
$last_name = $profile ? $profile['last_name'] : '';
$phone = $profile ? $profile['phone'] : '';
$address = $profile ? $profile['address'] : '';
$city = $profile ? $profile['city'] : '';
$country = $profile ? $profile['country'] : 'Philippines';

// Get user orders
$user_id = $_SESSION['user_id'];
$ordersSql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$ordersStmt = $conn->prepare($ordersSql);

if ($ordersStmt) {
    $ordersStmt->bind_param("i", $user_id);
    $ordersStmt->execute();
    $ordersResult = $ordersStmt->get_result();
    $ordersStmt->close();
} else {
    // Handle the case where prepare fails
    $ordersResult = false;
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $country = sanitizeInput($_POST['country']);

    // Update or create user profile
    if (updateUserProfile($_SESSION['user_id'], $first_name, $last_name, $phone, $address, $city, $country)) {
        $message = 'Profile updated successfully!';
        // Refresh profile data
        $profile = getUserProfile($_SESSION['user_id']);
        $first_name = $profile['first_name'];
        $last_name = $profile['last_name'];
        $phone = $profile['phone'];
        $address = $profile['address'];
        $city = $profile['city'];
        $country = $profile['country'];
    } else {
        $error = 'Error updating profile. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GRIT</title>
    <link rel="icon" type="image/x-icon" href="../gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>GRIT</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php#products">Products</a></li>
                    <li><a href="profile.php" class="active">My Profile</a></li>
                    <li><a href="../index.php#about">About</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">My Profile</h1>
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
                <h2>Profile Information</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>">
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="content-card">
                <h2>My Orders</h2>
                <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $ordersResult->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo $currency_symbol . number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>You haven't placed any orders yet.</p>
                    <a href="../index.php#products" class="btn btn-primary">Start Shopping</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>