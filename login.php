<?php

include 'config.php';
session_start();

if (isset($_POST['submit'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = mysqli_real_escape_string($conn, md5($_POST['password']));

    // Check if the user is a regular user
    $selectUser = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email' AND password = '$pass'") or die('query failed');

    if (mysqli_num_rows($selectUser) > 0) {
        $row = mysqli_fetch_assoc($selectUser);

        // Set user role to a variable
        $user_role = $row['role'];

        $_SESSION['user_id'] = $row['id'];

        // Redirect based on the user role
        switch ($user_role) {
            case 'user':
                header('Location: home.php');
                break;
            case 'mechanic':
                header('Location: homemechanic.php');
                break;
            case 'manager':
                header('Location: homemanager.php');
                break;
            case 'admin':
                header('Location: admin.php');
                break;
            default:
                // Handle other roles or unexpected values
                break;
        }

        exit();
    } else {
        // If not a regular user, check if it's a manager
        $selectManager = mysqli_query($conn, "SELECT * FROM manager WHERE email = '$email' AND password = '$pass'") or die('query failed');

        if (mysqli_num_rows($selectManager) > 0) {
            $rowManager = mysqli_fetch_assoc($selectManager);

            $_SESSION['manager_id'] = $rowManager['id'];

            header('Location: homemanager.php');
            exit();
        } else {
            $message[] = 'Incorrect email or password!';
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
            <input type="email" name="email" placeholder="Enter email" class="box" required>
            <input type="password" name="password" placeholder="Enter password" class="box" required>
            <input type="submit" name="submit" value="Login now" class="btn">
            <!-- <p>Log-in to Company? <a href="clogin.php">Proceed</a></p> -->
            <p>Don't have an account? <a href="register.php">Register now</a></p>
        </form>

    </div>

</body>

</html>
