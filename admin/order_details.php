<?php
require '../includes/database.php';

session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}
// Get the order ID from the URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die("Invalid order ID.");
}

// Database connection
$conn = getDB();

// Fetch the order details
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
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

$coordinates = explode(',', $order_data['location']);
$latitude = $coordinates[0];
$longitude = $coordinates[1];


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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<style>
    #map {
        margin-top: 20px;
        height: 400px;
        width: 100%;
    }
</style>

<body class="order-details-page">
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
    <div class="order_detail_container">
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

        <div id="map"></div>

        <a href="orders.php" class="btn-back">Back to Order History</a>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Use PHP to pass latitude and longitude to JavaScript
            const latitude = <?php echo json_encode($latitude); ?>;
            const longitude = <?php echo json_encode($longitude); ?>;

            // Initialize the map
            const map = L.map('map').setView([latitude, longitude], 15);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a marker at the specified location
            L.marker([latitude, longitude]).addTo(map)
                .bindPopup("Delivery Location")
                .openPopup();
        });
    </script>