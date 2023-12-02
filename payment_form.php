<?php
function processPayment($creditCardNumber, $expiryDate) {
    if (!isValidCreditCard($creditCardNumber)) {
        return false; // Payment failed
    }

    // Validate expiry date (a simple example, replace with actual validation)
    if (!isValidExpiryDate($expiryDate)) {
        return false; // Payment failed
    }

    return true;
}

// Placeholder function for credit card number validation
function isValidCreditCard($creditCardNumber) {
    return !empty($creditCardNumber);
}

// Placeholder function for expiry date validation
function isValidExpiryDate($expiryDate) {
    return strtotime($expiryDate) > time();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dgs';
$connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle payment processing logic
    $image_id = isset($_POST['image_id']) ? $_POST['image_id'] : '';
    $user_name = isset($_POST['name']) ? $_POST['name'] : '';
    $user_email = isset($_POST['email']) ? $_POST['email'] : '';
    $credit_card_number = isset($_POST['credit_card_number']) ? $_POST['credit_card_number'] : '';
    $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';

    // Validate and sanitize user inputs (add more validation as needed)
    $user_name = mysqli_real_escape_string($connection, $user_name);
    $user_email = filter_var($user_email, FILTER_VALIDATE_EMAIL) ? $user_email : null;
    $credit_card_number = mysqli_real_escape_string($connection, $credit_card_number);

    // Process the payment only if the required fields are not empty
    if (!empty($image_id) && !empty($user_name) && !empty($user_email) && !empty($credit_card_number) && !empty($expiry_date)) {
        // Process the payment here
        $payment_successful = false;

        // Check if credit card number and expiry date are valid
        if (isValidCreditCard($credit_card_number) && isValidExpiryDate($expiry_date)) {
            // Simulate payment processing
            $payment_successful = processPayment($credit_card_number, $expiry_date);
        }

        // Insert the payment into the database if successful
        if ($payment_successful) {
            // Use prepared statements to prevent SQL injection
            $insert_query = "INSERT INTO payments (user_id, credit_card_number, expiry_date, image_id, payment_date) VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = mysqli_prepare($connection, $insert_query);

            // Bind parameters
            mysqli_stmt_bind_param($insert_stmt, "isss", $user_id, $credit_card_number, $expiry_date, $image_id);

            // Retrieve user_id from user_form table using the logged-in user's email
            $user_query = "SELECT id FROM user_form WHERE email = ?";
            $user_stmt = mysqli_prepare($connection, $user_query);
            mysqli_stmt_bind_param($user_stmt, "s", $user_email);
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $user_row = mysqli_fetch_assoc($user_result);
            $user_id = $user_row['id'];

            // Execute the prepared statement
            $insert_result = mysqli_stmt_execute($insert_stmt);

            if ($insert_result) {
                echo "Payment successful. Thank you for your purchase!";
            } else {
                echo "Payment failed to be saved in the database. Please try again.";
            }

            mysqli_stmt_close($insert_stmt);
            mysqli_stmt_close($user_stmt);
        } else {
            echo "Payment failed. Please try again.";
        }
    } else {
        echo "Please enter all required payment information.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style7.css">
</head>

<body>
    <div>
        <h2>Payment Form</h2>

        <form action="" method="post">
            <input type="hidden" name="image_id" value="<?php echo isset($_POST['image_id']) ? $_POST['image_id'] : ''; ?>">
            <label for="name">Your name:</label>
            <input type="text" name="name" required>

            <!-- Update to use 'email' as the input name -->
            <label for="email">Your email:</label>
            <input type="email" name="email" required>

            <label for="credit_card_number">Credit card number:</label>
            <input type="text" name="credit_card_number" required>

            <label for="expiry_date">Expiry date:</label>
            <input type="date" name="expiry_date" required>

            <input type="submit" value="Pay now">
        </form>
        <a href="user_page.php">
            <button>Go Back to Home</button>
        </a>
    </div>
</body>

</html>
