<?php
include 'config.php';
include 'navbar.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:login_form.php');
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

// Handle Edit
if (isset($_GET['edit_medium_id'])) {
    $edit_medium_id = $_GET['edit_medium_id'];
    $edit_medium_query = "SELECT * FROM art_mediums WHERE art_medium_id = $edit_medium_id";
    $edit_medium_result = mysqli_query($connection, $edit_medium_query);

    if ($edit_medium_result && mysqli_num_rows($edit_medium_result) > 0) {
        $edit_medium_row = mysqli_fetch_assoc($edit_medium_result);
        $_SESSION['edit_art_medium'] = $edit_medium_row['art_medium_name'];
        $_SESSION['edit_medium_id'] = $edit_medium_id;
        header("location: edit_art_medium_form.php");
        exit();
    } else {
        echo "Error retrieving data for editing.";
    }
}


// Handle Update
if (isset($_POST['update_medium'])) {
    $edit_medium_id = $_POST['edit_medium_id'];
    $updated_art_medium = mysqli_real_escape_string($connection, $_POST['updated_art_medium']);

    $update_medium_query = "UPDATE art_mediums SET art_medium_name = '$updated_art_medium' WHERE art_medium_id = $edit_medium_id";
    if (mysqli_query($connection, $update_medium_query)) {
        echo "Art medium updated successfully";
        header("location: art_medium.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($connection);
    }
}

// Handle Delete
if (isset($_POST['delete_medium_id'])) {
    $delete_medium_id = $_POST['delete_medium_id'];
    $delete_medium_query = "DELETE FROM art_mediums WHERE art_medium_id = $delete_medium_id";
    if (mysqli_query($connection, $delete_medium_query)) {
        echo "Art medium deleted successfully";
        header("location: art_medium.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($connection);
    }
}

// Handle Add
if (isset($_POST['submit_medium'])) {
    $art_medium = mysqli_real_escape_string($connection, $_POST['art_medium']);
    $insert_medium_query = "INSERT INTO art_mediums (art_medium_name) VALUES ('$art_medium')";
    if (mysqli_query($connection, $insert_medium_query)) {
        echo "Art medium added successfully";
    } else {
        echo "Error adding record: " . mysqli_error($connection);
    }
}

// Fetch Art Mediums
$select_medium_query = "SELECT * FROM art_mediums";
$result_medium = mysqli_query($connection, $select_medium_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Art Mediums</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
</head>

<body>

    <h2>Manage Art Mediums</h2>

    <!-- Add Art Medium Form -->
    <form method="post" action="art_medium.php">
        <label for="art_medium">Art Medium:</label>
        <input type="text" id="art_medium" name="art_medium" required>
        <button type="submit" name="submit_medium">Add Art Medium</button>
    </form>

    <!-- Display Art Mediums -->
    <table border="1">
        <tr>
            <th>Art Medium</th>
            <th>Creation Date</th>
            <th>Action</th>
        </tr>
        <?php
        while ($row_medium = mysqli_fetch_assoc($result_medium)) {
            echo "<tr>";
            echo "<td>{$row_medium['art_medium_name']}</td>";
            echo "<td>{$row_medium['created_at']}</td>";
            echo "<td>
            <form method='get' action='art_medium.php' style='display:inline;'>
                <input type='hidden' name='edit_medium_id' value='{$row_medium['art_medium_id']}'>
                <button type='submit' name='edit_medium_button'>Edit</button>
            </form>
            <form method='post' action='art_medium.php' style='display:inline;'>
                <input type='hidden' name='delete_medium_id' value='{$row_medium['art_medium_id']}'>
                <button type='submit' name='delete_medium_button'>Delete</button>
            </form>
        </td>";

            echo "</tr>";
        }
        ?>
    </table>

</body>

</html>

<?php
mysqli_close($connection);
?>
