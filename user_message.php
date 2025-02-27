<?php
// Include the database configuration file
include 'config.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract the data
$message = $data['message'] ?? '';
$user_id = $data['user_id'] ?? '';
$companyid = $data['companyid'] ?? '';
$role = $data['role'] ?? '';

// Validate the input
if (empty($message) || empty($user_id) || empty($companyid) || empty($role)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// Prepare the SQL statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO cashier (role, message, date, companyid, user_id) VALUES (?, ?, NOW(), ?, ?)");
$stmt->bind_param("ssii", $role, $message, $companyid, $user_id);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>