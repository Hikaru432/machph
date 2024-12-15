<?php
session_start();
include 'config.php';

// Check if the form data is submitted
if (isset($_POST['car_id'], $_POST['message'])) {
    $car_id = $_POST['car_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Get the sender from session (user or mechanic)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // User is sending the message
        $sender = 'user';
        $mechanic_id = 1; // Mechanic ID needs to be dynamically set (based on car_id or logic)
        $autoshop_id = 1; // Autoshop ID needs to be dynamically set
    } elseif (isset($_SESSION['mechanic_id'])) {
        $mechanic_id = $_SESSION['mechanic_id']; // Mechanic is sending the message
        $sender = 'mechanic';
        $user_id = 1; // Placeholder for user ID, needs to be dynamically set
        $autoshop_id = 1; // Autoshop ID needs to be dynamically set
    } else {
        // Return an error if neither user nor mechanic is logged in
        echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
        exit;
    }

    // Insert the new message into the chatmechanic table
    $query = "INSERT INTO chatmechanic (user_id, car_id, mechanic_id, autoshop_id, message, sender)
              VALUES ($user_id, $car_id, $mechanic_id, $autoshop_id, '$message', '$sender')";

    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if (!$result) {
        die('Error in saving chat message: ' . mysqli_error($conn));
    }

    // Return success response
    echo json_encode(['success' => true]);
} else {
    // Return error response if the required fields are missing
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
}
?>
