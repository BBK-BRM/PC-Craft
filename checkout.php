<?php
require 'includes/database.php';
include 'includes/header.php';

include 'includes/url.php';
if (!isset($_SESSION["is_logged_in"])) {
    redirect('/login');
}

$conn = getDB();

// Ensure user has selected components
if (!isset($_SESSION['selected_components'])) {
    redirect('/build');
    exit();
}


// Initialize variables
$selectedComponents = json_decode($_SESSION['selected_components'], true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $location = "$latitude,$longitude";
    $paymentMethod = $_POST['payment_method'];
    $userId = $_SESSION['user_id'];

    // Validate phone number
    if (strlen($phone) > 20) {
        $errors[] = "Phone number is too long!";
    }

    if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = "Invalid phone number format!";
    }


    // Calculate total amount for the order
    $totalPrice = $_SESSION['totalPrice'];

    // Check if address exists for this user
    $stmt = mysqli_prepare($conn, "SELECT address_id FROM UserAddresses WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update the existing address
        $stmt = mysqli_prepare($conn, "UPDATE UserAddresses SET address = ?, city = ?, phone = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $address, $city, $phone, $userId);
    } else {
        // Insert new address
        $stmt = mysqli_prepare($conn, "INSERT INTO UserAddresses (user_id, address, city, phone) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $userId, $address, $city, $phone);
    }
    mysqli_stmt_execute($stmt);
    $addressId = mysqli_insert_id($conn); // Save address ID for future reference
    mysqli_stmt_close($stmt);

    // Create a new order
    $stmt = mysqli_prepare($conn, "INSERT INTO Orders (user_id, total_price, order_status, reference_id,location) VALUES (?, ?, 'Pending', ?, ?)");
    $referenceId = uniqid(); // Generate a unique reference ID
    mysqli_stmt_bind_param($stmt, "idss", $userId, $totalPrice, $referenceId, $location);
    mysqli_stmt_execute($stmt);
    $orderId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Save each component to the OrderItems table
    foreach ($selectedComponents as $component) {
        $stmt = mysqli_prepare($conn, "INSERT INTO OrderItems (order_id, component_id, quantity, price) VALUES (?, ?, 1, ?)");
        mysqli_stmt_bind_param($stmt, "iid", $orderId, $component['component_id'], $component['price']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($paymentMethod === 'esewa') {
        // Redirect to Sewa Gateway payment
        $sewaGatewayUrl = 'https://uat.esewa.com.np/epay/main';
        $sewaMerchantId = 'EPAYTEST'; // 
        $sewaSecretKey = '8gBm/:&EnhH.1/q';
        $return_url = 'http://localhost:8080/confirm_payment.php';

        // Generate payment request data
        $paymentData = [
            'scd' => $sewaMerchantId,
            'tAmt' => $totalPrice,
            'amt' => $totalPrice,
            'txAmt' => 0,
            'pdc' => 0,
            'psc' => 0,
            'pid' => $orderId,
            'su' => "{$return_url}?q=su&oid={$orderId}",
            'fu' => "{$return_url}?q=fu&oid='{$orderId}"
        ];

        // Create a form and submit it
        echo '<!DOCTYPE html>
     <html lang="en">
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Redirecting to Sewa Gateway</title>
     </head>
     <body onload="document.forms[0].submit()">
         <form action="' . htmlspecialchars($sewaGatewayUrl) . '" method="post">';

        foreach ($paymentData as $key => $value) {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }

        echo '</form>
     </body>
     </html>';
        exit();
    } elseif ($paymentMethod === 'stripe') {
        require 'stripe-config.php'; // Include the Stripe configuration file

        try {
            // Create a new Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'npr', // Use your currency code (e.g., 'usd' or 'npr')
                            'product_data' => [
                                'name' => 'PC Build Order',
                            ],
                            'unit_amount' => $totalPrice * 100, // Amount in cents (or paisa for NPR)
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => 'http://localhost:8080/confirm_payment.php?session_id={CHECKOUT_SESSION_ID}', // Replace with your success URL
                'cancel_url' => 'http://localhost:8080/checkout', // Replace with your cancel URL
            ]);

            // Redirect to Stripe Checkout page
            header('Location: ' . $session->url);
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            exit();
        }
    } else {
        echo '<script>
              alert("Your order has been placed successfully. Please keep the cash ready on delivery.");
              window.location.href = "/index";
          </script>';
        exit();
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PC Craft</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 2rem;
            color: #007bff;
            border-bottom: 1px solid #111;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
        }

        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.25rem;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
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

        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Checkout</h1>
        </div>

        <form method="POST" action="checkout.php">
            <?php if (!empty($error)): ?>
                <p id="error"><?= $error ?></p>
            <?php endif; ?>
            <h2>Shipping Address</h2>
            <label for="address">Address</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" maxlength="10" required>

            <label for="city">City</label>
            <input type="text" id="city" name="city" required>

            <h2>Order Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Component</th>

                        <th>Brand</th>
                        <th>Model</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($selectedComponents as $component): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($component['name']); ?></td>

                            <td><?php echo htmlspecialchars($component['brand']); ?></td>
                            <td><?php echo htmlspecialchars($component['model']); ?></td>
                            <td>NRS.<?php echo htmlspecialchars($component['price']); ?> </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total Price:</strong></td>
                        <td colspan="2" id="total-price"><strong>NRS.
                                <?php echo number_format($_SESSION['totalPrice'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>

            <h2>Delivery Location</h2>
            <div id="map"></div>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <h2>Payment Method</h2>
            <label>
                <input type="radio" name="payment_method" value="esewa" checked> Pay via eSewa
                <img src="images/esewa-logo.png" alt="eSewa Logo"
                    style="width: 100px; height: auto; vertical-align: middle;">
            </label>
            <br>
            <label>
                <input type="radio" name="payment_method" value="stripe">Stripe
                <img src="images/stripe-logo.png" alt="Stripe Logo"
                    style="width: 100px; height: auto; vertical-align: middle;">
            </label>
            <br>
            <label>
                <input type="radio" name="payment_method" value="cod"> Cash on Delivery
                <img src="images/cod.jpg" alt="COD Logo" style="width: 100px; height: auto; vertical-align: middle;">
            </label>

            <button type="submit">Proceed to Payment</button>
        </form>
        </d>
        <?php include 'includes/footer.php'; ?>
</body>
<script>
    // Initialize the map
    const map = L.map('map').setView([27.7172, 85.3240], 13); // Default to Kathmandu, Nepal

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add a marker with draggable functionality
    let marker = L.marker([27.7172, 85.3240], { draggable: true }).addTo(map);

    // Function to update hidden inputs
    function updateInputs(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    }

    // Try to get the user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;

                // Center the map and move the marker to the current location
                map.setView([latitude, longitude], 15);
                marker.setLatLng([latitude, longitude]);

                // Update the hidden inputs
                updateInputs(latitude, longitude);

                alert(`Current location found: Latitude ${latitude.toFixed(6)}, Longitude ${longitude.toFixed(6)}`);
            },
            () => {
                alert('Unable to access your location. Using default location.');
            }
        );
    } else {
        alert('Geolocation is not supported by your browser. Using default location.');
    }

    // Update hidden inputs when the marker is dragged
    marker.on('dragend', function (e) {
        const { lat, lng } = e.target.getLatLng();
        updateInputs(lat, lng);
        alert(`Location updated: Latitude ${lat.toFixed(6)}, Longitude ${lng.toFixed(6)}`);
    });

    // Update marker position when the map is clicked
    map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        marker.setLatLng([lat, lng]);
        updateInputs(lat, lng);
        alert(`Location updated: Latitude ${lat.toFixed(6)}, Longitude ${lng.toFixed(6)}`);
    });
</script>

</html>