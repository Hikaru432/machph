<?php
session_start(); // Start session to access session variables
include 'config.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Debug: Check what data is received
file_put_contents('php://stderr', print_r($data, true)); // Log data for debugging

// Check if the data is set and valid
if (isset($data['request_id']) && isset($data['branch_id']) && isset($data['message'])) {
    $request_id = $data['request_id'];
    $branch_id = $data['branch_id'];
    $message = mysqli_real_escape_string($conn, $data['message']); // Protect against SQL injection

    // Fetch the sender's company ID from the session
    $company_id = $_SESSION['company_id']; // Get company_id from session
    
    // Check if company_id is set in the session
    if (!$company_id) {
        echo json_encode(['status' => 'error', 'error' => 'No company ID in session']);
        exit;
    }

    // The sender's company ID (from session) is used as sender_id
    $sender_id = $company_id;

    // Assuming you have a `chat_messages` table to store chat history
    $query = "
        INSERT INTO chat_messages (request_id, company_id, branch_id, sender_id, message, sent_at)
        VALUES ('$request_id', '$company_id', '$branch_id', '$sender_id', '$message', NOW())";

    // Debug: Log the SQL query
    file_put_contents('php://stderr', "SQL Query: $query\n");

    // Execute the query and handle errors
    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        // Capture and log the error message from the database
        $error_message = mysqli_error($conn);
        file_put_contents('php://stderr', "Error: $error_message\n");
        echo json_encode(['status' => 'error', 'error' => $error_message]);
    }
} else {
    // If the necessary data is missing, log the error
    file_put_contents('php://stderr', "Missing data: request_id, branch_id, or message\n");
    echo json_encode(['status' => 'error', 'error' => 'Invalid data']);
}
?>

<script>fetch('sendChatMessages.php', {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(responseData => {
    if (responseData.status === 'success') {
        console.log('Message sent successfully');
    } else {
        console.error('Failed to send message:', responseData.error);
    }
})
.catch(error => {
    console.error('Error during request:', error);
});
</script>
