<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $approvals_query = "SELECT u.name, c.carmodel, a.status
                        FROM users u
                        INNER JOIN approvals a ON u.user_id = a.user_id
                        INNER JOIN cars c ON a.car_id = c.car_id";
    
    $result_approvals = mysqli_query($conn, $approvals_query);

    $approvals = array();

    while ($row = mysqli_fetch_assoc($result_approvals)) {
        $approvals[] = $row;
    }

    echo json_encode($approvals);
} else {
    echo "Invalid request.";
}
?>
