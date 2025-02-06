<?php
include 'config.php';

// Fetch data from the `accomplishtask` table
$query = "
    SELECT 
        at.id, 
        at.progress_percentage, 
        at.car_id, 
        at.mechanic_id, 
        u.firstname AS user_firstname, 
        u.lastname AS user_lastname, 
        c.carmodel, 
        c.year, 
        m.firstname AS mechanic_firstname, 
        m.lastname AS mechanic_lastname
    FROM 
        accomplishtask at
    JOIN user u ON at.user_id = u.id
    JOIN car c ON at.car_id = c.car_id
    JOIN mechanic m ON at.mechanic_id = m.mechanic_id
    WHERE at.progress_percentage = 100"; // You can adjust this condition as per your needs

$result = mysqli_query($conn, $query);

// Check if there are results
if (mysqli_num_rows($result) > 0) {
    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = [
            'id' => $row['id'],
            'user_name' => $row['user_firstname'] . ' ' . $row['user_lastname'],
            'car_model' => $row['carmodel'],
            'mechanic_name' => $row['mechanic_firstname'] . ' ' . $row['mechanic_lastname'],
            'user_id' => $row['user_id'],
            'car_id' => $row['car_id'],
            'mechanic_id' => $row['mechanic_id'],
        ];
    }
    echo json_encode($tasks);
} else {
    echo json_encode([]);
}

mysqli_close($conn);
?>
