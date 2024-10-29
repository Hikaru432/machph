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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body style="background-color: #6d0222;">

    <div class="form-container">

        <form action="" method="post" enctype="multipart/form-data">
            <h3>Login</h3>
            <?php
            if (isset($message)) {
                foreach ($message as $message) {
                    echo '<div class="message">' . $message . '</div>';
                }
            }
            ?>
            <input type="text" name="name" placeholder="Enter name" class="box" required>
            <input type="password" name="password" placeholder="Enter password" class="box" required>
            <input type="submit" name="submit" value="Login now" class="btn">
            <p>Don't have an account? <a href="register.php">Register now</a></p>
        </form>

    </div>

</body>

</html>
