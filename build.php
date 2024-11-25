<?php
require 'includes/database.php';
include 'includes/header.php';

$conn = getDB();

// Component types
$componentTypes = [
    'CPU' => 'CPU',
    'Motherboard' => 'Motherboard',
    'GPU' => 'GPU',
    'RAM' => 'RAM',
    'Storage' => 'Storage',
    'Power Supply' => 'Power Supply',
    'Case' => 'Case',
    'Cooling System' => 'Cooling System'
];

// Fetch components based on types
$components = [];

foreach ($componentTypes as $type) {
    $stmt = mysqli_prepare($conn, "SELECT component_id, name, brand, model, price, specs, images FROM components WHERE type = ?");
    mysqli_stmt_bind_param($stmt, "s", $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $components[$type] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Initialize selected components and compatibility check
$selectedComponents = [];
$errors = [];
$totalPrice = 0;


// Compatibility check function
function checkCompatibility($selectedComponents, &$errors)
{
    // Define arcs (component pairs that need to be checked for compatibility)
    $constraints = [
        ['CPU', 'Motherboard'],
        ['Motherboard', 'RAM'],
        ['Power Supply', 'GPU'],
        ['Case', 'Cooling System'],
    ];

    $arcs = $constraints;

    // AC-3 Algorithm loop
    while (!empty($arcs)) {
        list($Xi, $Xj) = array_pop($arcs);
        if (revise($selectedComponents, $Xi, $Xj, $errors)) {
            // If a component becomes invalid, return false
            if (empty($selectedComponents[$Xi]['specs'])) {
                return false;
            }

            // Add neighbors of Xi to arcs to be checked again
            foreach ($constraints as $constraint) {
                if ($constraint[0] === $Xi && $constraint[1] !== $Xj) {
                    $arcs[] = $constraint;
                }
            }
        }
    }
    return true;
}

// Revise function to check compatibility
function revise(&$selectedComponents, $Xi, $Xj, &$errors)
{
    $revised = false;

    // Example: Check CPU and Motherboard socket compatibility
    if ($Xi === 'CPU' && $Xj === 'Motherboard') {
        $cpuSpecs = json_decode($selectedComponents['CPU']['specs'], true) ?? [];
        $motherboardSpecs = json_decode($selectedComponents['Motherboard']['specs'], true) ?? [];

        if (isset($cpuSpecs['socket']) && isset($motherboardSpecs['socket']) && $cpuSpecs['socket'] !== $motherboardSpecs['socket']) {
            $errors[] = "CPU and Motherboard socket types do not match!";
            $revised = true;
        }
    }

    // Add more checks for other pairs (e.g., GPU and PSU)
    // Example: PSU wattage check with GPU
    if ($Xi === 'Power Supply' && $Xj === 'GPU') {
        $psuSpecs = json_decode($selectedComponents['Power Supply']['specs'], true) ?? [];
        $gpuSpecs = json_decode($selectedComponents['GPU']['specs'], true) ?? [];

        if (isset($psuSpecs['wattage']) && isset($gpuSpecs['power_draw'])) {
            if ($psuSpecs['wattage'] < $gpuSpecs['power_draw']) {
                $errors[] = "Power Supply wattage is insufficient for the GPU!";
                $revised = true;
            }
        }
    }

    return $revised;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($componentTypes as $type) {
        if (isset($_POST[$type]) && !empty($_POST[$type])) {
            $selectedComponent = json_decode($_POST[$type], true);
            $selectedComponents[$type] = $selectedComponent;
            $totalPrice += $selectedComponent['price'];
        }
    }

    // Perform compatibility check using AC-3 algorithm
    $isCompatible = checkCompatibility($selectedComponents, $errors);

    if (!$isCompatible) {
        $_SESSION['errors'] = $errors;
    } else {
        // Prepare query string for redirect
        $_SESSION['selected_components'] = json_encode($selectedComponents);
        $_SESSION['totalPrice'] = $totalPrice;
        header('Location: checkout');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Your Custom PC - PC Craft</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #111;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #007bff;
        }

        main form {
            margin-bottom: 40px;
        }

        h2 {
            font-size: 1.5rem;
            color: #343a40;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td img {
            max-width: 80px;
            height: auto;
        }

        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #ff6600;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.25rem;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        #compatibility-issues {
            margin-top: 20px;
        }

        h3 {
            font-size: 1.25rem;
            margin-top: 20px;
        }

        h3[style="color: red;"] {
            color: #dc3545;
        }

        h3[style="color: green;"] {
            color: #28a745;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const compatibilityIssuesContainer = document.querySelector('#compatibility-issues');

            function checkCompatibility() {
                const formData = new FormData(form);
                const selectedComponents = {};

                formData.forEach((value, key) => {
                    // Parse each selected component as a JSON object
                    try {
                        selectedComponents[key] = JSON.parse(value);
                    } catch (error) {
                        console.error('Error parsing component:', key, value, error);
                    }
                });

                let issues = [];

                // Compatibility check: CPU and Motherboard sockets
                const cpu = selectedComponents['CPU'];
                const motherboard = selectedComponents['Motherboard'];

                if (cpu && motherboard) {
                    const cpuSpecs = cpu.specs ? JSON.parse(cpu.specs) : null;
                    const motherboardSpecs = motherboard.specs ? JSON.parse(motherboard.specs) : null;

                    if (cpuSpecs && motherboardSpecs) {
                        const cpuSocket = cpuSpecs.socket;
                        const motherboardSocket = motherboardSpecs.socket;

                        if (cpuSocket && motherboardSocket && cpuSocket !== motherboardSocket) {
                            issues.push("CPU and Motherboard socket types do not match.");
                        }
                    } else {
                        issues.push("CPU or Motherboard specs are missing or invalid.");
                    }
                }

                // Display compatibility issues or success message
                if (issues.length > 0) {
                    compatibilityIssuesContainer.innerHTML = `
                <h3 style="color: red;">Compatibility Issues Detected</h3>
                <ul>${issues.map(issue => `<li>${issue}</li>`).join('')}</ul>
            `;
                } else {
                    compatibilityIssuesContainer.innerHTML = '<h3 style="color: green;">All components are compatible!</h3>';
                }
            }

            // Trigger compatibility check on form change
            form.addEventListener('change', checkCompatibility);
        });

    </script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Build Your Custom PC</h1>
        </div>

        <main>
            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="error-messages">
                    <h3 style="color: red;">Compatibility Issues Detected:</h3>
                    <ul>
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); // Clear errors after displaying them ?>
            <?php endif; ?>

            <form method="POST" action="build.php">
                <?php foreach ($componentTypes as $type => $label): ?>
                    <h2>Select <?php echo htmlspecialchars($label); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($components[$type] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($item['model']); ?></td>
                                    <td>NRS.<?php echo htmlspecialchars($item['price']); ?> </td>
                                    <td><img src="<?php echo '../uploads/' . $item['images']; ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                                    <td>
                                        <input type="radio" name="<?php echo htmlspecialchars($type); ?>"
                                            value="<?php echo htmlspecialchars(json_encode($item)); ?>" required>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>

                <button type="submit">Build PC</button>
            </form>
            <div id="compatibility-issues">

            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>