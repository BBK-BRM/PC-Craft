<?php
require '../includes/database.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Database connection
    $conn = getDB();

    // Delete order from order_items first (due to foreign key constraints)
    $stmt_items = mysqli_prepare($conn, "DELETE FROM OrderItems WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt_items, "i", $order_id);
    mysqli_stmt_execute($stmt_items);
    mysqli_stmt_close($stmt_items);

    // Then delete the order itself
    $stmt_order = mysqli_prepare($conn, "DELETE FROM Orders WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt_order, "i", $order_id);

    if (mysqli_stmt_execute($stmt_order)) {
      echo "Order deleted successfully!";
      header("Location: orders.php");
      exit();
    } else {
        echo "Error deleting order: " . mysqli_stmt_error($stmt_order);
    }

    mysqli_stmt_close($stmt_order);
    mysqli_close($conn);

}