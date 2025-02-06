<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];

    // Assuming you have a form field named 'approval_status' in your HTML form
    $approval_status = isset($_POST['approval_status']) ? $_POST['approval_status'] : '0';

    // Check if the user and car combination already exists in the approvals table
    $check_approval_query = "SELECT * FROM approvals WHERE user_id = '$user_id' AND car_id = '$car_id'";
    $result_check_approval = mysqli_query($conn, $check_approval_query);

    if ($result_check_approval) {
        if (mysqli_num_rows($result_check_approval) > 0) {
            if (isset($_POST['approval_status'])) {
                $approval_status = $_POST['approval_status'];
            
                // Update 'approvals' table with the approval status
                $update_approvals_query = "UPDATE approvals SET status = '$approval_status' WHERE user_id = '$user_id' AND car_id = '$car_id'";
                
                $result_approvals_update = mysqli_query($conn, $update_approvals_query);
            
                if ($result_approvals_update) {
                    echo "Approval status updated successfully!";
                } else {
                    echo "Error updating approval status: " . mysqli_error($conn);
                }
            } else {
                // Handle the case where 'approval_status' is not set
                echo "Error: 'approval_status' not set in the form.";
            }
        } else {
            // Handle the case where the user and car combination doesn't exist
            echo "Error: Record not found in the approvals table.";
        }
    } else {
        // Handle the case where there is an error in the query
        echo "Error checking approvals: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
