<?php
require_once 'config.php';

// Get all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

echo "<h1>Product Image Check</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image Filename</th><th>Image Exists</th><th>Preview</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td>" . $row["image"] . "</td>";
        
        // Check if image file exists
        if (!empty($row["image"])) {
            $imagePath = "assets/images/" . $row["image"];
            if (file_exists($imagePath)) {
                echo "<td style='color: green;'>YES</td>";
                echo "<td><img src='" . $imagePath . "' width='50'></td>";
            } else {
                echo "<td style='color: red;'>NO</td>";
                echo "<td>File not found</td>";
            }
        } else {
            echo "<td>No image</td>";
            echo "<td>No image</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No products found";
}
$conn->close();
?>