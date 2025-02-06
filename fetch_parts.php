<?php
// fetch_parts.php

// Assuming you have a database connection established
include 'config.php';

// Check if problem_id is provided
if(isset($_GET['problem_id'])) {
    $problem_id = $_GET['problem_id'];

    // Prepare and execute a query to fetch parts based on problem_id
    $query = "SELECT * FROM parts WHERE problem_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $problem_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Fetch parts and store them in an array
    $parts = [];
    while($row = mysqli_fetch_assoc($result)) {
        $parts[] = $row;
    }

    // Return parts data as JSON
    header('Content-Type: application/json');
    echo json_encode($parts);
    exit;
} else {
    // If problem_id is not provided, return an error response
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Problem ID is required'));
    exit;
}
?>
