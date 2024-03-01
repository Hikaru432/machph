<?php
// Connect to your database

// Assume $_POST contains the necessary data sent from the AJAX request
$mechanic_id = $_POST['mechanic_id'];
$user_id = $_POST['user_id'];
$car_id = $_POST['car_id'];
$progress = $_POST['progress'];

// Insert progress data into the database along with mechanic ID
$query = "INSERT INTO progress_table (mechanic_id, user_id, car_id, progress) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'iiid', $mechanic_id, $user_id, $car_id, $progress);
mysqli_stmt_execute($stmt);

if(mysqli_stmt_affected_rows($stmt) > 0) {
    echo "Progress saved successfully.";
} else {
    echo "Error saving progress.";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
