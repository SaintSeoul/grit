<?php
echo "<h1>PHP Upload Settings</h1>";

echo "<p>file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";

echo "<h2>_FILES superglobal:</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>POST data:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if (isset($_FILES['image'])) {
        echo "<h2>Image file data:</h2>";
        echo "<pre>";
        print_r($_FILES['image']);
        echo "</pre>";
        
        if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>File uploaded successfully to temp directory</p>";
        } else {
            echo "<p style='color: red;'>File upload error: " . $_FILES['image']['error'] . "</p>";
        }
    } else {
        echo "<p>No image file detected in upload</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <p>
        <label for="test_image">Select an image to test upload:</label><br>
        <input type="file" id="test_image" name="image" accept="image/*">
    </p>
    <p>
        <input type="submit" value="Test Upload" class="btn btn-primary">
    </p>
</form>