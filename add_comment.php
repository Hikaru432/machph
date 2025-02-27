<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $comment = $_POST['comment'];
    
    // Get company information
    $company_query = "SELECT companyname FROM autoshop WHERE companyid = ?";
    $stmt = mysqli_prepare($conn, $company_query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['companyid']);
    mysqli_stmt_execute($stmt);
    $company_result = mysqli_stmt_get_result($stmt);
    $company_data = mysqli_fetch_assoc($company_result);

    // Insert the new comment
    $stmt = mysqli_prepare($conn, "INSERT INTO action (request_id, action_type, comments, companyname) VALUES (?, 'comment', ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $request_id, $comment, $company_data['companyname']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 