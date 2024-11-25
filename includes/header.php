<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>PC Craft</title>
    <script>function dropmenu() {
            document.getElementById("dropdown").classList.toggle("show");
        }</script>
</head>

<body>
    <header>
        <nav>
            <a href="/">
                <img src="../images/logo-transparent.png" id="logo-nav" alt="Pc Craft">
            </a>
            <ul id="nav-links">
                <li class="nav-item"><a href="/" class="link"><span>Home</span></a></li>
                <li class="nav-item"><a href="build" class="link"><span>Build</span></a></li>
                <li class="nav-item"><a href="order" class="link"><span>Order</span></a></li>
                <li class="nav-item"><a href="about_us.php" class="link"><span>About Us</span></a></li>
            </ul>
        </nav>
        <div class="user">
            <p id="user" onclick="dropmenu()">
                <i class="material-icons">account_circle</i>
                <?php
                session_start();
                if (isset($_SESSION['username'])) {
                    echo $_SESSION['username'];
                }
                ?>
            </p>
            <div id="dropdown" class="dropdown-content">
                <a href="../profile.php">Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>
    <main>