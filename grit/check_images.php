<?php
require_once 'config.php';

$sql = "SELECT id, name, image FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Name: " . $row["name"]. " - Image: " . $row["image"]. "\n";
        // Check if image file exists
        if (!empty($row["image"])) {
            $imagePath = "assets/images/" . $row["image"];
            if (file_exists($imagePath)) {
                echo "  Image file exists: YES\n";
            } else {
                echo "  Image file exists: NO\n";
            }
        } else {
            echo "  No image file specified\n";
        }
        echo "\n";
    }
} else {
    echo "0 results";
}
$conn->close();
?>