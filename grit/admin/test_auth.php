<?php
require_once '../config.php';

echo "<h1>Authentication Test</h1>";

echo "<p>Session status: " . session_status() . "</p>";
echo "<p>Is logged in: " . (isLoggedIn() ? 'Yes' : 'No') . "</p>";
echo "<p>Is admin: " . (isAdmin() ? 'Yes' : 'No') . "</p>";

if (isset($_SESSION)) {
    echo "<h2>Session Data:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<h2>Database Test:</h2>";
$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Products count: " . $row['count'] . "</p>";
} else {
    echo "<p>Database error: " . $conn->error . "</p>";
}
?>