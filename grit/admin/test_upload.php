<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

echo "<h1>Image Upload Test</h1>";

// Check PHP upload settings
echo "<h2>PHP Upload Settings</h2>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";

$uploadDir = dirname(__DIR__) . '/assets/images/';

echo "<h2>Directory Information</h2>";
echo "<p>Upload directory: " . $uploadDir . "</p>";

// Check if directory exists
if (is_dir($uploadDir)) {
    echo "<p style='color: green;'>✓ Directory exists</p>";
} else {
    echo "<p style='color: red;'>✗ Directory does not exist</p>";
}

// Check if directory is writable
if (is_writable($uploadDir)) {
    echo "<p style='color: green;'>✓ Directory is writable</p>";
} else {
    echo "<p style='color: red;'>✗ Directory is NOT writable</p>";
}

// Show directory permissions
echo "<p>Directory permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "</p>";

// List some files in directory
$files = array_slice(scandir($uploadDir), 2, 5); // Skip . and ..
echo "<p>Sample files in directory:</p>";
echo "<ul>";
foreach ($files as $file) {
    echo "<li>" . $file . "</li>";
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
        // Show detailed error message
        switch ($_FILES['test_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo "<p>Error: File exceeds upload_max_filesize directive in php.ini</p>";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "<p>Error: File exceeds MAX_FILE_SIZE directive in form</p>";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "<p>Error: File was only partially uploaded</p>";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "<p>Error: No file was uploaded</p>";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "<p>Error: Missing temporary folder</p>";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "<p>Error: Failed to write file to disk</p>";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "<p>Error: File upload stopped by extension</p>";
                break;
            default:
                echo "<p>Error: Unknown upload error</p>";
                break;
        }
    }
}

echo "<h2>Test Upload Form</h2>";
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

<p><a href="products.php">Back to Products</a></p>