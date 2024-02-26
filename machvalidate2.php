<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Retrieve Repair data for the specified user and car
$user_id = $_GET['user_id']; // Make sure to validate and sanitize user inputs
$car_id = $_GET['car_id'];   // Make sure to validate and sanitize user inputs

$repair_data = array();  // Associative array to store diagnoses for different problems

// Function to fetch and store data for a specific problem
function fetchDataForProblem($conn, $user_id, $car_id, $problem)
{
    $query = "SELECT diagnosis FROM repair WHERE user_id = ? AND plateno = ? AND problem = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $problem);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['diagnosis'];
    }

    mysqli_stmt_close($stmt);
    return $data;
}

// Function to fetch approval reason from approvals table
function getApprovalReason($conn, $user_id, $car_id) {
    $approval_query = "SELECT reason FROM approvals WHERE user_id = '$user_id' AND car_id = '$car_id'";
    $approval_result = mysqli_query($conn, $approval_query);

    if ($approval_result) {
        $approval_info = mysqli_fetch_assoc($approval_result);
        return $approval_info['reason'];
    } else {
        return 'Error fetching reason: ' . mysqli_error($conn);
    }
}

// For Major
$engine_overhaul_data = fetchDataForProblem($conn, $user_id, $car_id, 'Engine Overhaul');
$engine_low_power_data = fetchDataForProblem($conn, $user_id, $car_id, 'Engine Low Power');
$electrical_problem_data = fetchDataForProblem($conn, $user_id, $car_id, 'Electrical Problem');

// For Maintenance
$battery_data = fetchDataForProblem($conn, $user_id, $car_id, 'Battery');
$light_data = fetchDataForProblem($conn, $user_id, $car_id, 'Light');
$oil_data = fetchDataForProblem($conn, $user_id, $car_id, 'Oil');
$water_data = fetchDataForProblem($conn, $user_id, $car_id, 'Water');
$brake_data = fetchDataForProblem($conn, $user_id, $car_id, 'Brake');
$air_data = fetchDataForProblem($conn, $user_id, $car_id, 'Air');
$gas_data = fetchDataForProblem($conn, $user_id, $car_id, 'Gas');
$tire_data = fetchDataForProblem($conn, $user_id, $car_id, 'Tire');

// Fetch user and car information
$user_car_query = "SELECT user.name, user.image, car.carmodel, car.plateno FROM user
                    JOIN car ON user.id = car.user_id
                    WHERE user.id = '$user_id' AND car.car_id = '$car_id'";
$user_car_result = mysqli_query($conn, $user_car_query);

if ($user_car_result && mysqli_num_rows($user_car_result) > 0) {
    $user_car_info = mysqli_fetch_assoc($user_car_result);
    $carmodel = $user_car_info['carmodel'];
    $plateno = $user_car_info['plateno'];
} else {
    die('Error fetching user and car information: ' . mysqli_error($conn));
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation</title>
    <link rel="stylesheet" href="css/machvalidate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
    <!-- Your other scripts -->
</head>

<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="homemechanic.php">Home</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Dropdown
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
            </li>
            <li class="nav-item">
            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
            </li>
        </ul>
        <form class="d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        </div>
    </div>
</nav>

<div class="container mx-auto mt-5">
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
            <!-- Display car model and plate number -->
            <p class="mb-2">Car Model: <?php echo $carmodel; ?></p>
            <p class="mb-2">Plate Number: <?php echo $plateno; ?></p>
        </div>
    </div>


    <div class="for-major-container bg-white p-4 rounded-md shadow-md">
        <h2 class="text-2xl font-bold mb-4">Major</h2>

        <?php if (!empty($engine_overhaul_data)) { ?>
            <div>
                <strong>Engine Overhaul:</strong>
                <?php echo implode(', ', $engine_overhaul_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Engine overhaul:</strong>Good</p>
        <?php } ?>

        <?php if (!empty($engine_low_power_data)) { ?>
            <div>
                <strong>Engine Low Power:</strong>
                <?php echo implode(', ', $engine_low_power_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Engine low power:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($electrical_problem_data)) { ?>
            <div>
                <strong>Electrical Problems:</strong>
                <?php echo implode(', ', $electrical_problem_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Electrical problem:</strong> Good</p>
        <?php } ?>
    </div>

    <!-- Maintenance part -->
    <div class="for-maintenance-container bg-white p-4 mt-4 rounded-md shadow-md">
        <h2 class="text-2xl font-bold mb-4">Maintenance</h2>

        <?php if (!empty($battery_data)) { ?>
            <div>
                <strong>Battery:</strong>
                <?php echo implode(', ', $battery_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Battery:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($light_data)) { ?>
            <div>
                <strong>Light:</strong>
                <?php echo implode(', ', $light_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Light:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($oil_data)) { ?>
            <div>
                <strong>Oil:</strong>
                <?php echo implode(', ', $oil_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Oil:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($water_data)) { ?>
            <div>
                <strong>Water:</strong>
                <?php echo implode(', ', $water_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Water:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($brake_data)) { ?>
            <div>
                <strong>Brake:</strong>
                <?php echo implode(', ', $brake_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Brake:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($air_data)) { ?>
            <div>
                <strong>Air:</strong>
                <?php echo implode(', ', $air_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Air:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($gas_data)) { ?>
            <div>
                <strong>Gas:</strong>
                <?php echo implode(', ', $gas_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Gas:</strong> Good</p>
        <?php } ?>

        <?php if (!empty($tire_data)) { ?>
            <div>
                <strong>Tire:</strong>
                <?php echo implode(', ', $tire_data); ?>
            </div>
        <?php } else { ?>
            <p><strong>Tire:</strong> Good</p>
        <?php } ?>
    </div>

    <!-- Approval Reason -->
    <div class="bg-white p-4 rounded-md shadow-md">
        <p><span class="font-bold">Reason:</span> <?php echo getApprovalReason($conn, $user_id, $car_id); ?></p>
    </div>

    <button id="validBtn" class="mt-4 bg-green-500 text-white p-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring focus:border-green-300">
        Valid
    </button>
    <button id="invalidBtn" class="mt-4 bg-red-500 text-white p-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring focus:border-red-300">
        Invalid
    </button>

    <script>
        document.getElementById('validBtn').addEventListener('click', function () {
            updateValidation('valid');
        });

        document.getElementById('invalidBtn').addEventListener('click', function () {
            const comment = prompt('Enter your comment for invalidation:');
            if (comment !== null) {
                updateValidation('invalid', comment);
            }
        });

        function updateValidation(status, comment = null) {
            const user_id = <?php echo $user_id; ?>;
            const car_id = <?php echo $car_id; ?>;

            // Use AJAX to update validation status
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_validation.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Handle the response if needed
                    // You can reload the page or update UI elements
                    alert('Validation status updated successfully.');

                    // Redirect to homemanager.php
                    window.location.href = 'homemanager.php';
                }
            };

            const data = 'user_id=' + user_id + '&car_id=' + car_id + '&status=' + status + '&comment=' + comment;
            xhr.send(data);
        }
</script>
</body>
</html>