<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Retrieve repair job details
$car_id = $_POST['car_id']; // Make sure to validate and sanitize user inputs

// Fetch assigned mechanic details
$assigned_mechanic_query = "SELECT CONCAT_WS(' ', u.name) AS mechanic_name, m.jobrole
                            FROM mechanic m
                            INNER JOIN user u ON m.user_id = u.id
                            WHERE m.car_id = '$car_id'";
$assigned_mechanic_result = mysqli_query($conn, $assigned_mechanic_query);

if (!$assigned_mechanic_result) {
    die('Error fetching assigned mechanic data: ' . mysqli_error($conn));
}

// Fetch repair job details
$repair_job_query = "SELECT * FROM repair WHERE car_id = '$car_id'";
$repair_job_result = mysqli_query($conn, $repair_job_query);

if (!$repair_job_result) {
    die('Error fetching repair job data: ' . mysqli_error($conn));
}

// Fetch user and car information
$user_car_query = "SELECT u.name, u.image, c.carmodel
                    FROM user u
                    JOIN car c ON u.id = c.user_id
                    WHERE c.car_id = '$car_id'";
$user_car_result = mysqli_query($conn, $user_car_query);

if (!$user_car_result || mysqli_num_rows($user_car_result) == 0) {
    die('Error fetching user and car information: ' . mysqli_error($conn));
}

$user_car_info = mysqli_fetch_assoc($user_car_result);
$carmodel = $user_car_info['carmodel'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair Job Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <h2>Repair Job Details</h2>

    <!-- User and Car Information -->
    <div class="profilevalidate bg-white p-4 rounded-md shadow-md mb-4">
        <div class="vimage">
            <?php
            if ($user_car_info['image'] == '') {
                echo '<img src="images/default-avatar.png">';
            } else {
                echo '<img src="uploaded_img/' . $user_car_info['image'] . '">';
            }
            ?>
            <p class="mb-2">Name: <?php echo $user_car_info['name']; ?></p>
            <!-- Display car model -->
            <p class="mb-2">Car Model: <?php echo $carmodel; ?></p>
        </div>
    </div>

    <!-- Assigned Mechanic Details -->
    <div class="assigned-mechanic bg-white p-4 rounded-md shadow-md mb-4">
        <h3>Assigned Mechanic</h3>
        <?php
        if (mysqli_num_rows($assigned_mechanic_result) > 0) {
            $assigned_mechanic = mysqli_fetch_assoc($assigned_mechanic_result);
            echo '<p>Mechanic Name: ' . $assigned_mechanic['mechanic_name'] . '</p>';
            echo '<p>Job Role: ' . $assigned_mechanic['jobrole'] . '</p>';
        } else {
            echo '<p>No mechanic assigned yet.</p>';
        }
        ?>
    </div>

    <!-- Repair Job Details -->
    <div class="repair-job bg-white p-4 rounded-md shadow-md mb-4">
        <h3>Repair Job</h3>
        <?php
        if (mysqli_num_rows($repair_job_result) > 0) {
            echo '<ul>';
            while ($row = mysqli_fetch_assoc($repair_job_result)) {
                echo '<li>' . $row['problem'] . ': ' . $row['diagnosis'] . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No repair job details found.</p>';
        }
        ?>
    </div>

    <!-- Button to proceed to job repair -->
    <a href="jobrepair.php?car_id=<?php echo $car_id; ?>" class="btn btn-primary">Proceed to Job Repair</a>
</div>

</body>
</html>
