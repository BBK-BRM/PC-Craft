<?php
require '../includes/database.php';

// Function to calculate the distance between two points using Haversine formula
function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
{
  $earthRadius = 6371; // Earth radius in kilometers

  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $a = sin($latDelta / 2) * sin($latDelta / 2) +
    cos($latFrom) * cos($latTo) *
    sin($lonDelta / 2) * sin($lonDelta / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  return $earthRadius * $c; // Distance in kilometers
}

// Fetch user locations who have made orders from the database
$conn = getDB();
$stmt = $conn->prepare("
    SELECT DISTINCT u.user_id, u.username, o.location, o.user_id
    FROM users u
    JOIN orders o ON u.user_id = o.user_id
    WHERE o.location IS NOT NULL
");
$stmt->execute();
$result = $stmt->get_result();

$locations = [];
while ($row = $result->fetch_assoc()) {
  // Assuming location is in format "latitude,longitude"
  list($latitude, $longitude) = explode(',', $row['location']);
  $locations[$row['user_id']] = [
    'username' => $row['username'],
    'latitude' => floatval($latitude),
    'longitude' => floatval($longitude)
  ];
}

// Get the factory's coordinates (starting point)
$factory_lat = 27.7172; // Example latitude
$factory_lng = 85.3240; // Example longitude

// Add the factory location as the first element
$locations['factory'] = [
  'username' => 'Factory',
  'latitude' => $factory_lat,
  'longitude' => $factory_lng
];

// Nearest Neighbor Heuristic to approximate a TSP solution
function findNearestNeighborRoute($start_lat, $start_lng, $locations)
{
  $route = [];
  $visited = [];
  $current_lat = $start_lat;
  $current_lng = $start_lng;
  $total_distance = 0;

  while (count($visited) < count($locations)) {
    $nearest_location = null;
    $nearest_distance = PHP_INT_MAX;

    // Find the nearest unvisited location
    foreach ($locations as $id => $location) {
      if (!in_array($id, $visited)) {
        $distance = calculateDistance($current_lat, $current_lng, $location['latitude'], $location['longitude']);
        if ($distance < $nearest_distance) {
          $nearest_distance = $distance;
          $nearest_location = $location;
          $nearest_id = $id;
        }
      }
    }

    // Visit the nearest location
    $route[] = [
      'user_id' => $nearest_id,
      'username' => $nearest_location['username'],
      'latitude' => $nearest_location['latitude'],
      'longitude' => $nearest_location['longitude'],
      'distance' => $nearest_distance
    ];
    $total_distance += $nearest_distance;

    // Update current position
    $current_lat = $nearest_location['latitude'];
    $current_lng = $nearest_location['longitude'];

    // Mark as visited
    $visited[] = $nearest_id;
  }

  // Return to factory (round trip)
  $return_distance = calculateDistance($current_lat, $current_lng, $start_lat, $start_lng);
  $total_distance += $return_distance;

  $route[] = [
    'user_id' => 'Return to Factory',
    'username' => 'Factory',
    'latitude' => $start_lat,
    'longitude' => $start_lng,
    'distance' => $return_distance
  ];

  return ['route' => $route, 'total_distance' => $total_distance];
}

$optimized_route = findNearestNeighborRoute($factory_lat, $factory_lng, $locations);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User Orders Location Map - Optimized Route</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
  <style>
    #map {
      height: 600px;
      width: 90%;
      margin: auto;
    }
  </style>
</head>

<body>
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
  <div class="optimized_route_container">
    <h2>User Orders Location Map - Optimized Route</h2>
    <div id="map"></div>
  </div>
  <script>
    // Initialize the map centered on the factory location
    var map = L.map('map').setView([<?php echo $factory_lat; ?>, <?php echo $factory_lng; ?>], 12);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Factory marker
    var factoryMarker = L.marker([<?php echo $factory_lat; ?>, <?php echo $factory_lng; ?>])
      .addTo(map)
      .bindPopup('<b>Factory Location</b>')
      .openPopup();

    // Gather optimized route coordinates from PHP
    var waypoints = [
      L.latLng(<?php echo $factory_lat; ?>, <?php echo $factory_lng; ?>), // Start from the factory
      <?php foreach ($optimized_route['route'] as $location): ?>
                                        L.latLng(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>),
      <?php endforeach; ?>
            L.latLng(<?php echo $factory_lat; ?>, <?php echo $factory_lng; ?>) // Return to factory
    ];

    // Plot the route using Leaflet Routing Machine
    L.Routing.control({
      waypoints: waypoints,
      routeWhileDragging: false,
      createMarker: function (i, wp, nWps) {
        return L.marker(wp.latLng).bindPopup(i === 0 ? 'Factory' : (i === nWps - 1 ? 'Back to Factory' : `User Stop ${i}`));
      }
    }).addTo(map);
  </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>