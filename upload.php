<?php
// Include configuration and common functions
include 'config.php';
include 'navbar2.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['artist_name'])) {
    header('location: login_form.php');
    exit();
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dgs';

$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the list of albums from the database
$art_types = [];
$sql = "SELECT art_type_id, art_type_name, created_at FROM art_types";
$result = mysqli_query($connection, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $art_types[] = $row;
    }
}
$art_mediums = [];
$sql = "SELECT art_medium_id, art_medium_name, created_at FROM art_mediums";
$result = mysqli_query($connection, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $art_mediums[] = $row;
    }
}

// Check if the form is submitted
if (isset($_POST['upload_image'])) {
    // Set user_id with the appropriate value (you need to retrieve it from your session or wherever it's stored)
    $user_id = $_SESSION['id'];
    if ($_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
        // Sanitize and validate file data
        $imageData = file_get_contents($_FILES['image_file']['tmp_name']);
        $base64Image = base64_encode($imageData);

        $sql = "INSERT INTO images (user_id, image_data, art_type_id, art_medium_id, description, selling_price) 
        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "ississ",
            $user_id,
            $base64Image,
            $_POST['art_type_id'],
            $_POST['art_medium_id'],
            $_POST['description'],
            $_POST['selling_price']
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "Art uploaded successfully";
        } else {
            die("Art upload failed: " . mysqli_error($connection));
        }

        mysqli_stmt_close($stmt);
    } else {
        die("Error in file upload: " . $_FILES['image_file']['error']);
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload and Share</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style5.css">
</head>

<body>
    <form method="POST" enctype="multipart/form-data" action="upload.php">
        <!-- Removed the unused input field for image_id -->
        <label for="image_file">Select Image to Upload:</label>
        <input type="file" name="image_file" id="image_file" required>

        <label for="art_type_id">Art Type:</label>
        <select name="art_type_id" id="art_type_id" required>
            <?php
            foreach ($art_types as $art_type) {
                echo "<option value='" . $art_type['art_type_id'] . "'>" . $art_type['art_type_name'] . "</option>";
            }
            ?>
        </select>

        <label for="art_medium_id">Art Medium:</label>
        <select name="art_medium_id" id="art_medium_id" required>
            <?php
            foreach ($art_mediums as $art_medium) {
                echo "<option value='" . $art_medium['art_medium_id'] . "'>" . $art_medium['art_medium_name'] . "</option>";
            }
            ?>
        </select>

        <label for="description">Art Product Description:</label>
        <input type="text" name="description" id="description" required>

        <label for="selling_price">Selling Price:</label>
        <input type="text" name="selling_price" id="selling_price" required>

        <button type="submit" name="upload_image">Upload Art</button>
    </form>
</body>

</html>
