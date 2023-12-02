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
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "SELECT * FROM art_types WHERE art_type_id = $edit_id";
    $edit_result = mysqli_query($connection, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_row = mysqli_fetch_assoc($edit_result);
        $_SESSION['edit_art_type'] = $edit_row['art_type_name'];
        $_SESSION['edit_id'] = $edit_id;
        header("location: edit_art_type_form.php");
        exit();
    } else {
        echo "Error retrieving data for editing.";
    }
}


// Handle Update
if (isset($_POST['update'])) {
    $edit_id = $_POST['edit_id'];
    $updated_art_type = mysqli_real_escape_string($connection, $_POST['updated_art_type']);

    $update_query = "UPDATE art_types SET art_type_name = '$updated_art_type' WHERE art_type_id = $edit_id";
    if (mysqli_query($connection, $update_query)) {
        echo "Art type updated successfully";
        header("location: art_type.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($connection);
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM art_types WHERE art_type_id = $delete_id";
    if (mysqli_query($connection, $delete_query)) {
        echo "Art type deleted successfully";
        header("location: art_type.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($connection);
    }
}

// Handle Add
if (isset($_POST['submit'])) {
    $art_type = mysqli_real_escape_string($connection, $_POST['art_type']);
    $insert_query = "INSERT INTO art_types (art_type_name) VALUES ('$art_type')";
    if (mysqli_query($connection, $insert_query)) {
        echo "Art type added successfully";
    } else {
        echo "Error adding record: " . mysqli_error($connection);
    }
}

// Fetch Art Types
$select_query = "SELECT * FROM art_types";
$result = mysqli_query($connection, $select_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Art Types</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
</head>

<body>

    <h2>Manage Art Types</h2>

    <!-- Add Art Type Form -->
    <form method="post" action="art_type.php">
        <label for="art_type">Art Type:</label>
        <input type="text" id="art_type" name="art_type" required>
        <button type="submit" name="submit">Add Art Type</button>
    </form>

    <!-- Display Art Types -->
    <table border="1">
        <tr>
            <th>Art Type</th>
            <th>Creation Date</th>
            <th>Action</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['art_type_name']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "<td>
            <form method='get' action='art_type.php' style='display:inline;'>
                <input type='hidden' name='edit_id' value='{$row['art_type_id']}'>
                <button type='submit' name='edit_button'>Edit</button>
            </form>
            <form method='get' action='art_type.php' style='display:inline;'>
                <input type='hidden' name='delete_id' value='{$row['art_type_id']}'>
                <button type='submit' name='delete_button'>Delete</button>
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
