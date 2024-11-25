<?php
require '../includes/database.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Get the user ID from the query parameter
if (!isset($_GET['user_id'])) {
    die("Invalid request: No user ID provided.");
}

$user_id = intval($_GET['user_id']);

// Database connection
$conn = getDB();

// Fetch user and address details from the database
$stmt = mysqli_prepare($conn, "SELECT u.user_id, u.username, u.email, u.created_at as user_created_at, 
                               u.updated_at as user_updated_at, a.address, a.city, a.phone,
                               a.updated_at as address_updated_at
                               FROM Users u
                               LEFT JOIN UserAddresses a ON u.user_id = a.user_id
                               WHERE u.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // If user is found, display their details
} else {
    die("User not found.");
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="user_detail_page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>PC Craft Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard">Overview</a></li>
            <li><a href="product">Manage Products</a></li>
            <li><a href="orders">Manage Orders</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="optimized_route.php">Orders Location</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </aside>

    <div class="user_detail_container">
        <h2>User Details</h2>
        <p><strong>User ID:</strong> <?php echo $user['user_id']; ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Account Created At:</strong> <?php echo htmlspecialchars($user['user_created_at']); ?></p>
        <p><strong>Account Updated At:</strong> <?php echo htmlspecialchars($user['user_updated_at']); ?></p>

        <h3>Address Information</h3>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']) ?: 'No address provided'; ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']) ?: 'No city provided'; ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']) ?: 'No phone provided'; ?></p>
        <p><strong>Address Updated At:</strong> <?php echo htmlspecialchars($user['address_updated_at']); ?></p>

        <a href="orders.php" class="btn-back">Back to Order History</a>
    </div>


</body>

</html>