<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle marking messages as read
if (isset($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Handle deleting messages
if (isset($_GET['delete'])) {
    $message_id = intval($_GET['delete']);
    $sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Get all messages
$messagesSql = "SELECT * FROM messages ORDER BY created_at DESC";
$messagesResult = $conn->query($messagesSql);

// Get unread messages count
$unreadMessagesSql = "SELECT COUNT(*) as total FROM messages WHERE is_read = 0";
$unreadMessagesResult = $conn->query($unreadMessagesSql);
$unreadMessagesCount = $unreadMessagesResult->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - GRIT Admin</title>
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
                    <li><a href="messages.php" class="active">Messages</a></li>
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
                <h1 class="page-title">Messages</h1>
            </div>

            <!-- Messages Statistics -->
            <div class="content-card">
                <h2>Message Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $messagesResult->num_rows; ?></h3>
                        <p>Total Messages</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $unreadMessagesCount; ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($messagesResult->num_rows > 0): ?>
                                <?php while($message = $messagesResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <?php if ($message['is_read']): ?>
                                            <span class="status delivered">Read</span>
                                        <?php else: ?>
                                            <span class="status pending">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="message_details.php?id=<?php echo $message['id']; ?>" class="btn btn-secondary">View</a>
                                        <?php if (!$message['is_read']): ?>
                                            <a href="?mark_read=<?php echo $message['id']; ?>" class="btn btn-primary">Mark Read</a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $message['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No messages found</td>
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