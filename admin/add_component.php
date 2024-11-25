<?php
require '../includes/database.php';
// Start the session and check if the user is an admin
// session_start();

// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     die("Access denied: You do not have the appropriate permissions to access this page.");
// }

// Database connection 
$conn = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $price = $_POST['price'];
    $specs = $_POST['specs'];

    // Validate and encode specs as JSON
    $specs_json = json_encode($specs);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Invalid specs format. Please ensure it's valid JSON.");
    }

    // Handle image upload
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($image);

    // Upload the image
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Prepare the SQL statement
        $stmt = mysqli_prepare($conn, "INSERT INTO components (name, type, brand, model, price, specs, images) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssdss", $name, $type, $brand, $model, $price, $specs_json, $target_file);

        if (mysqli_stmt_execute($stmt)) {
            echo "Component added successfully!";
            header("Location: product.php");
            exit();
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error uploading image.";
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PC Craft</title>
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
        <div class="addContainer">
            <h2>Add a New Component</h2>
            <form method="POST" action="add_component.php" enctype="multipart/form-data">
                <label for="name">Component Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="type">Component Type:</label>
                <select id="type" name="type" required>
                    <option value="CPU">CPU</option>
                    <option value="GPU">GPU</option>
                    <option value="RAM">RAM</option>
                    <option value="Storage">Storage</option>
                    <option value="Motherboard">Motherboard</option>
                    <option value="Power Supply">Power Supply</option>
                    <option value="Case">Case</option>
                    <option value="Cooling System">Cooling System</option>
                    <option value="Others">Others</option>
                </select>

                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" required>

                <label for="model">Model:</label>
                <input type="text" id="model" name="model" required>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>

                <label for="specs">Specifications (JSON format):</label>
                <textarea id="specs" name="specs" rows="4" required></textarea>

                <label for="image">Component Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>

                <button type="submit" class="btn-submit">Add Component</button>
            </form>
        </div>
    </div>
</body>

</html>