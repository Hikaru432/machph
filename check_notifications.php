<?php
session_start();

// Simulate some notification data (replace this with your actual notification logic)
$notificationCount = rand(0, 10); // Generate a random notification count

// Check if there are new notifications
$hasNewNotifications = $notificationCount > 0;

// Respond with JSON data
$response = [
    'hasNewNotifications' => $hasNewNotifications,
    'notificationCount' => $notificationCount
];

header('Content-Type: application/json');
echo json_encode($response);
?>
