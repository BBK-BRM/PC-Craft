<?php
require '../includes/database.php';
// Start session and check if the user is an admin
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Database connection
$conn = getDB();

// Fetch all orders for admin to view
$stmt = mysqli_prepare($conn, "SELECT * FROM orders ORDER BY created_at DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Craft Admin - Order Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
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
        <div class="components">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Reference ID</th>
                        <th>Action</th>
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
                            <td><a href="user_details.php?user_id=<?php echo $order['user_id']; ?>">
                                    <?php echo $order['user_id']; ?>
                                </a>
                            </td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td>
                                <?php
                                // Fetch items for each order
                                $order_id = $order['order_id'];
                                $item_stmt = mysqli_prepare($conn, "SELECT OrderItems.*, Components.name 
                                        FROM OrderItems 
                                        JOIN Components ON OrderItems.component_id = Components.component_id 
                                        WHERE OrderItems.order_id = ?");
                                mysqli_stmt_bind_param($item_stmt, "i", $order_id);
                                mysqli_stmt_execute($item_stmt);
                                $item_result = mysqli_stmt_get_result($item_stmt);

                                while ($item = mysqli_fetch_assoc($item_result)) {
                                    echo $item['name'] . " x " . $item['quantity'] . " @ NRS." . $item['price'] . "<br>";
                                }

                                mysqli_stmt_close($item_stmt);
                                ?>
                            </td>
                            <td><?php echo "NRS." . number_format($order['total_price'], 2); ?></td>
                            <td>
                                <form method="POST" action="update_order_status.php">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="order_status">
                                        <option value="Pending" <?php echo ($order['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo ($order['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="paid" <?php echo ($order['order_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Shipped" <?php echo ($order['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo ($order['order_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo ($order['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($order['reference_id']); ?></td>
                            <td>
                                <a href="delete_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this order?');">Remove</a>
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