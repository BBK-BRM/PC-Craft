<?php
require 'includes/url.php';
require 'includes/database.php';
session_start();
$error ='';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = getDB();

    $sql_user_check = 'SELECT * FROM Users WHERE username=? OR email=? ';
    $stmt = mysqli_prepare($conn, $sql_user_check);
    if ($stmt == false) {
        $error = 'SQL Error: ' . mysqli_error($conn);
    } else {
        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            if ($user && mysqli_num_rows($result) > 0) {
                $pass_check = password_verify($password, $user['password']);
                if ($pass_check) {
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['user_id'];
                    
                    if($user['role'] == 'admin'){
                        $_SESSION['user_role'] = $user['role'];
                        redirect('/admin/dashboard');
                        exit();
                    }else{
                        $_SESSION['user_role'] = $user['role'];
                        redirect('/'); 
                        exit();
                    }
                    
                } else {
                    $error = 'Incorrect password';
                }
            } else {
                $error = 'Invalid Username';
            }
        } else {
            $error = 'SQL Error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login-signup.css">
    <title>Login</title>
</head>

<body>
    <main class="container">
        <div class="login-desc">
            <h1>PC Craft</h1>
        </div>
        <div class="form-container">
            <h3>Login</h3>
            <form method="post">

                <label for="username">Username/Email</label>
                <input type="text" name="username" id="username" placeholder="Username or Email" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>

                <?php if (!empty($error)): ?>
                    <p id="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <button name="login">Login</button>
            </form>
            <p>Don't have an account?<a href="signup.php">Sign Up.</a></p>
        </div>
    </main>
</body>
</html>