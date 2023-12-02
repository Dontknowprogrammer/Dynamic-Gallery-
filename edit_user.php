<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_name'])) {
    header('location: login_form.php'); // Redirect to the login page if the admin is not logged in
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

// Check if user ID is provided in the query string
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details based on the user ID
    $user_query = mysqli_query($connection, "SELECT * FROM user_form WHERE id = '$user_id'");
    $user_data = mysqli_fetch_assoc($user_query);

    if (!$user_data) {
        echo "User not found.";
        exit();
    }

// Handle form submission for updating user information
if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);

    // Update user information in the database
    $update_query = "UPDATE user_form SET name = '$name', email = '$email' WHERE id = '$user_id'";

    if (mysqli_query($connection, $update_query)) {
        echo "User information updated successfully.";
        // Redirect to the user management page or any other relevant page after updating
        header('location: account.php');
        exit();
    } else {
        echo "Error updating user information: " . mysqli_error($connection);
    }
}

} else {
    echo "User ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/style8.css">
</head>
<body>

    <h1>Edit User</h1>

    <form action="" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $user_data['name']; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $user_data['email']; ?>" required>

        <button type="submit" name="update">Update</button>

        <button formaction="account.php">Go back to account</button>
    </form>
</body>
</html>
