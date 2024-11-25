<?php
require 'includes/database.php';
include 'includes/header.php';
include 'includes/url.php';

// Check if user is logged in
if (!isset($_SESSION["is_logged_in"])) {
  redirect('/login');
}

// Fetch user details based on session user ID
$userId = $_SESSION['user_id'];
$conn = getDB();
$stmt = mysqli_prepare($conn, "SELECT * FROM Users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
  $username = htmlspecialchars($user['username']);
  $email = htmlspecialchars($user['email']);
  $createdAt = $user['created_at'];
  $role = $user['role'];
}

mysqli_stmt_close($stmt);

// Fetch user address if available
$addressStmt = mysqli_prepare($conn, "SELECT * FROM UserAddresses WHERE user_id = ?");
mysqli_stmt_bind_param($addressStmt, "i", $userId);
mysqli_stmt_execute($addressStmt);
$addressResult = mysqli_stmt_get_result($addressStmt);

if ($address = mysqli_fetch_assoc($addressResult)) {
  $addressText = htmlspecialchars($address['address']);
  $city = htmlspecialchars($address['city']);
  $phone = htmlspecialchars($address['phone']);
} else {
  $addressText = '';
  $city = '';
  $phone = '';
}

mysqli_stmt_close($addressStmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - PC Craft</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Profile Page Container */
    .profile-page {
      max-width: 600px;
      margin: 50px auto;
      padding: 20px;
      background-color: #f8f9fa;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      border-radius: 10px;
    }

    /* Header Styling */
    .profile-page h1 {
      text-align: center;
      color: #007bff;
      margin-bottom: 20px;
      font-size: 2rem;
    }

    /* Form Labels */
    .profile-page label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      font-size: 1rem;
      color: #333;
    }

    /* Input Fields */
    .profile-page input[type="text"],
    .profile-page input[type="email"],
    .profile-page input[type="tel"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ced4da;
      border-radius: 5px;
      font-size: 1rem;
      background-color: #fff;
    }

    /* Submit Button */
    .profile-page button[type="submit"] {
      width: 100%;
      padding: 15px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 1.25rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    /* Submit Button Hover State */
    .profile-page button[type="submit"]:hover {
      background-color: #0056b3;
    }

    /* Success Message */
    .profile-page .success-message {
      padding: 15px;
      background-color: #d4edda;
      color: #155724;
      margin-bottom: 20px;
      border-radius: 5px;
      border: 1px solid #c3e6cb;
      font-size: 1rem;
    }

    /* Error Messages */
    .profile-page .error-message p {
      padding: 10px;
      background-color: #f8d7da;
      color: #721c24;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #f5c6cb;
      font-size: 1rem;
    }

    /* Profile Page Mobile Responsive */
    @media (max-width: 768px) {
      .profile-page {
        max-width: 100%;
        padding: 15px;
      }

      .profile-page h1 {
        font-size: 1.75rem;
      }

      .profile-page button[type="submit"] {
        font-size: 1.1rem;
      }
    }
  </style>
</head>

<body>
  <div class="profile-page">
    <h1>User Profile</h1>

    <!-- Display success message if profile is updated -->
    <?php if (isset($_SESSION['profile_updated'])): ?>
      <div class="success-message">
        <?php echo $_SESSION['profile_updated']; ?>
      </div>
      <?php unset($_SESSION['profile_updated']); ?>
    <?php endif; ?>

    <form method="POST" action="update_profile.php">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>

      <label for="address">Address</label>
      <input type="text" id="address" name="address" value="<?php echo $addressText; ?>">

      <label for="city">City</label>
      <input type="text" id="city" name="city" value="<?php echo $city; ?>">

      <label for="phone">Phone Number</label>
      <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>">

      <h3>Account Details</h3>
      <p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>
      <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($createdAt)); ?></p>

      <button type="submit">Update Profile</button>
    </form>
  </div>

  <?php include 'includes/footer.php'; ?>
</body>

</html>