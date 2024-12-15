<?php
include 'config.php';

// Get the query parameters
$request_id = $_GET['request_id'];

// Fetch the chat messages
$query = "
    SELECT cm.sender_id, u.firstname, u.lastname, cm.message, cm.sent_at
    FROM chat_messages cm
    LEFT JOIN users u ON cm.sender_id = u.id
    WHERE cm.request_id = '$request_id'
    ORDER BY cm.sent_at ASC";

$result = mysqli_query($conn, $query);

// Check if there are messages
if (mysqli_num_rows($result) > 0) {
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'sender_name' => $row['firstname'] . ' ' . $row['lastname'],
            'message' => $row['message'],
            'sent_at' => $row['sent_at']
        ];
    }

    echo json_encode($messages);
} else {
    echo json_encode([]);
}
?>
