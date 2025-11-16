<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

echo "<h1>Image Upload Test</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    echo "<h2>File Upload Information:</h2>";
    echo "<pre>";
    print_r($_FILES['image']);
    echo "</pre>";
    
    // Check if file was uploaded without errors
    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/grit/assets/images/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        echo "<p>Upload directory: " . $uploadDir . "</p>";
        echo "<p>Upload file path: " . $uploadFile . "</p>";
        echo "<p>Upload directory exists: " . (is_dir($uploadDir) ? 'Yes' : 'No') . "</p>";
        echo "<p>Upload directory writable: " . (is_writable($uploadDir) ? 'Yes' : 'No') . "</p>";
        
        // Check if image file is actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            echo "<p>File is an image - " . $check["mime"] . ".</p>";
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                echo "<p style='color: green;'>Image uploaded successfully!</p>";
                echo "<p>Uploaded file: <a href='/grit/assets/images/" . $fileName . "'>" . $fileName . "</a></p>";
            } else {
                echo "<p style='color: red;'>Error uploading image.</p>";
            }
        } else {
            echo "<p style='color: red;'>File is not an image.</p>";
        }
    } else {
        echo "<p style='color: red;'>Upload error: " . $_FILES['image']['error'] . "</p>";
    }
}

echo "
<form method='POST' enctype='multipart/form-data'>
    <label for='image'>Select Image:</label>
    <input type='file' id='image' name='image' required>
    <br><br>
    <button type='submit'>Upload Image</button>
</form>
";
?>