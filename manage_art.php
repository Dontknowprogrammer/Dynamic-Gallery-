<?php
session_start();
include 'config.php';
include 'navbar2.php';

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

// Database connection
$connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve the list of uploaded art by the artist (user_id)
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM images WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$uploaded_art = [];
while ($row = $result->fetch_assoc()) {
    $row['art_type_name'] = getArtTypeById($connection, $row['art_type_id']);
    $row['art_medium_name'] = getArtMediumById($connection, $row['art_medium_id']);
    $uploaded_art[] = $row;
}

$stmt->close();
// Close the connection here if you're not performing any more database operations

function getArtTypeById($connection, $artTypeId)
{
    $typeSql = "SELECT art_type_name FROM art_types WHERE art_type_id = ?";
    $typeStmt = $connection->prepare($typeSql);
    $typeStmt->bind_param("i", $artTypeId);
    $typeStmt->execute();
    $typeResult = $typeStmt->get_result();
    $typeRow = $typeResult->fetch_assoc();
    $typeStmt->close();

    return $typeRow['art_type_name'];
}

function getArtMediumById($connection, $artMediumId)
{
    $mediumSql = "SELECT art_medium_name FROM art_mediums WHERE art_medium_id = ?";
    $mediumStmt = $connection->prepare($mediumSql);
    $mediumStmt->bind_param("i", $artMediumId);
    $mediumStmt->execute();
    $mediumResult = $mediumStmt->get_result();
    $mediumRow = $mediumResult->fetch_assoc();
    $mediumStmt->close();

    return $mediumRow['art_medium_name'];
}

// Close the connection here if you're not performing any more database operations
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Art</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
</head>

<body>
    <h2>Art Uploaded by <?php echo $_SESSION['artist_name']; ?></h2>

    <table border="1">
        <tr>
            <th>Image</th>
            <th>Art Type</th>
            <th>Art Medium</th>
            <th>Description</th>
            <th>Selling Price</th>
            <th>Action</th>
        </tr>
        <?php foreach ($uploaded_art as $art) : ?>
            <tr>
                <td><img src="data:image/jpeg;base64,<?php echo ($art['image_data']); ?>" height="50" width="50" alt="Art"></td>
                <td><?php echo $art['art_type_name']; ?></td>
                <td><?php echo $art['art_medium_name']; ?></td>
                <td><?php echo $art['description']; ?></td>
                <td><?php echo $art['selling_price']; ?></td>
                <td>
                    <form method="POST" action="edit_art.php">
                        <input type="hidden" name="image_id" value="<?php echo $art['image_id']; ?>">
                        <button type="submit" name="edit_art">Edit</button>
                    </form>
                    <form method="POST" action="delete_art.php" onsubmit="return confirm('Are you sure you want to delete this art?');">
                        <input type="hidden" name="image_id" value="<?php echo $art['image_id']; ?>">
                        <button type="submit" name="delete_art">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>
