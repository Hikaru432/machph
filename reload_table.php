<?php
include 'config.php';

// Assuming $lastUpdateTime is a timestamp representing the last time the data was updated
// You would need to implement this logic to track the last update time in your application

// Adjust the SQL query to fetch only records that have been added or modified since the last update
$car_select = mysqli_query($conn, "SELECT car.*, user.name as username, validation.status as validation_status, validation.comment
                                    FROM car 
                                    JOIN user ON car.user_id = user.id
                                    LEFT JOIN validation ON car.user_id = validation.user_id AND car.car_id = validation.car_id
                                    WHERE car.added_at > '$lastUpdateTime'");

if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}

$output = '';

while ($row = mysqli_fetch_assoc($car_select)) {
    $output .= '<tr id="row-'.$row['car_id'].'">';
    $output .= '<td>'.isset($row['username']) ? $row['username'] : ''.'</td>';
    $output .= '<td>'.isset($row['manufacturer']) ? $row['manufacturer'] : ''.'</td>';
    $output .= '<td>'.isset($row['plateno']) ? $row['plateno'] : ''.'</td>';
    $output .= '<td>';
    if ($row['validation_status'] === 'invalid') {
        $output .= '<span class="invalid-label">Invalid</span> - '.(isset($row['comment']) ? $row['comment'] : '');
    } elseif ($row['validation_status'] === 'valid') {
        $output .= 'Valid';
    } else {
        $output .= 'Not determined yet';
    }
    $output .= '</td>';
    $output .= '<td><a href="machidentify.php?user_id='.(int)$row['user_id'].'&car_id='.(int)$row['car_id'].'" class="btn btn-primary view-profile-btn">View Profile</a></td>';
    $output .= '</tr>';
}

echo $output;
?>
