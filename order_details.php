<?php
require 'includes/database.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied: You need to log in to view order details.");
}

// Get the order ID from the URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die("Invalid order ID.");
}

// Database connection
$conn = getDB();

// Fetch the order details
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$order = mysqli_stmt_get_result($stmt);

if ($order && mysqli_num_rows($order) > 0) {
    $order_data = mysqli_fetch_assoc($order);

    // Fetch the items related to this order
    $item_stmt = mysqli_prepare($conn, "SELECT OrderItems.*, Components.name 
                                        FROM OrderItems 
                                        JOIN Components ON OrderItems.component_id = Components.component_id 
                                        WHERE OrderItems.order_id = ?");
    mysqli_stmt_bind_param($item_stmt, "i", $order_id);
    mysqli_stmt_execute($item_stmt);
    $items = mysqli_stmt_get_result($item_stmt);
} else {
    die("Order not found or you do not have permission to view it.");
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Order Details Page Styling */
        .order-details-page .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .order-details-page h2 {
            text-align: center;
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .order-details-page table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .order-details-page table th,
        .order-details-page table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .order-details-page table th {
            background-color: #f4f4f4;
            color: #555;
        }

        .order-details-page table tr:hover {
            background-color: #f1f1f1;
        }

        .order-details-page .order-summary {
            margin-top: 30px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .order-details-page .order-summary .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .order-details-page .btn-back {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
        }

        .order-details-page .btn-back:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .order-details-page .container {
                padding: 10px;
            }

            .order-details-page table th,
            .order-details-page table td {
                font-size: 14px;
            }

            .order-details-page h2 {
                font-size: 24px;
            }

            .order-details-page .btn-back {
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="order-details-page">
    <div class="container">
        <h2>Order Details - #<?php echo $order_data['order_id']; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Component Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo "NRS." . number_format($item['price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="order-summary">
            <div class="summary-item">
                <span>Order Date:</span>
                <span><?php echo $order_data['created_at']; ?></span>
            </div>
            <div class="summary-item">
                <span>Order Status:</span>
                <span><?php echo htmlspecialchars($order_data['order_status']); ?></span>
            </div>
            <div class="summary-item">
                <span>Total Price:</span>
                <span><?php echo "NRS." . number_format($order_data['total_price'], 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Reference ID:</span>
                <span><?php echo htmlspecialchars($order_data['reference_id']); ?></span>
            </div>
        </div>

        <a href="order.php" class="btn-back">Back to Order History</a>
    </div>
    <?php include 'includes/footer.php'; ?>