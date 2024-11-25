<?php
require '../includes/database.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied: You do not have the appropriate permissions to access this page.");
}

// Database connection 
$conn = getDB();

// Filter components based on type
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';

// Prepare the SQL query
$query = "SELECT component_id, name, type, brand, model, price, specs, images FROM components";
if ($filter_type) {
    $query .= " WHERE type = ?";
}

$stmt = mysqli_prepare($conn, $query);
if ($filter_type) {
    mysqli_stmt_bind_param($stmt, "s", $filter_type);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Craft Admin - Manage Products</title>
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
                <li><a href="orders">Manage Orders</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="optimized_route.php">Orders Location</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="components">
            <h2>Available Components</h2>
            <form method="GET" action="product.php">
                <label for="filter_type">Filter by Type:</label>
                <select id="filter_type" name="type" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="CPU" <?php if ($filter_type === 'CPU')
                        echo 'selected'; ?>>CPU</option>
                    <option value="GPU" <?php if ($filter_type === 'GPU')
                        echo 'selected'; ?>>GPU</option>
                    <option value="RAM" <?php if ($filter_type === 'RAM')
                        echo 'selected'; ?>>RAM</option>
                    <option value="Storage" <?php if ($filter_type === 'Storage')
                        echo 'selected'; ?>>Storage</option>
                    <option value="Motherboard" <?php if ($filter_type === 'Motherboard')
                        echo 'selected'; ?>>Motherboard
                    </option>
                    <option value="Power Supply" <?php if ($filter_type === 'Power Supply')
                        echo 'selected'; ?>>Power
                        Supply</option>
                    <option value="Case" <?php if ($filter_type === 'Case')
                        echo 'selected'; ?>>Case</option>
                    <option value="Cooling System" <?php if ($filter_type === 'Cooling System')
                        echo 'selected'; ?>>
                        Cooling System</option>
                    <option value="Others" <?php if ($filter_type === 'Others')
                        echo 'selected'; ?>>Others</option>
                </select>
            </form>
            <a href="add_component.php" class="btn-add">Add New Product</a>
            <table class="product_table">
                <thead>
                    <tr>
                        <th>Component ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Price</th>
                        <th>Specifications</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['component_id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['type']; ?></td>
                            <td><?php echo $row['brand']; ?></td>
                            <td><?php echo $row['model']; ?></td>
                            <td><?php echo $row['price']; ?></td>
                            <td> <?php $specs = json_decode($row['specs'], true);
                            if ($specs) {
                                foreach ($specs as $key => $value) {
                                    echo ucfirst($key) . ": " . htmlspecialchars($value) . "<br>";
                                }
                            } else {
                                echo "No specifications available";
                            } ?></td>
                            <td><img src="<?php echo '../uploads/' . $row['images']; ?>" alt="<?php echo $row['name']; ?>">
                            </td>
                            <td>
                                <a href="edit_component.php?id=<?php echo $row['component_id']; ?>"
                                    class="btn-edit">Edit</a>
                                <a href="delete_product.php?id=<?php echo $row['component_id']; ?>" class="btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this product?');">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    // Free result set and close connection
    mysqli_free_result($result);
    ?>
</body>

</html>