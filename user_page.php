<?php
function getAllImages() {
    global $connection;

    $query = "SELECT images.*, user_form.name, art_types.art_type_name, art_mediums.art_medium_name
        FROM images
        JOIN user_form ON user_form.id = images.user_id
        JOIN art_types ON art_types.art_type_id = images.art_type_id
        JOIN art_mediums ON art_mediums.art_medium_id = images.art_medium_id";

    $result = mysqli_query($connection, $query);

    $all_images = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $all_images[] = $row;
        }
        return $all_images;
    } else {
        return false;
    }
}

// Include configuration and common functions
include 'config.php';
include 'navbar3.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_name'])) {
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

// Retrieve the list of all available images from the database
$all_images = getAllImages();

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style5.css">
    <link rel="stylesheet" href="css/style6.css">
</head>
<body>
    <div>
        <h2>Art Gallery</h2>

        <!-- Display all available images -->
        <h3>All Available Artworks</h3>
        <div class="art-grid">
            <?php foreach ($all_images as $image): ?>
                <div class="art-item">
                    <?php echo '<img src="data:image/*;base64,' .($image['image_data']) . '" alt="Art Image">'; ?>
                    <p>Artist: <?php echo $image['name']; ?></p>
                    <p>Art Type: <?php echo $image['art_type_name']; ?></p>
                    <p>Art Medium: <?php echo $image['art_medium_name']; ?></p>
                    <p>Description: <?php echo $image['description']; ?></p>
                    <p>Selling Price: <?php echo $image['selling_price']; ?></p>
                    <form action="payment_form.php" method="post">
                        <input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>">
                        <input type="submit" name="buy" value="Buy">
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
