<?php
session_start();
include 'config.php';

if (isset($_POST['user_id']) && isset($_POST['progress'])) {
    $userId = $_POST['user_id'];
    $progress = $_POST['progress'];

    // Insert progress into progress table
    $insertQuery = "INSERT INTO progress (user_id, progress_percentage) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'is', $userId, $progress);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Progress saved successfully.";
    } else {
        echo "Error saving progress.";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Incomplete data received.";
}
?>
