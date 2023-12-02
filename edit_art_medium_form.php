<?php
session_start();
include 'config.php';
include 'navbar.php';


if (!isset($_SESSION['edit_art_medium']) || !isset($_SESSION['edit_medium_id'])) {
    header('location: art_medium.php');
    exit();
}

$edit_art_medium = $_SESSION['edit_art_medium'];
$edit_medium_id = $_SESSION['edit_medium_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Art Medium</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/style8.css">
</head>
<body>
    <!-- Edit Art Medium Form -->
<form method="post" action="art_medium.php">
    <input type="hidden" name="edit_medium_id" value="<?php echo $edit_medium_id; ?>">
    <label for="updated_art_medium">Updated Art Medium:</label>
    <input type="text" id="updated_art_medium" name="updated_art_medium" value="<?php echo $edit_art_medium; ?>" required>
    <button type="submit" name="update_medium">Update Art Medium</button>
    <button formaction="art_medium.php">Go back to edit art medium</button>
</form>
</body>
</html>
