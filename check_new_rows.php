<?php
include 'config.php';

// Assuming you have a last checked timestamp stored somewhere, adjust this according to your actual implementation
$lastCheckedTimestamp = isset($_SESSION['last_checked_timestamp']) ? $_SESSION['last_checked_timestamp'] : 0;

// Retrieve new rows since the last checked timestamp
$newRowsQuery = mysqli_query($conn, "SELECT car_id FROM car WHERE created_at > '$lastCheckedTimestamp'");

if (!$newRowsQuery) {
    die('Error in querying new rows: ' . mysqli_error($conn));
}

$newRows = array();
while ($row = mysqli_fetch_assoc($newRowsQuery)) {
    $newRows[] = $row['car_id'];
}

// Update last checked timestamp to current time
$_SESSION['last_checked_timestamp'] = time();

// Return new rows as JSON
echo json_encode($newRows);
?>
