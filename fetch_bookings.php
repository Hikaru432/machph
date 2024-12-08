<?php
session_start();
include 'config.php';

$user_id = $_GET['user_id'];
$car_id = $_GET['car_id']; // Get car_id from the request
$query = "
    SELECT b.*, u.firstname, u.lastname, c.carmodel
    FROM bookings b
    JOIN user u ON b.user_id = u.id
    JOIN car c ON b.car_id = c.car_id
    WHERE b.user_id = '$user_id' AND b.car_id = '$car_id'"; // Filter by user_id and car_id

$result = mysqli_query($conn, $query);
$output = '';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fullname = $row['firstname'] . ' ' . $row['lastname'];
        $carmodel = $row['carmodel'];
        $date = $row['date']; // Adjust according to your bookings table
        $count = $row['booking_date']; // This should be the count of bookings for that date

        // Determine the color based on booking count
        $color = 'green';
        if ($count >= 4 && $count <= 5) {
            $color = 'red';
        } elseif ($count == 3) {
            $color = 'yellow';
        }

        $output .= "<tr style='background-color: $color;'>
                        <td>$fullname</td>
                        <td>$carmodel</td>
                        <td>$date</td>
                        <td>$count</td>
                    </tr>";
    }
} else {
    $output .= '<tr><td colspan="4">No bookings found</td></tr>';
}

echo $output;

?>
