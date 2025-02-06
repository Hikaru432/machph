<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form was submitted with the "approve" button
    if (isset($_POST["approve"])) {
        // Get the mechanic ID from the form data
        $mechanic_id = $_POST["mechanic_id"];
    
        // Perform the necessary action to approve the mechanic
        $update_query = "UPDATE mechanic SET apply = 'approve' WHERE mechanic_id = $mechanic_id";
        $result = mysqli_query($conn, $update_query);
    
        if ($result) {
            // Mechanic approved successfully
            $_SESSION['message'] = "Mechanic approved successfully.";
            echo "<script>alert('Mechanic approved successfully.')</script>";
            header("Location: admin.php"); // Redirect to the admin page
            exit();
        } else {
            // Error approving mechanic
            $_SESSION['error'] = "Failed to approve mechanic: " . mysqli_error($conn);
            header("Location: admin.php"); // Redirect to the admin page
            exit();
        }
    } elseif (isset($_POST["decline"])) {
        // Get the mechanic ID from the form data
        $mechanic_id = $_POST["mechanic_id"];
    
        // Perform the necessary action to decline the mechanic
        $update_query = "UPDATE mechanic SET apply = 'decline' WHERE mechanic_id = $mechanic_id";
        $result = mysqli_query($conn, $update_query);
    
        if ($result) {
            // Mechanic declined successfully
            $_SESSION['message'] = "Mechanic declined successfully.";
            echo "<script>alert('Mechanic declined successfully.')</script>";
            header("Location: admin.php"); // Redirect to the admin page
            exit();
        } else {
            // Error declining mechanic
            $_SESSION['error'] = "Failed to decline mechanic: " . mysqli_error($conn);
            header("Location: admin.php"); // Redirect to the admin page
            exit();
        }
    }
} else {
    // Redirect to some error page if accessed directly without POST request
    header('location: error.php');
    exit();
}
?>
