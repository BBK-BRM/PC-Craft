<?php
session_start();
include 'includes/url.php';
require 'includes/database.php';
require 'stripe-config.php'; // Stripe configuration file

if (!isset($_SESSION["is_logged_in"])) {
    redirect('/login');
}

$conn = getDB();

// Initialize variables
$message = "Invalid payment request.";
$message_type = 'error';

if (isset($_GET['q']) && isset($_GET['oid'])) {
    // Handle eSewa Payment
    $status = $_GET['q'];
    $order_id = $_GET['oid'];
    $reference_id = isset($_GET['refId']) ? $_GET['refId'] : null;

    if ($status == 'su' && $reference_id) {
        // Payment successful
        $stmt = $conn->prepare("UPDATE Orders SET order_status = 'Paid', reference_id = ? WHERE order_id = ?");
        $stmt->bind_param("ss", $reference_id, $order_id);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['selected_components']); // Clear cart session
        $message = "eSewa Payment successful! Your order ID is $order_id and Reference ID is $reference_id.";
        $message_type = 'success';
    } elseif ($status == 'fu') {
        // Payment failed
        $stmt = $conn->prepare("UPDATE Orders SET order_status = 'Failed' WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $stmt->close();

        $message = "eSewa Payment failed. Please try again. Your order ID is $order_id.";
        $message_type = 'error';
    }
} elseif (isset($_GET['session_id'])) {
    // Handle Stripe Payment
    $session_id = $_GET['session_id'];

    try {
        // Retrieve the Checkout Session
        $session = \Stripe\Checkout\Session::retrieve($session_id);

        if ($session->payment_status === 'paid') {
            // Payment successful
            $stmt = $conn->prepare("UPDATE Orders SET order_status = 'Paid' WHERE reference_id = ?");
            $stmt->bind_param("s", $reference_id);
            $stmt->execute();
            $stmt->close();

            unset($_SESSION['selected_components']); // Clear cart session
            $message = "Stripe Payment successful! Your order has been processed.";
            $message_type = 'success';
        } else {
            $message = "Stripe Payment failed. Please try again.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = "Stripe Payment error: " . $e->getMessage();
        $message_type = 'error';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - PC Craft</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Payment Confirmation</h1>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <a href="/" class="button">Return to Home</a>
    </div>
</body>

</html>