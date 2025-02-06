<?php
// Include your database connection file
include('config.php');

// Query to get the latest chat messages
$query = "SELECT * FROM chats ORDER BY timestamp ASC";
$result = mysqli_query($conn, $query);

// Check if there are any messages
if (mysqli_num_rows($result) > 0) {
    // Loop through the messages and display them
    while ($chat = mysqli_fetch_assoc($result)) {
        echo '<div class="' . ($chat['sender'] == 'mechanic' ? 'mechanic-message' : 'user-message') . '">';
        echo '<p class="mb-0">' . htmlspecialchars($chat['message']) . '</p>';
        echo '<small class="timestamp">' . date('F j, Y, g:i a', strtotime($chat['timestamp'])) . '</small>';
        echo '</div>';
    }
} else {
    echo '<p>No messages yet.</p>';
}

// Close the database connection
mysqli_close($conn);
?>
