<?php
require '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Database connection
    $conn = getDB();

    // Update order status
    $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $order_status, $order_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Order status updated successfully!";
    } else {
        echo "Error updating order status: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Redirect back to the order management page
    header("Location: orders.php");
    exit();
}

