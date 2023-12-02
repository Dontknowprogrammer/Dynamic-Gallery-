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

// Retrieve the list of sold artworks by the current artist
$artist_name = $_SESSION['artist_name'];

$query = "SELECT images.*, art_types.art_type_name, art_mediums.art_medium_name, payments.payment_date
FROM images
JOIN user_form ON images.user_id = user_form.id
JOIN art_types ON art_types.art_type_id = images.art_type_id
JOIN art_mediums ON art_mediums.art_medium_id = images.art_medium_id
JOIN payments ON payments.image_id = images.image_id
WHERE user_form.name = ?";

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 's', $artist_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$sold_artworks = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sold_artworks[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($connection);
}


mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style6.css">
</head>
<body>
    <div>
        <h2>Welcome, <?php echo htmlspecialchars($artist_name); ?>!</h2>

        <!-- Display sold artworks by the artist -->
        <h3>Your Sold Artworks</h3>
        <div class="art-grid">
            <?php foreach ($sold_artworks as $artwork): ?>
                <div class="art-item">
                    <?php echo '<img src="data:image/*;base64,' . ($artwork['image_data']) . '" alt="Art Image">'; ?>
                    <p>Art Type: <?php echo htmlspecialchars($artwork['art_type_name']); ?></p>
                    <p>Art Medium: <?php echo htmlspecialchars($artwork['art_medium_name']); ?></p>
                    <p>Description: <?php echo htmlspecialchars($artwork['description']); ?></p>
                    <p>Selling Price: <?php echo htmlspecialchars($artwork['selling_price']); ?></p>
                    <p>Payment Date: <?php echo htmlspecialchars($artwork['payment_date']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
