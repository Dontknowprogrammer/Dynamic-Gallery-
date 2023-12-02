<?php
// Include configuration and common functions
include 'config.php';
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

// Handle art deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_delete"])) {
    $image_id = isset($_POST["image_id"]) ? $_POST["image_id"] : null;

    // Delete the art from the database
    $delete_sql = "DELETE FROM images WHERE image_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $image_id);

    if (mysqli_stmt_execute($delete_stmt)) {
        echo "<p>Art deleted successfully.</p>";
    } else {
        echo "<p>Error deleting art: " . mysqli_error($connection) . "</p>";
    }

    mysqli_stmt_close($delete_stmt);
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Art</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/style9.css">
</head>

<body>
    <h2>Delete Art</h2>

    <p>Are you sure you want to delete this art?</p>

    <form method="POST" action="">
        <input type="hidden" name="image_id" value="<?php echo isset($_POST['image_id']) ? $_POST['image_id'] : ''; ?>">
        <button type="submit" name="confirm_delete">Confirm Delete</button>
        <a href="manage_art.php"><button type="button">Cancel</button></a>
        <a href="manage_art.php"><button type="button">Go back to manage art</button></a>
    </form>
</body>

</html>
