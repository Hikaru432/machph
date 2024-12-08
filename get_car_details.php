<?php
include 'config.php';

$carId = $_GET['car_id'];

$query = "SELECT autoshop.companyname, car.carmodel, mechanic.firstname, approvals.status, approvals.reason 
          FROM approvals 
          JOIN car ON approvals.car_id = car.car_id 
          JOIN mechanic ON approvals.mechanic_id = mechanic.mechanic_id 
          JOIN autoshop ON car.companyid = autoshop.companyid 
          WHERE car.car_id = '$carId'";

$result = mysqli_query($conn, $query);
$carDetails = mysqli_fetch_assoc($result);

echo json_encode($carDetails);
?>
