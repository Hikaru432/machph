<?php
session_start();
include 'config.php';

// Check if the manager is already logged in
if (isset($_SESSION['manager_id'])) {
    header('location:homemanager.php');
    exit();
}

// Handle manager registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert manager details into the database
    $insertSql = "INSERT INTO manager (email, username, password, role) VALUES ('$email', '$username', '$hashedPassword', 'manager')";

    if ($conn->query($insertSql)) {
        echo "Manager registration successful. You can now log in.";
        // Optionally, you can redirect the manager to the login page after successful registration.
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Registration</title>
</head>
<body>
    <h2>Manager Registration</h2>
    <form method="post" action="">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>

        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>

        <input type="submit" value="Register">
    </form>
</body>
</html>
