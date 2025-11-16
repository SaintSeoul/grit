<?php
require_once 'config.php';

// Get all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

echo "<h1>Current Products in Database</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Image</th><th>Stock</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td>₱" . number_format($row["price"], 2) . "</td>";
        echo "<td>" . $row["image"] . "</td>";
        echo "<td>" . $row["stock"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No products found in database";
}

$conn->close();
?>