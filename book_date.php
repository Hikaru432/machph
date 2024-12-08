<?php
session_start();
include 'config.php';

// Retrieve POST data
$user_id = $_POST['user_id'];
$date = $_POST['date'];
$car_id = $_POST['car_id'];
$companyname = $_POST['companyname']; // Retrieve companyname from POST

// Check for existing bookings
$checkQuery = "SELECT * FROM bookings WHERE date = '$date' AND user_id = '$user_id' AND car_id = '$car_id' AND companyname = '$companyname'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    // Insert new booking
    $insertQuery = "INSERT INTO bookings (user_id, date, car_id, companyname, booking_date) VALUES ('$user_id', '$date', '$car_id', '$companyname', 1)";
    if (mysqli_query($conn, $insertQuery)) {
        echo "Booking successful!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // Update existing booking
    $updateQuery = "UPDATE bookings SET booking_date = booking_date + 1 WHERE date = '$date' AND user_id = '$user_id' AND car_id = '$car_id' AND companyname = '$companyname'";
    if (mysqli_query($conn, $updateQuery)) {
        echo "Booking updated!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Check booking count
$checkCountQuery = "SELECT booking_date FROM bookings WHERE date = '$date' AND user_id = '$user_id' AND companyname = '$companyname'";
$checkCountResult = mysqli_query($conn, $checkCountQuery);
$booking = mysqli_fetch_assoc($checkCountResult);

if ($booking && $booking['booking_date'] >= 5) {
    echo "Maximum booking limit reached for this date!";
    exit();
}

// Proceed with booking logic...
echo "User ID: $user_id, Date: $date, Company Name: $companyname"; // Added to see what's being sent
?>
