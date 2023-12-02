<?php
@include 'config.php';

session_start();
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dgs';
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = md5($_POST['password']);

    $select = "SELECT * FROM user_form WHERE email = '$email' AND password = '$pass'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);

        if ($row['user_type'] == 'admin') {
            $_SESSION['admin_name'] = $row['name'];
            header('location: admin_page.php');
        } elseif ($row['user_type'] == 'user' && $row['user_verified'] == 'y') {
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['id']=$row['id'];
            header('location: user_page.php');
        } elseif ($row['user_type'] == 'artist') {
            if ($row['user_verified'] == 'y') {
                $_SESSION['artist_name'] = $row['name'];
                $_SESSION['id']=$row['id'];
                header('location: artist_page.php');
            } else {
                $error[] = 'Artist is not verified by admin';
            }
        } else {
            $error[] = 'User is not verified by admin';
        }
    } else {
        $error[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style3.css">

</head>

<body>

    <div class="form-container">

        <form action="" method="post">
            <h3>Login Now</h3>
            <?php
            if (isset($error)) {
                foreach ($error as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                };
            };
            ?>
            <input type="email" name="email" required placeholder="Enter your email">
            <input type="password" name="password" required placeholder="Enter your password">
            <input type="submit" name="submit" value="Login Now" class="form-btn">
            <p>Don't have an account? <a href="register_form.php">Register now</a></p>
        </form>

    </div>

</body>

</html>
