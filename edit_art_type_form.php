<?php
session_start();
include 'config.php';
include 'navbar.php';

if (!isset($_SESSION['edit_art_type']) || !isset($_SESSION['edit_id'])) {
    header('location: art_type.php');
    exit();
}

$edit_art_type = $_SESSION['edit_art_type'];
$edit_id = $_SESSION['edit_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Art Type</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/style8.css">
</head>
<body>
    <!-- Edit Art Type Form -->
<form method="post" action="art_type.php">
    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
    <label for="updated_art_type">Updated Art Type:</label>
    <input type="text" id="updated_art_type" name="updated_art_type" value="<?php echo $edit_art_type; ?>" required>
    <button type="submit" name="update">Update Art Type</button>
    <button formaction="art_type.php">Go back to art type</button>
</form>
</body>
</html>
