<?php
include '../includes/url.php';
require '../includes/database.php';

session_start();

if (!isset($_SESSION["is_logged_in"])) {
    redirect('/login');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

$conn = getDB();
// Simulated data (Replace with actual database queries)
$totalSales = 0;
$totalSalesQuery = mysqli_query($conn, "SELECT * FROM Orders WHERE order_status ='paid'") or die('Query Failed.');
while ($fetch_sales = mysqli_fetch_assoc($totalSalesQuery)) {
    $totalSales += $fetch_sales['total_price'];
}

$totalOrders = 0;
$totalOrderQuery = mysqli_query($conn, "SELECT * FROM Orders") or die("Query Failed");
$totalOrders = mysqli_num_rows($totalOrderQuery);

$totalUsers = 0;
$TotalUserQuery = mysqli_query($conn, "SELECT * FROM Users WHERE role='user'") or die("Query Failed");
$totalUsers = mysqli_num_rows($TotalUserQuery);

// Sample chart data (could be fetched from DB)
// $salesData = [1200, 2300, 1800, 2400, 2800, 3200];
// $orderData = [30, 40, 25, 35, 50, 45];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Craft Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <main class="main-content">
            <section id="overview" class="section">
                <h2>Overview</h2>
                <div class="cards">
                    <div class="card">
                        <h3>Total Sales</h3>
                        <p>NRS.<?php echo number_format($totalSales); ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Orders</h3>
                        <p><?php echo $totalOrders; ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>
                <h2>Sales & Orders Trends</h2>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </section>
        </main>
    </div>
    <script>
        // Sales Chart
        // const salesCtx = document.getElementById('salesChart').getContext('2d');
        // const salesChart = new Chart(salesCtx, {
        //     type: 'line',
        //     data: {
        //         labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        //         datasets: [{
        //             label: 'Sales',
        //             data: <?php echo json_encode($salesData); ?>,
        //             backgroundColor: 'rgba(75, 192, 192, 0.2)',
        //             borderColor: 'rgba(75, 192, 192, 1)',
        //             borderWidth: 1
        //         }]
        //     },
        //     options: {
        //         scales: {
        //             y: {
        //                 beginAtZero: true
        //             }
        //         }
        //     }
        // });

    </script>
</body>

</html>