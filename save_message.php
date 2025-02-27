<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'];
$requestId = $data['requestId'];
$role = $data['role'];

// Fetch companyid and user_id based on requestId
$request_query = "SELECT companyid, user_id FROM requestproduct WHERE id = ?";
$request_stmt = mysqli_prepare($conn, $request_query);
mysqli_stmt_bind_param($request_stmt, "i", $requestId);
mysqli_stmt_execute($request_stmt);
$request_result = mysqli_stmt_get_result($request_stmt);
$request_row = mysqli_fetch_assoc($request_result);

$companyid = $request_row['companyid'];
$user_id = $request_row['user_id'];

// Insert the message into the cashier table
$stmt = mysqli_prepare($conn, "INSERT INTO cashier (role, message, companyid, user_id) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssii", $role, $message, $companyid, $user_id);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'error' => mysqli_error($conn)]);
}
?>