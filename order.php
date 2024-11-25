<?php
require 'includes/database.php';
include 'includes/header.php';
require 'includes/url.php';

if (!isset($_SESSION['user_id'])) {
  redirect('/login');
  die("Access denied: You need to log in to view your order history.");
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Database connection
$conn = getDB();

// Fetch the orders for the current user
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="order-history-page">
    <div class="container">
        <h2>Your Order History</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Reference ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <!-- Link to the detailed order page -->
                            <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>">
                                <?php echo $order['order_id']; ?>
                            </a>
                        </td>
                        <td><?php echo $order['created_at']; ?></td>
                        <td><?php echo "Rs." . number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                        <td><?php echo htmlspecialchars($order['reference_id']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index" class="btn-back">Back to Home</a>
    </div>


<?php
include 'includes/footer.php';
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
