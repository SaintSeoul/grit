<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get message ID from URL
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($message_id <= 0) {
    redirect('messages.php');
}

// Get message details
$messageSql = "SELECT * FROM messages WHERE id = ?";
$messageStmt = $conn->prepare($messageSql);
$messageStmt->bind_param("i", $message_id);
$messageStmt->execute();
$messageResult = $messageStmt->get_result();

if ($messageResult->num_rows == 0) {
    redirect('messages.php');
}

$message = $messageResult->fetch_assoc();
$messageStmt->close();

// Mark message as read
if (!$message['is_read']) {
    $updateSql = "UPDATE messages SET is_read = 1 WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $message_id);
    $updateStmt->execute();
    $updateStmt->close();
    $message['is_read'] = 1;
}

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteSql = "DELETE FROM messages WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("i", $message_id);
    $deleteStmt->execute();
    $deleteStmt->close();
    redirect('messages.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Details - GRIT Admin</title>
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
                <h1 class="page-title">Message Details</h1>
                <div>
                    <a href="messages.php" class="btn btn-secondary">Back to Messages</a>
                    <a href="?id=<?php echo $message['id']; ?>&delete=1" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">Delete Message</a>
                </div>
            </div>

            <div class="content-card">
                <div class="message-details-grid">
                    <div class="message-info">
                        <h3>Message Information</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($message['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?></p>
                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <?php if ($message['is_read']): ?>
                                <span class="status delivered">Read</span>
                            <?php else: ?>
                                <span class="status pending">Unread</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h3>Message Content</h3>
                <div class="message-content">
                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>