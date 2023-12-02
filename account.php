<?php
@include 'navbar.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location: login_form.php'); // Redirect to the login page if the admin is not logged in
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

// Handle user registration
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $_POST['user_type'];

    // Insert new user into the database with pending status
    $insert = "INSERT INTO user_form (name, email, password, user_type, user_verified) VALUES ('$name', '$email', '$password', '$user_type', 'n')";

    if (mysqli_query($connection, $insert)) {
        echo "Registration successful. Awaiting admin approval.";
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}

if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];
    $update = "UPDATE user_form SET user_verified = 'y' WHERE id = '$user_id'";
    
    if (mysqli_query($connection, $update)) {
        echo "User approved successfully.";
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}

if (isset($_POST['disapprove'])) {
    $user_id = $_POST['user_id'];
    
    // Handle deletion of associated records in the payments table
    $delete_payments = "DELETE FROM payments WHERE user_id = '$user_id'";
    
    if (mysqli_query($connection, $delete_payments)) {
        // Proceed to remove the user
        $delete_user = "DELETE FROM user_form WHERE id = '$user_id'";
        
        if (mysqli_query($connection, $delete_user)) {
            echo "User disapproved and removed successfully.";
        } else {
            echo "Error deleting user: " . mysqli_error($connection);
        }
    } else {
        echo "Error deleting associated payments: " . mysqli_error($connection);
    }
}


// Handle edit and delete actions
if (isset($_POST['edit'])) {
    $user_id = $_POST['user_id'];
    // Redirect to the edit page with the user ID for further processing
    header("location: edit_user.php?user_id=$user_id");
    exit();
}

if (isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];

    // Handle cascading deletion
    $delete_payments = "DELETE FROM payments WHERE user_id = '$user_id'";
    $delete_images = "DELETE FROM images WHERE user_id = '$user_id'";
    $delete_user = "DELETE FROM user_form WHERE id = '$user_id'";

    // Use transactions to ensure atomicity
    mysqli_autocommit($connection, false);

    if (mysqli_query($connection, $delete_payments) && mysqli_query($connection, $delete_images) && mysqli_query($connection, $delete_user)) {
        mysqli_commit($connection);
        echo "User and associated records deleted successfully.";
    } else {
        mysqli_rollback($connection);
        echo "Error deleting user and associated records: " . mysqli_error($connection);
    }

    // Restore autocommit mode
    mysqli_autocommit($connection, true);
}

$pending_users = mysqli_query($connection, "SELECT * FROM user_form WHERE user_verified = 'n'");
$users_query = mysqli_query($connection, "SELECT * FROM user_form WHERE user_type IN ('user', 'artist')");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
</head>

<body>

    <h1>User Management</h1>

    <h2>Pending Users</h2>

    <?php if (mysqli_num_rows($pending_users) > 0): ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($pending_users)): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['user_type']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="approve">Approve</button>
                            <button type="submit" name="disapprove">Disapprove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No pending users found.</p>
    <?php endif; ?>

    <h2>Users List</h2>

    <?php if (mysqli_num_rows($users_query) > 0): ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($users_query)): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['user_type']; ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit">Edit</button>
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>

</body>

</html>