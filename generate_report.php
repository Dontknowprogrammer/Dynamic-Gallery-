<?php
include 'config.php';
include 'navbar.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['admin_name'])) {
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

// Handle report generation
if (isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'];

    switch ($reportType) {
        case 'images':
            generateImageReport($connection);
            break;
        case 'users':
            generateUserReport($connection);
            break;
            case 'purchase_history':
                generatePurchaseHistoryReport($connection);
                break;
                case 'artists':
                    generateArtistReport($connection);
                    break;
        default:
            echo "Invalid report type selected";
    }
}

function generateImageReport($connection){
    $report_data = [];
    $sql = "SELECT * FROM images";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }

        // Generate HTML table for the image report
        echo "<h2>Art Report</h2>";
        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr><th>Image ID</th><th>User ID</th><th>Art Type ID</th><th>Art Medium ID</th><th>Description</th><th>Selling Price</th><th>Uploaded At</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($report_data as $image) {
            echo "<tr>";
            echo "<td>" . $image['image_id'] . "</td>";
            echo "<td>" . $image['user_id'] . "</td>";
            echo "<td>" . $image['art_type_id'] . "</td>";
            echo "<td>" . $image['art_medium_id'] . "</td>";
            echo "<td>" . $image['description'] . "</td>";
            echo "<td>" . $image['selling_price'] . "</td>";
            echo "<td>" . $image['created_at'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "Error retrieving image data";
    }
}

function generateUserReport($connection){
    $sql = "SELECT id, name, email, user_type, user_verified FROM user_form WHERE user_type = 'user'";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }

        // Generate HTML table for the user report
        echo "<h2>User Report</h2>";
        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>User Type</th><th>User Verified</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['user_type'] . "</td>";
            echo "<td>" . $user['user_verified'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "Error retrieving user data";
    }
}
function generatePurchaseHistoryReport($connection){
    $report_data = [];
    $sql = "SELECT payments.*, user_form.name AS user_name, art_types.art_type_name, art_mediums.art_medium_name
            FROM payments
            JOIN images ON payments.image_id = images.image_id
            JOIN user_form ON user_form.id = payments.user_id
            JOIN art_types ON art_types.art_type_id = images.art_type_id
            JOIN art_mediums ON art_mediums.art_medium_id = images.art_medium_id";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }

        // Generate HTML table for the purchase history report
        echo "<h2>Purchase History Report</h2>";
        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr><th>Payment ID</th><th>User Name</th><th>Credit Card Number</th><th>Expiry Date</th><th>Art Type</th><th>Art Medium</th><th>Payment Date</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($report_data as $purchase) {
            echo "<tr>";
            echo "<td>" . $purchase['payment_id'] . "</td>";
            echo "<td>" . $purchase['user_name'] . "</td>";
            echo "<td>" . $purchase['credit_card_number'] . "</td>";
            echo "<td>" . $purchase['expiry_date'] . "</td>";
            echo "<td>" . $purchase['art_type_name'] . "</td>";
            echo "<td>" . $purchase['art_medium_name'] . "</td>";
            echo "<td>" . $purchase['payment_date'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "Error retrieving purchase history data";
    }
}
function generateArtistReport($connection){
    $sql = "SELECT id, name, email, user_verified FROM user_form WHERE user_type = 'artist'";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        $artists = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $artists[] = $row;
        }

        // Generate HTML table for the artist report
        echo "<h2>Artist Report</h2>";
        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr><th>Artist ID</th><th>Name</th><th>Email</th><th>User Verified</th></tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($artists as $artist) {
            echo "<tr>";
            echo "<td>" . $artist['id'] . "</td>";
            echo "<td>" . $artist['name'] . "</td>";
            echo "<td>" . $artist['email'] . "</td>";
            echo "<td>" . $artist['user_verified'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "Error retrieving artist data";
    }
}
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <div>
        <!-- Report Generation Form -->
        <form method="post" action="">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type">
                <option value="images">Art</option>
                <option value="users">Users</option>
                <option value="purchase_history">Purchase History</option>
                <option value="artists">Artists</option>
            </select>
            <button type="submit" name="generate_report">Generate Report</button>
        </form>
    </div>
</body>
</html>