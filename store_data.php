<?php
session_start();

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $new_maintenance_item = $_POST['new_maintenance_item'];

    // Insert data into the 'maintenance' table (adjust the query accordingly)
    $insert_query = "INSERT INTO maintenance (user_id, car_id, maintenance_item) VALUES ('$user_id', '$car_id', '$new_maintenance_item')";
    $result = mysqli_query($conn, $insert_query);

    if ($result) {
        echo "Data successfully stored!";
    } else {
        echo "Error storing data: " . mysqli_error($conn);
    }
}
?>
