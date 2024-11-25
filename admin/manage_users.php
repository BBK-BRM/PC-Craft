<?php
require '../includes/database.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Database connection
$conn = getDB();

// Fetch all users
$stmt = mysqli_prepare($conn, "SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Craft Admin - Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>PC Craft Admin</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Overview</a></li>
                <li><a href="product.php">Manage Products</a></li>
                <li><a href="orders.php">Manage Orders</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="optimized_route.php">Orders Location</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="components">
            <h2>Manage Users</h2>
            <table style="height: auto;">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td>
                                <!-- Delete User -->
                                <a href="delete_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>