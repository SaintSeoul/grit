<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();

// Get user profile
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.username, u.email, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $country = sanitizeInput($_POST['country']);
    
    // Update user profile
    $stmt = $conn->prepare("UPDATE user_profiles SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, country = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $address, $city, $country, $user_id);
    
    if ($stmt->execute()) {
        $success = 'Profile updated successfully!';
        // Refresh user data
        $stmt->close();
        $stmt = $conn->prepare("SELECT u.username, u.email, up.first_name, up.last_name, up.phone, up.address, up.city, up.country FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = 'Failed to update profile. Please try again.';
    }
    
    $stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/jpeg" href="logo/gritfavicon.jpg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2>Your Profile</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country'] ?? 'Philippines'); ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Update Profile</button>
                </div>
            </form>
            
            <div class="profile-actions">
                <p><a href="user_orders.php" class="btn-secondary">View My Orders</a></p>
            </div>
            
            <p><a href="index.php">← Back to Home</a></p>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>