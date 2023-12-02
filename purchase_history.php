<?php
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

// Retrieve the logged-in user's name from the session
$user_name = $_SESSION['user_name'];

// Get purchase history for the logged-in user
$purchase_history = getPurchaseHistory($user_name);

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style5.css">
    <link rel="stylesheet" href="css/style6.css">
    <!-- Add any additional stylesheets if needed -->
</head>
<body>
    <div>
        <h2>Your Purchase History</h2>

        <!-- Display purchase history if available -->
        <?php if (isset($purchase_history) && !empty($purchase_history)): ?>
            <div class="art-grid">
                <?php foreach ($purchase_history as $purchase): ?>
                    <div class="art-item">
                        <?php echo '<img src="data:image/*;base64,' . ($purchase['image_data']) . '" alt="Art Image">'; ?>
                        <p>Artist: <?php echo $purchase['artist_name']; ?></p>
                        <p>Art Type: <?php echo $purchase['art_type_name']; ?></p>
                        <p>Art Medium: <?php echo $purchase['art_medium_name']; ?></p>
                        <p>Purchase Date: <?php echo $purchase['payment_date']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You currently have no purchase history.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
function getPurchaseHistory($user_name) {
    global $connection;
  
    $query = "SELECT payments.*, user_form.name AS artist_name, art_types.art_type_name, art_mediums.art_medium_name, images.image_data
    FROM payments
    JOIN images ON payments.image_id = images.image_id
    JOIN user_form ON user_form.id = payments.user_id
    JOIN art_types ON art_types.art_type_id = images.art_type_id
    JOIN art_mediums ON art_mediums.art_medium_id = images.art_medium_id
    WHERE user_form.name = ?";
  
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
  
    $result = mysqli_stmt_get_result($stmt);
  
    if (!$result) {
      die("Database query failed."); // Add better error handling based on your needs
    }
  
    $purchase_history = [];
  
    while ($row = mysqli_fetch_assoc($result)) {
      $purchase_history[] = $row;
    }
  
    return $purchase_history;
}
?>
