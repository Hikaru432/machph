<?php
session_start();
include 'config.php';

// Check if the car_id is passed via GET
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    // Query to fetch chat messages between the user and mechanic
    $query = "
        SELECT cm.message, cm.sender, cm.timestamp, m.firstname AS mechanic_name
        FROM chatmechanic cm
        LEFT JOIN mechanic m ON cm.mechanic_id = m.mechanic_id
        WHERE cm.car_id = $car_id AND (cm.user_id = $user_id OR cm.car_id = $car_id)
        ORDER BY cm.timestamp ASC
    ";

    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if (!$result) {
        die('Error in loading chat messages: ' . mysqli_error($conn));
    }

    // Prepare the messages for output
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'message' => $row['message'],
            'sender' => $row['sender'],
            'timestamp' => $row['timestamp'],
            'mechanic_name' => $row['mechanic_name']
        ];
    }

    // Return the messages as a JSON response
    echo json_encode(['messages' => $messages]);
}
?>
