<?php 
include 'config.php'; 

// Get the last checked timestamp from the session 
$last_checked = isset($_SESSION['last_checked']) ? (int)$_SESSION['last_checked'] : 0; 

// Get the current timestamp 
$current_time = time(); 
 
$new_cars_query = mysqli_query($conn, "SELECT COUNT(*) FROM car WHERE added_at > FROM_UNIXTIME($last_checked)"); 
$new_cars = mysqli_fetch_row($new_cars_query)[0]; 

$_SESSION['last_checked'] = $current_time; 

ob_start(); 
include 'table_content.php'; 
$html = ob_get_clean(); 

echo json_encode(['new_cars' => $new_cars, 'html' => $html]); 
?>
