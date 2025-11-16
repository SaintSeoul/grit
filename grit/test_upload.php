<?php
// Test image upload functionality
echo "<h1>Image Upload Test</h1>";

// Check if assets/images directory exists
$uploadDir = 'assets/images/';
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>Upload directory exists: " . $uploadDir . "</p>";
} else {
    echo "<p style='color: red;'>Upload directory does not exist: " . $uploadDir . "</p>";
}

// Check if directory is writable
if (is_writable($uploadDir)) {
    echo "<p style='color: green;'>Upload directory is writable</p>";
} else {
    echo "<p style='color: red;'>Upload directory is NOT writable</p>";
}

// Show directory permissions
echo "<p>Directory permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "</p>";

// List files in directory
$files = scandir($uploadDir);
echo "<p>Files in directory (" . count($files) . " items):</p>";
echo "<ul>";
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo "<li>" . $file . "</li>";
    }
}
echo "</ul>";

// Test file upload if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_image'])) {
    echo "<h2>Upload Test Results:</h2>";
    
    if ($_FILES['test_image']['error'] == UPLOAD_ERR_OK) {
        echo "<p style='color: green;'>File uploaded to server successfully</p>";
        
        $fileName = uniqid() . '_' . basename($_FILES['test_image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        echo "<p>Attempting to move file to: " . $uploadFile . "</p>";
        
        // Check if it's an actual image
        $check = getimagesize($_FILES['test_image']['tmp_name']);
        if ($check !== false) {
            echo "<p style='color: green;'>File is a valid image</p>";
            
            if (move_uploaded_file($_FILES['test_image']['tmp_name'], $uploadFile)) {
                echo "<p style='color: green;'>File moved successfully to assets/images/</p>";
                echo "<p>Uploaded file: <a href='" . $uploadFile . "'>" . $fileName . "</a></p>";
            } else {
                echo "<p style='color: red;'>Error moving file to assets/images/</p>";
                echo "<p>PHP error: " . print_r(error_get_last(), true) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>File is not a valid image</p>";
        }
    } else {
        echo "<p style='color: red;'>File upload error: " . $_FILES['test_image']['error'] . "</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <p>
        <label for="test_image">Select an image to test upload:</label><br>
        <input type="file" id="test_image" name="test_image" accept="image/*">
    </p>
    <p>
        <input type="submit" value="Test Upload" class="btn btn-primary">
    </p>
</form>