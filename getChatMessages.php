<?php
// getChatMessages.php
include 'config.php';

$request_id = $_GET['request_id'];
$sender_id = $_GET['sender_id'];

// Query to fetch messages between the main company and the selected sender
$query = "
    SELECT cm.message, cm.sent_at, a.companyname AS sender_name
    FROM chat_messages cm
    JOIN autoshop a ON cm.sender_id = a.companyid
    WHERE cm.request_id = '$request_id' AND cm.sender_id = '$sender_id'
    ORDER BY cm.sent_at ASC";

$result = mysqli_query($conn, $query);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
