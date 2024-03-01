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
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <!-- Navbar content here -->
</nav>

<div class="container mx-auto mt-5">
    <!-- User and Car Information -->
    <ul class="flex justify-normal items-center" id="container">
        <li>
            <?php
            if($user_car_info['image'] == ''){
                echo '<img src="images/default-avatar.png" class="w-20 h-20 rounded-full">';
            }else{
                echo '<img src="uploaded_img/' . $user_car_info['image'] . '" class="w-20 h-20 rounded-full">';
            }
            ?>
        </li>
        <li class="px-4"><p class="mb-2 font-medium">User Name: <?php echo '<span class="font-normal">'.$user_car_info['name'] . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium">Car Model: <?php echo '<span class="font-normal">'. $carmodel . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium">Plate #:  <?php echo '<span class="font-normal">'. $plateno . '</span>'; ?></p></li>
    </ul>

    <!-- The repair check box -->

    <!-- Major  -->

    <div class="for-major-container bg-gray-100 p-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Major</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php if (!empty($engine_overhaul_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php if (is_array($engine_overhaul_data)) { ?>
                    <?php foreach ($engine_overhaul_data as $item) { ?>
                        <div>
                            <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="major-checkbox">
                            <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $engine_overhaul_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $engine_overhaul_data); ?>_checkbox" class="major-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $engine_overhaul_data); ?>_checkbox"><strong><?php echo $engine_overhaul_data; ?>:</strong><br><?php echo $engine_overhaul_data; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($engine_low_power_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php if (is_array($engine_low_power_data)) { ?>
                    <?php foreach ($engine_low_power_data as $item) { ?>
                        <div>
                            <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="major-checkbox">
                            <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $engine_low_power_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $engine_low_power_data); ?>_checkbox" class="major-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $engine_low_power_data); ?>_checkbox"><strong><?php echo $engine_low_power_data; ?>:</strong><br><?php echo $engine_low_power_data; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($electrical_problem_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php if (is_array($electrical_problem_data)) { ?>
                    <?php foreach ($electrical_problem_data as $item) { ?>
                        <div>
                            <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="major-checkbox">
                            <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $electrical_problem_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $electrical_problem_data); ?>_checkbox" class="major-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $electrical_problem_data); ?>_checkbox"><strong><?php echo $electrical_problem_data; ?>:</strong><br><?php echo $electrical_problem_data; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <!-- Add checkboxes and labels for other major problems here -->
    </div>
</div>

        <!-- For minor -->

        <div class="for-maintenance-container bg-white p-4 mt-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Maintenance</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php if (!empty($battery_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($battery_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($light_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($light_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($oil_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($oil_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($water_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($water_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($brake_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($brake_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($air_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($air_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($gas_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($gas_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (!empty($tire_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <?php foreach ($tire_data as $item) { ?>
                    <div>
                        <input type="checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox" class="maintenance-checkbox">
                        <label for="<?php echo str_replace(' ', '_', $item); ?>_checkbox"><strong><?php echo $item; ?>:</strong><br><?php echo $item; ?></label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<br>
<h3 class="">Accomplish</h3>
<div class="progress mt-5" style="width: 50%;">
    <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
</div>

<button id="save-progress-btn" class="btn btn-primary mt-3">Save Progress</button>


<br>
<br>
<br>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        // Calculate total problems count
        var totalProblems = <?php echo count($engine_overhaul_data) + count($engine_low_power_data) + count($electrical_problem_data) + count($battery_data) + count($light_data) + count($oil_data) + count($water_data) + count($brake_data) + count($air_data) + count($gas_data) + count($tire_data); ?>;
        
        // Initialize progress bar
        var progressBar = $('.progress-bar');
        var percent = 0;

        // Update progress bar percentage
        function updateProgressBar() {
            percent = ((completedProblems / totalProblems) * 100).toFixed(2);
            progressBar.text(percent + '%');
            progressBar.css('width', percent + '%');
        }

        // Initialize completed problems count
        var completedProblems = 0;

        // Check if each problem is completed
        function checkCompletion() {
            if (completedProblems === totalProblems) {
                // Save progress to the database using AJAX
                $.ajax({
                    type: 'POST',
                    url: 'save_progress.php',
                    data: {user_id: <?php echo $user_id; ?>, car_id: <?php echo $car_id; ?>, progress: percent},
                    success: function(response) {
                        console.log('Progress saved successfully.');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error saving progress:', error);
                    }
                });
            }
        }

        // Function to update completed problems count and progress bar
        function updateCompletion() {
            completedProblems++;
            updateProgressBar();
            checkCompletion();
        }

        // Handle checkbox change events
        $('input[type="checkbox"]').change(function() {
            if (this.checked) {
                updateCompletion();
            } else {
                completedProblems--;
                updateProgressBar();
            }
        });
    });
</script>



</body>
</html>
