<?php
include 'config.php';

session_start();
$user_id = $_SESSION['user_id']; 
// Check if user_id is set
if (empty($user_id)) {
    echo json_encode(['error' => 'User ID not found in session.']);
    exit;
}

// Prepare the query
$query = "SELECT 
              car.car_id, 
              car.carmodel, 
              car.bodyno, 
              autoshop.companyname,
              approvals.status,
              approvals.reason
          FROM 
              car 
          JOIN 
              approvals ON car.car_id = approvals.car_id 
          JOIN 
              user ON user.id = approvals.user_id 
          JOIN 
              autoshop ON autoshop.companyid = car.companyid 
          WHERE 
              user.id = '$user_id'";

$result = mysqli_query($conn, $query);

// Initialize an array to hold car data
$cars = [];

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cars[] = $row;
    }
} else {
    echo json_encode(['error' => 'Query Error: ' . mysqli_error($conn)]);
    exit;
}

// Return the JSON-encoded array
echo json_encode($cars);
?>
