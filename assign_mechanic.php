    <?php
    include 'config.php';

    $response = ['success' => false, 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_POST['userId'];
        $carId = $_POST['carId'];
        $mechanicId = $_POST['mechanicId'];

        // Perform the assignment process here, insert a new record into the assignments table
        $insertQuery = "INSERT INTO assignments (user_id, car_id, mechanic_id) VALUES ($userId, $carId, $mechanicId)";
        $result = mysqli_query($conn, $insertQuery);

        if ($result) {
            $response['success'] = true;
        } else {
            $response['message'] = 'Error assigning mechanic: ' . mysqli_error($conn);
        }
    }

    echo json_encode($response);
    ?>
