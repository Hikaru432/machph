<?php
include 'config.php';

$data = json_decode(file_get_contents("php://input"));

$request_id = $data->request_id;
$message = $data->message;
$sender_id = 1; 
$receiver_id = 2; 
// Insert the chat message into the database
$query = "
    INSERT INTO companychat (request_id, sender_id, receiver_id, message)
    VALUES ($request_id, $sender_id, $receiver_id, '$message')
";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
