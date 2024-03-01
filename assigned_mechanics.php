<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $mechanic_id = $_POST['mechanic_id'];

    // Update the car record with the assigned mechanic
    $update_query = "UPDATE car SET mechanic_id = $mechanic_id WHERE user_id = $user_id AND car_id = $car_id";
    $result = mysqli_query($conn, $update_query);

    if ($result) {
        // Success message
        echo "Mechanic assigned successfully!";
    } else {
        // Error message
        echo "Error assigning mechanic: " . mysqli_error($conn);
    }
}
?>
