<?php
require '../includes/database.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Check if a user ID is provided
if (!isset($_GET['user_id'])) {
    die("Invalid request: No user ID provided.");
}

$user_id = intval($_GET['user_id']);

// Database connection
$conn = getDB();

// Prepare the delete statement
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);

// Execute the delete statement
if (mysqli_stmt_execute($stmt)) {
    // Redirect back to manage users page after deletion
    header("Location: manage_users.php?success=User deleted successfully");
} else {
    die("Error deleting user: " . mysqli_error($conn));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);