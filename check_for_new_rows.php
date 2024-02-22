<?php
include 'config.php';

// Query to check for new rows in the car table
$check_new_rows_query = mysqli_query($conn, "SELECT COUNT(*) as new_rows_count FROM car WHERE created_at > NOW() - INTERVAL 1 MINUTE");

if (!$check_new_rows_query) {
    die('Error checking for new rows: ' . mysqli_error($conn));
}

// Fetch the result
$row = mysqli_fetch_assoc($check_new_rows_query);
$new_rows_count = $row['new_rows_count'];

// Return the count of new rows
echo $new_rows_count;
?>
