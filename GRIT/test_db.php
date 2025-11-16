<?php
require_once 'config.php';

// Test database connection
$conn = getDBConnection();

if ($conn) {
    echo "<h2>Database Connection Successful!</h2>";
    echo "<p>Connected to database: " . DB_NAME . "</p>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Number of users in database: " . $row['count'] . "</p>";
    }
    
    // Show PHP info
    echo "<h3>PHP Configuration</h3>";
    echo "<ul>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "<li>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
    echo "<li>Database Host: " . DB_HOST . "</li>";
    echo "<li>Database User: " . DB_USER . "</li>";
    echo "</ul>";
    
    $conn->close();
} else {
    echo "<h2>Database Connection Failed!</h2>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
}
?>