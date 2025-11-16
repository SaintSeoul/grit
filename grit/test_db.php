<?php
require_once 'config.php';

echo "<h1>Database Connection Test</h1>";

// Check connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Connected successfully to database: " . DB_NAME . "</p>";
    
    // Test query
    $sql = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Found " . $row['count'] . " users in the database.</p>";
    } else {
        echo "<p style='color: red;'>Error executing query: " . $conn->error . "</p>";
    }
}

echo "<h2>Session Status</h2>";
echo "<p>Session status: " . session_status() . "</p>";

echo "<h2>Directory Permissions</h2>";
echo "<p>Current directory: " . getcwd() . "</p>";
echo "<p>Can read config.php: " . (is_readable('config.php') ? 'Yes' : 'No') . "</p>";
?>