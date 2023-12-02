<!-- edit_art.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Art</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/style8.css">
</head>

<body>
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
    ?>

    <h2>Edit Art</h2>

    <?php
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'dgs';

    $connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_art"])) {
        $image_id = isset($_POST["image_id"]) ? $_POST["image_id"] : null;
        $new_art_type_id = isset($_POST["new_art_type_id"]) ? $_POST["new_art_type_id"] : null;
        $new_art_medium_id = isset($_POST["new_art_medium_id"]) ? $_POST["new_art_medium_id"] : null;
        $new_description = isset($_POST["new_description"]) ? $_POST["new_description"] : null;
        $new_selling_price = isset($_POST["new_selling_price"]) ? $_POST["new_selling_price"] : null;

        // Update the art information in the database
        $update_sql = "UPDATE images SET art_type_id=?, art_medium_id=?, description=?, selling_price=? WHERE image_id=?";
        $update_stmt = mysqli_prepare($connection, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "iissi", $new_art_type_id, $new_art_medium_id, $new_description, $new_selling_price, $image_id);

        if (mysqli_stmt_execute($update_stmt)) {
            echo "<p>Art information updated successfully.</p>";
        } else {
            echo "<p>Error updating art information: " . mysqli_error($connection) . "</p>";
        }

        mysqli_stmt_close($update_stmt);
    }

    // Retrieve the current art information if image_id is set
    if (isset($_POST["image_id"])) {
        $image_id = $_POST["image_id"];
        $select_sql = "SELECT * FROM images WHERE image_id=?";
        $select_stmt = mysqli_prepare($connection, $select_sql);
        mysqli_stmt_bind_param($select_stmt, "i", $image_id);
        mysqli_stmt_execute($select_stmt);
        $result = mysqli_stmt_get_result($select_stmt);
        $art = mysqli_fetch_assoc($result);
        mysqli_stmt_close($select_stmt);
    }

    // Function to get all art types
    function getAllArtTypes($connection)
    {
        $typeSql = "SELECT * FROM art_types";
        $typeResult = mysqli_query($connection, $typeSql);
        $types = mysqli_fetch_all($typeResult, MYSQLI_ASSOC);
        return $types;
    }

    // Function to get all art mediums
    function getAllArtMediums($connection)
    {
        $mediumSql = "SELECT * FROM art_mediums";
        $mediumResult = mysqli_query($connection, $mediumSql);
        $mediums = mysqli_fetch_all($mediumResult, MYSQLI_ASSOC);
        return $mediums;
    }
    ?>

    <form method="POST" action="edit_art.php">
        <input type="hidden" name="image_id" value="<?php echo $art['image_id']; ?>">

        <label for="new_art_type_id">Art Type:</label>
        <select name="new_art_type_id" id="new_art_type_id">
            <?php
            $allArtTypes = getAllArtTypes($connection);
            foreach ($allArtTypes as $type) {
                $selected = (isset($art['art_type_id']) && $art['art_type_id'] == $type['art_type_id']) ? 'selected' : '';
                echo "<option value='{$type['art_type_id']}' $selected>{$type['art_type_name']}</option>";
            }
            ?>
        </select><br>

        <label for="new_art_medium_id">Art Medium:</label>
        <select name="new_art_medium_id" id="new_art_medium_id">
            <?php
            $allArtMediums = getAllArtMediums($connection);
            foreach ($allArtMediums as $medium) {
                $selected = (isset($art['art_medium_id']) && $art['art_medium_id'] == $medium['art_medium_id']) ? 'selected' : '';
                echo "<option value='{$medium['art_medium_id']}' $selected>{$medium['art_medium_name']}</option>";
            }
            ?>
        </select><br>

        <label for="new_description">Description:</label>
        <input type="text" name="new_description" id="new_description" value="<?php echo isset($art['description']) ? $art['description'] : ''; ?>"><br>

        <label for="new_selling_price">Selling Price:</label>
        <input type="text" name="new_selling_price" id="new_selling_price" value="<?php echo isset($art['selling_price']) ? $art['selling_price'] : ''; ?>"><br>

        <button type="submit" name="update_art">Update</button>

        <button formaction="manage_art.php">Go back to manage art</button>
    </form>
   
    <?php
    mysqli_close($connection);
    ?>
</body>

</html>
