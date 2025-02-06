<?php
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = $_POST['service_id'];

    // First, get the user_id, car_id, and companyname associated with the service record
    $service_query = "SELECT user_id, car_id FROM service WHERE car_id = '$serviceId'";
    $service_result = mysqli_query($conn, $service_query);
    
    if ($service_row = mysqli_fetch_assoc($service_result)) {
        $userId = $service_row['user_id'];
        $carId = $service_row['car_id'];

        // Now delete the service record
        $delete_service_query = "DELETE FROM service WHERE car_id = '$serviceId'";
        if (mysqli_query($conn, $delete_service_query)) {
            // Set companyid to NULL in user table
            $update_user_query = "UPDATE user SET companyid = NULL WHERE id = '$userId'";
            mysqli_query($conn, $update_user_query);

            // Set companyid to NULL in car table
            $update_car_query = "UPDATE car SET companyid = NULL WHERE car_id = '$carId'";
            mysqli_query($conn, $update_car_query);

            // Now delete the associated booking record
            $delete_booking_query = "DELETE FROM bookings WHERE car_id = '$carId' AND user_id = '$userId'";
            if (mysqli_query($conn, $delete_booking_query)) {
                // Successfully deleted booking
                echo json_encode(['status' => 'success']);
            } else {
                // Error deleting booking
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete booking.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete service.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Service not found.']);
    }
}
?>
