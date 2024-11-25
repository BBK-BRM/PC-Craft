<?php
require 'includes/url.php';
require 'includes/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {

    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    // $role = 'admin';


    if ($_POST['password'] !== $_POST['cpassword']) {
        $error = 'password doesm\'t match';
    }
    // elseif(!preg_match('/^([9]{1})+([87]{1})[0-9]{8}/',$phone)){
    //     $error = 'enter valid phone number';
    // }
    // elseif(!preg_match('/^[A-Za-z][A-Za-z0-9_]{7,29}$/',$username)){
    //     $error = 'username must have 8 characters and can have character,number and uderscore(_) ';
    // }
    else {
        $conn = getDB();

        //checking if user already exits or not.
        $sql_user_check = 'SELECT username,email FROM Users WHERE username=? OR email=?';
        $stmt = mysqli_prepare($conn, $sql_user_check);
        if ($stmt == false) {
            echo mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) > 0) {
                    $error = 'user is already exist';
                } else {
                    //if user not exits then
                    $sql = 'INSERT INTO Users(username,email,password,created_at, updated_at) VALUES(?,?,?,NOW(),NOW())';
                    $stmt = mysqli_prepare($conn, $sql);
                    if ($stmt == false) {
                        echo mysqli_error($conn);
                    } else {
                        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                        mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashed_pass);
                        if (mysqli_stmt_execute($stmt)) {
                            header('refresh:3;url=/login.php');
                        } else {
                            echo mysqli_stmt_error($stmt);
                        }
                    }

                }
            }
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/login-signup.css">
</head>

<body>
    <main>
        <div class="form-container">
            <h3>Signup form</h3>
            <form method="post">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" placeholder="example@example.com" required>

                <label for="username">Username *</label>
                <input type="text" name="username" id="username" placeholder="Username" required>

                <label for="password">Password *</label>
                <input type="password" name="password" id="password" placeholder="Password" required>

                <label for="confirm_password"> confirm Password *</label>
                <input type="password" name="cpassword" id="confirm_password" placeholder="Password" required>

                <?php if (!empty($error)): ?>
                    <p id="error"><?= $error ?></p>
                <?php endif; ?>
                <div class="btn-container">
                    <button id="signup" name="signup">Signup</button>
                    <button id="cancel-btn"><a href="login.php">Cancel</a></button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>