<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $approval_status = $_POST['approval_status'];
    
    // Insert into 'approvals' table
    $insert_into_approvals_query = "INSERT INTO approvals (user_id, car_id, status) VALUES ('$user_id', '$car_id', '$approval_status')";
    
    $result_approvals_insert = mysqli_query($conn, $insert_into_approvals_query);

    if ($result_approvals_insert) {
        echo "Approval status successfully recorded!";
    } else {
        echo "Error recording approval status: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
