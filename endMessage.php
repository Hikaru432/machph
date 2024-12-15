<?php
include 'config.php';
$carId = $_POST['carId'];
$mechanicId = $_POST['mechanicId'];
$message = $_POST['message'];
$sender = $_POST['sender'];
$userId = $_SESSION['user_id']; // Assuming user_id is stored in session

$query = "
    INSERT INTO chatmechanic (user_id, car_id, mechanic_id, message, sender)
    VALUES (?, ?, ?, ?, ?)
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiiss", $userId, $carId, $mechanicId, $message, $sender);
$stmt->execute();
echo "Message sent successfully";
?>
