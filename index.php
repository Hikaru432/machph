<?php

include 'config.php';
session_start();

if (isset($_POST['submit'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']); 

    // Debugging output for user authentication
    error_log("Attempting to authenticate user: $name");

    // Check if the user is a regular user (using md5 hashed password)
    $hashed_pass = md5($pass);
    $selectUser = mysqli_query($conn, "SELECT * FROM user WHERE name = '$name' AND password = '$hashed_pass'") or die('User query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($selectUser) > 0) {
        $row = mysqli_fetch_assoc($selectUser);

        // Set user role to a variable
        $user_role = $row['role'];

        $_SESSION['user_id'] = $row['id'];

        // Redirect based on the user role
        switch ($user_role) {
            case 'user':
                header('Location: home.php');
                exit();
            default:
                // Handle other roles or unexpected values
                break;
        }
    } else {
        // Debugging output
        error_log("Failed to authenticate as a regular user: $name");

        // Debugging output for autoshop authentication
        error_log("Attempting to authenticate autoshop: $name with provided password");

        // If not a regular user or manager, check if it's an autoshop (using plaintext password)
        $selectAutoshop = mysqli_query($conn, "SELECT * FROM autoshop WHERE cname = '$name' AND cpassword = '$pass'") or die('Autoshop query failed: ' . mysqli_error($conn));

        if (mysqli_num_rows($selectAutoshop) > 0) {
            $rowAutoshop = mysqli_fetch_assoc($selectAutoshop);

            $_SESSION['companyid'] = $rowAutoshop['companyid'];

            header('Location: admin.php');
            exit();
        } else {
            // Admin authentication
            if ($name === 'admin' && $pass === 'admin') {
                $_SESSION['admin'] = true;
                header('Location: machphadmin.php');
                exit();
            }

            // Debugging output
            error_log("Failed to authenticate as an autoshop: $name");

            $message[] = 'Incorrect name or password!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            display: flex;
            width: 900px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .login-image {
            width: 50%;
            background: url('img/background.jpg') center/cover;
        }
        .login-form {
            width: 50%;
            padding: 40px;
            background: #fff;
        }
        .form-control {
            border-radius: 20px;
        }
        .btn-custom {
            background-color:rgb(173, 0, 52);
            border: none;
            border-radius: 20px;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #b3003698;
        }
        .text-custom {
            color:rgba(139, 0, 42, 0.82);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image"></div>
        <div class="login-form">
            <h3 class="text-center mb-4">Sign In</h3>
            <form action="" method="post">
                <?php if (isset($message)) { 
                    foreach ($message as $msg) {
                        echo '<div class="alert alert-danger">' . $msg . '</div>';
                    }
                } ?>
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="submit" class="btn btn-custom" style="color: white;">Sign In</button>
                <div class="d-flex justify-content-between mt-2">
                    <div>
                        <input type="checkbox" id="remember"> <label for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="text-custom">Forgot Password?</a>
                </div>
                <p class="text-center mt-3">Not a member? <a href="register.php" class="text-custom">Sign Up</a></p>
            </form>
        </div>
    </div>
</body>
</html>
