<?php
session_start();
include 'config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract data from POST request
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $status = $_POST['status'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    // Insert or update validation status and comment in the database
    $query = "INSERT INTO validation (user_id, car_id, status, comment) 
              VALUES (?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE status = VALUES(status), comment = VALUES(comment)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiss', $user_id, $car_id, $status, $comment);
    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
