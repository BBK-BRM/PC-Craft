<?php
require '../includes/database.php';

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Database connection 
$conn = getDB();

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $component_id = $_GET['id'];

    // Prepare the SQL statement to delete the component
    $stmt = mysqli_prepare($conn, "DELETE FROM components WHERE component_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $component_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Component deleted successfully!";
        header("Location: product.php");
        exit();
    } else {
        echo "Error: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
} else {
    die("Invalid request.");
}

mysqli_close($conn);

