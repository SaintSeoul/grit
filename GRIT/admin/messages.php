<?php
require_once '../config.php';

// Check if admin is logged in
if (!isAdmin()) {
    redirect('../login.php');
}

$conn = getDBConnection();

// Handle reply submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $original_message_id = (int)$_POST['original_message_id'];
    $user_id = (int)$_POST['user_id'];
    $order_id = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
    $reply_content = sanitizeInput($_POST['reply_content']);
    
    if (empty($reply_content)) {
        $error = 'Please enter a reply message.';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert admin reply
            $stmt = $conn->prepare("INSERT INTO messages (user_id, order_id, message, sender_role, is_read, created_at) VALUES (?, ?, ?, 'admin', FALSE, NOW())");
            if ($order_id) {
                $stmt->bind_param("iiss", $user_id, $order_id, $reply_content);
            } else {
                $stmt->bind_param("iss", $user_id, $reply_content);
            }
            
            if ($stmt->execute()) {
                // Mark original message as read
                $updateStmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
                $updateStmt->bind_param("i", $original_message_id);
                $updateStmt->execute();
                $updateStmt->close();
                
                $message = 'Reply sent successfully!';
            } else {
                throw new Exception('Failed to send reply.');
            }
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// Mark messages as read when admin views them
if (isset($_GET['mark_as_read'])) {
    $message_id = (int)$_GET['mark_as_read'];
    $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to avoid resubmission
    header("Location: messages.php");
    exit();
}

// Fetch unread messages
$unreadMessagesQuery = "
    SELECT m.*, u.username, o.id as order_id 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    LEFT JOIN orders o ON m.order_id = o.id 
    WHERE m.is_read = FALSE AND m.sender_role = 'user'
    ORDER BY m.created_at DESC";
$unreadMessagesResult = $conn->query($unreadMessagesQuery);
if (!$unreadMessagesResult) {
    $unreadMessagesResult = new stdClass();
    $unreadMessagesResult->num_rows = 0;
}

// Fetch all messages with conversation threading
$allMessagesQuery = "
    SELECT m.*, u.username, o.id as order_id 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    LEFT JOIN orders o ON m.order_id = o.id 
    ORDER BY m.user_id, m.order_id, m.created_at ASC";
$allMessagesResult = $conn->query($allMessagesQuery);
if (!$allMessagesResult) {
    $allMessagesResult = new stdClass();
    $allMessagesResult->num_rows = 0;
}

// Group messages by user and order for conversation view
$groupedMessages = [];
if ($allMessagesResult && $allMessagesResult->num_rows > 0) {
    while ($msg = $allMessagesResult->fetch_assoc()) {
        $key = $msg['user_id'] . '_' . ($msg['order_id'] ?? 'no_order');
        if (!isset($groupedMessages[$key])) {
            $groupedMessages[$key] = [
                'user' => $msg['username'],
                'order_id' => $msg['order_id'],
                'messages' => []
            ];
        }
        $groupedMessages[$key]['messages'][] = $msg;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Admin Panel</title>
    <link rel="icon" type="image/jpeg" href="../logo/gritfavicon.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .conversation {
            background: white;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .conversation-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .message-thread {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-item.user {
            background: #f8f9fa;
        }
        
        .message-item.admin {
            background: #e9ecef;
        }
        
        .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .message-text {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .reply-form {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .reply-form textarea {
            width: 100%;
            min-height: 100px;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .message-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .message-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .message-content {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .message-actions {
            text-align: right;
        }
        
        .unread {
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <h2>Customer Messages</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($unreadMessagesResult && $unreadMessagesResult->num_rows > 0): ?>
                <h3>Unread Messages (<?php echo $unreadMessagesResult->num_rows; ?>)</h3>
                <div class="messages-container">
                    <?php while ($msg = $unreadMessagesResult->fetch_assoc()): ?>
                        <div class="message-card unread">
                            <div class="message-header">
                                <strong>From:</strong> <?php echo htmlspecialchars($msg['username']); ?> 
                                <?php if ($msg['order_id']): ?>
                                    | <strong>Order:</strong> #<?php echo $msg['order_id']; ?>
                                <?php endif; ?>
                                <span class="message-date"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            <div class="message-actions">
                                <a href="?mark_as_read=<?php echo $msg['id']; ?>" class="btn-primary">Mark as Read</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No unread messages.</p>
            <?php endif; ?>
            
            <h3>All Conversations</h3>
            <?php if (!empty($groupedMessages)): ?>
                <?php foreach ($groupedMessages as $conversation): ?>
                    <div class="conversation">
                        <div class="conversation-header">
                            Conversation with <?php echo htmlspecialchars($conversation['user']); ?>
                            <?php if ($conversation['order_id']): ?>
                                | Order #<?php echo $conversation['order_id']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="message-thread">
                            <?php foreach ($conversation['messages'] as $msg): ?>
                                <div class="message-item <?php echo $msg['sender_role']; ?>">
                                    <div class="message-sender">
                                        <?php echo $msg['sender_role'] === 'user' ? htmlspecialchars($conversation['user']) : 'Admin'; ?>
                                        <span class="message-time"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                    </div>
                                    <div class="message-text">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    </div>
                                    <?php if ($msg['sender_role'] === 'user' && $msg['is_read'] == 0): ?>
                                        <div class="reply-form">
                                            <form method="POST" action="">
                                                <input type="hidden" name="original_message_id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $msg['user_id']; ?>">
                                                <?php if ($msg['order_id']): ?>
                                                    <input type="hidden" name="order_id" value="<?php echo $msg['order_id']; ?>">
                                                <?php endif; ?>
                                                <div class="form-group">
                                                    <label for="reply_<?php echo $msg['id']; ?>">Reply:</label>
                                                    <textarea id="reply_<?php echo $msg['id']; ?>" name="reply_content" required placeholder="Enter your reply..."></textarea>
                                                </div>
                                                <button type="submit" name="reply_message" class="btn-primary">Send Reply</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No conversations yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>