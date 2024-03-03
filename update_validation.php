<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $status = $_POST['status'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    // Insert or update validation status and comment in the database
    $query = "INSERT INTO validation (user_id, car_id, status, comment) 
              VALUES (?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE status = VALUES(status), comment = VALUES(comment)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iiss', $user_id, $car_id, $status, $comment);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

?>
