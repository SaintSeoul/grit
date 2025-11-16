<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'grit');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
$site_title = "GRIT";
$site_description = "Underground Streetwear from Bacolod City";
$currency_symbol = "₱";
$currency_code = "PHP";

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Function to get user profile
function getUserProfile($user_id) {
    global $conn;
    $sql = "SELECT * FROM user_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// Function to update user profile
function updateUserProfile($user_id, $first_name, $last_name, $phone, $address, $city, $country) {
    global $conn;
    
    // Check if profile exists
    $checkSql = "SELECT id FROM user_profiles WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing profile
        $sql = "UPDATE user_profiles SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, country = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $address, $city, $country, $user_id);
    } else {
        // Create new profile
        $sql = "INSERT INTO user_profiles (user_id, first_name, last_name, phone, address, city, country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $phone, $address, $city, $country);
    }
    
    return $stmt->execute();
}

// Function to get cart item count
function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

// Function to register a new user
function registerUser($username, $email, $password) {
    global $conn;
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        // Get the new user's ID
        $user_id = $stmt->insert_id;
        
        // Create a profile for the user
        $profile_sql = "INSERT INTO user_profiles (user_id, country) VALUES (?, 'Philippines')";
        $profile_stmt = $conn->prepare($profile_sql);
        $profile_stmt->bind_param("i", $user_id);
        $profile_stmt->execute();
        $profile_stmt->close();
        
        return $user_id;
    }
    
    return false;
}
?>