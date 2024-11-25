<?php
require 'database.php';

function getComponentConstraints($conn) {
    $sql = "SELECT * FROM ComponentConstraints";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function isCompatible($component1, $component2, $constraintType) {
    // This function will check the compatibility between two components based on the constraint type

    // Fetch component details
    $conn = getDB();
    
    $sql = "SELECT * FROM Components WHERE id IN (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $component1, $component2);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $components = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Separate components
    $comp1 = array_filter($components, fn($comp) => $comp['id'] === $component1);
    $comp2 = array_filter($components, fn($comp) => $comp['id'] === $component2);
    $comp1 = array_shift($comp1);
    $comp2 = array_shift($comp2);

    // Example compatibility checks
    if ($constraintType === 'socket') {
        // Example: Check if CPU socket matches motherboard socket
        return $comp1['socket'] === $comp2['socket'];
    } elseif ($constraintType === 'form_factor') {
        // Example: Check if motherboard form factor matches case form factor
        return $comp1['form_factor'] === $comp2['form_factor'];
    } elseif ($constraintType === 'power_rating') {
        // Example: Check if power supply rating is sufficient for the components
        $totalPowerRequired = $comp1['power_rating'] + $comp2['power_rating']; // Simplified example
        return $totalPowerRequired <= $comp2['max_power_rating'];
    } 

    // Add more constraint types as needed

    return true; // Default return value if no specific check is implemented
}

function ac3($selectedComponents, $constraints) {
    $queue = [];
    foreach ($constraints as $constraint) {
        $queue[] = [$constraint['component1_id'], $constraint['component2_id'], $constraint['constraint_type']];
    }

    while (!empty($queue)) {
        list($comp1, $comp2, $constraintType) = array_shift($queue);
        
        // Check compatibility
        if (!isCompatible($comp1, $comp2, $constraintType)) {
            return false;
        }
    }
    return true;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$selectedComponents = $input;

$conn = getDB();
$constraints = getComponentConstraints($conn);
mysqli_close($conn);

$compatible = ac3($selectedComponents, $constraints);

echo json_encode(['compatible' => $compatible]);

