<?php
include 'config.php';

// Retrieve progress percentage and mechanic ID from POST data
$progress = $_POST['progress'];
$mechanicId = $_POST['mechanic_id'];

// Update or insert the progress percentage into the database
$query = "INSERT INTO progress (mechanic_id, progress_percentage) VALUES (?, ?) ON DUPLICATE KEY UPDATE progress_percentage = ?";
$stmt = mysqli_prepare($conn, $query);

// Bind parameters and execute the statement
mysqli_stmt_bind_param($stmt, 'ids', $mechanicId, $progress, $progress);
mysqli_stmt_execute($stmt);

// Check if the query was successful
if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo "Progress saved successfully.";
} else {
    echo "Error saving progress.";
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
