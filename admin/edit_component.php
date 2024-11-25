<?php
require '../includes/database.php';

// Start the session and check if the user is an admin
// session_start();
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     die("Access denied: You do not have the appropriate permissions to access this page.");
// }

// Database connection
$conn = getDB();

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $component_id = $_GET['id'];

    // Retrieve the existing component data
    $stmt = mysqli_prepare($conn, "SELECT name, type, brand, model, price, specs, images FROM components WHERE component_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $component_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Assign the fetched data to variables
        $name = $row['name'];
        $type = $row['type'];
        $brand = $row['brand'];
        $model = $row['model'];
        $price = $row['price'];
        $specs = $row['specs'];
        $current_image = $row['images'];
    } else {
        die("Component not found.");
    }

    mysqli_stmt_close($stmt);
} else {
    die("Invalid request.");
}

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $price = $_POST['price'];
    $specs = $_POST['specs'];

    // Handle image upload
    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image);

        // Upload the new image
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_to_use = $target_file;
        } else {
            echo "Error uploading image.";
            $image_to_use = $current_image; // Use existing image if upload fails
        }
    } else {
        $image_to_use = $current_image; // Use existing image if no new image is uploaded
    }

    // Prepare the SQL statement to update the component
    $stmt = mysqli_prepare($conn, "UPDATE components SET name = ?, type = ?, brand = ?, model = ?, price = ?, specs = ?, images = ? WHERE component_id = ?");
    mysqli_stmt_bind_param($stmt, "ssssdssi", $name, $type, $brand, $model, $price, $specs, $image_to_use, $component_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Component updated successfully!";
        header("Location: product.php");
        exit();
    } else {
        echo "Error: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Component - PC Craft</title>
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

        <div class="editContainer">
            <h2>Edit Component</h2>
            <form method="POST" action="edit_component.php?id=<?php echo $component_id; ?>"
                enctype="multipart/form-data">
                <label for="name">Component Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

                <label for="type">Component Type:</label>
                <select id="type" name="type" required>
                    <option value="CPU" <?php if ($type === 'CPU')
                        echo 'selected'; ?>>CPU</option>
                    <option value="GPU" <?php if ($type === 'GPU')
                        echo 'selected'; ?>>GPU</option>
                    <option value="RAM" <?php if ($type === 'RAM')
                        echo 'selected'; ?>>RAM</option>
                    <option value="Storage" <?php if ($type === 'Storage')
                        echo 'selected'; ?>>Storage</option>
                    <option value="Motherboard" <?php if ($type === 'Motherboard')
                        echo 'selected'; ?>>Motherboard
                    </option>
                    <option value="Power Supply" <?php if ($type === 'Power Supply')
                        echo 'selected'; ?>>Power Supply
                    </option>
                    <option value="Case" <?php if ($type === 'Case')
                        echo 'selected'; ?>>Case</option>
                    <option value="Cooling System" <?php if ($type === 'Cooling System')
                        echo 'selected'; ?>>Cooling
                        System</option>
                    <option value="Others" <?php if ($type === 'Others')
                        echo 'selected'; ?>>Others</option>
                </select>

                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>" required>

                <label for="model">Model:</label>
                <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($model); ?>" required>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>"
                    required>

                <label for="specs">Specifications (JSON format):</label>
                <textarea id="specs" name="specs" rows="4" required><?php echo htmlspecialchars($specs); ?></textarea>

                <label for="image">Component Image:</label>
                <input type="file" id="image" name="image" accept="image/*">

                <img src="<?php echo '../uploads/' . htmlspecialchars($current_image); ?>" alt="Current Image"
                    style="max-width: 200px; display: block; margin-top: 10px;">

                <button type="submit" class="btn-submit">Update Component</button>
            </form>
        </div>
    </div>
</body>

</html>