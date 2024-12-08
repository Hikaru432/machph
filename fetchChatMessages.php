<?php
include 'config.php';

$request_id = $_GET['request_id'];

// Fetch chat messages for the specific request
$query = "
    SELECT c.message, u.firstname AS sender_name
    FROM companychat c
    JOIN user u ON c.sender_id = u.id
    WHERE c.request_id = $request_id
    ORDER BY c.timestamp ASC
";
$result = mysqli_query($conn, $query);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
