<?php
session_start();
include 'config.php';

// Check if the mechanic is logged in
if (!isset($_SESSION['mechanic_id'])) {
    header('location:login.php');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Retrieve Repair data for the specified car
$car_id = $_GET['car_id'];   
$repair_data = array(); 

// Function to fetch and store data for a specific problem
function fetchDataForProblem($conn, $mechanic_id, $car_id, $problem)
{
    $query = "SELECT diagnosis FROM diagnose WHERE mechanic_id = ? AND plateno = ? AND problem = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $mechanic_id, $car_id, $problem);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['diagnosis'];
    }

    mysqli_stmt_close($stmt);
    return $data;
}

// For Major
$engine_overhaul_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Engine Overhaul');
$engine_low_power_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Engine Low Power');
$electrical_problem_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Electrical Problem');

// For Maintenance
$battery_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Battery');
$light_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Light');
$oil_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Oil');
$water_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Water');
$brake_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Brake');
$air_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Air');
$gas_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Gas');
$tire_data = fetchDataForProblem($conn, $_SESSION['mechanic_id'], $car_id, 'Tire');

// Fetch car information
$car_query = "SELECT carmodel, plateno FROM car WHERE car_id = '$car_id'";
$car_result = mysqli_query($conn, $car_query);

if ($car_result && mysqli_num_rows($car_result) > 0) {
    $car_info = mysqli_fetch_assoc($car_result);
    $carmodel = $car_info['carmodel'];
    $plateno = $car_info['plateno'];
} else {
    die('Error fetching car information: ' . mysqli_error($conn));
}

$user_query = "SELECT * FROM user WHERE id = (SELECT user_id FROM car WHERE car_id = '$car_id')";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_info = mysqli_fetch_assoc($user_result);
} else {
    // Handle the case where no user is found
    $user_info = array(); // Assign an empty array
    // or you could die with an error message
    // die('Error fetching user information: ' . mysqli_error($conn));
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

    <nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php">Mechanic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="admin.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger"></span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-white" href="#">Action</a></li>
                        <li><a class="dropdown-item text-white" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <div class="container mx-auto mt-5">
        <!-- User and Car Information -->
        <ul class="flex justify-normal items-center" id="container">
        <li>
            <?php
            if (isset($user_info['image']) && !empty($user_info['image'])) {
                // Use image.php to serve the BLOB image
                echo '<img src="image.php?id=' . $user_info['id'] . '" class="w-20 h-20 rounded-full">';
            } else {
                echo '<img src="images/default-avatar.png" class="w-20 h-20 rounded-full">';
            }
            ?>
        </li>

            <li class="px-4"><p class="mb-2 font-medium">User Name: <?php echo '<span class="font-normal">'.$user_info['name'].'</span>'; ?></p></li>
            <li class="px-4"><p class="mb-2 font-medium">Car Model: <?php echo '<span class="font-normal">'. $carmodel . '</span>'; ?></p></li>
            <li class="px-4"><p class="mb-2 font-medium">Plate #:  <?php echo '<span class="font-normal">'. $plateno . '</span>'; ?></p></li>
        </ul>

        <!-- The repair check box -->

        <!-- Major  -->
        <div class="for-major-container bg-gray-100 p-4 rounded-md shadow-md">
            <h2 class="text-2xl font-bold mb-4">Primary Engine System</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php if (!empty($engine_overhaul_data)) { ?>
                    <div id="eo_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                        <label class="block">
                            <span class="font-bold">Mechanical Issues</span>
                        </label>
                        <div class="flex flex-col items-start pt-4 checkbox-group">
                            <?php if (is_array($engine_overhaul_data)) { ?>
                                <?php foreach ($engine_overhaul_data as $item) { ?>
                                    <span class="ml-6 flex items-center">
                                        <input type="checkbox" class="major-checkbox eo-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                                        <span class="ml-4"><?php echo $item; ?></span>
                                    </span>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" class="major-checkbox eo-checkbox" id="<?php echo str_replace(' ', '_', $engine_overhaul_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $engine_overhaul_data); ?>_checkbox">
                                    <span class="ml-4"><?php echo $engine_overhaul_data; ?></span>
                                </span>
                            <?php } ?>
                        </div>
                        <!-- Inline percentage display -->
                        <div class="pt-4">
                            <span class="font-bold">Progress: </span>
                            <span id="mechanical_issues_percentage">0%</span>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty($engine_low_power_data)) { ?>
                    <div id="elp_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                        <label class="block">
                            <span class="font-bold">Fuel and Air Intake System</span>
                        </label>
                        <div class="flex flex-col items-start pt-4 checkbox-group">
                            <?php if (is_array($engine_low_power_data)) { ?>
                                <?php foreach ($engine_low_power_data as $item) { ?>
                                    <span class="ml-6 flex items-center">
                                        <input type="checkbox" class="major-checkbox elp-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                                        <span class="ml-4"><?php echo $item; ?></span>
                                    </span>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" class="major-checkbox elp-checkbox" id="<?php echo str_replace(' ', '_', $engine_low_power_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $engine_low_power_data); ?>_checkbox">
                                    <span class="ml-4"><?php echo $engine_low_power_data; ?></span>
                                </span>
                            <?php } ?>
                        </div>
                        <!-- Inline percentage display -->
                        <div class="pt-4">
                            <span class="font-bold">Progress: </span>
                            <span id="engine_low_power_percentage">0%</span>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty($electrical_problem_data)) { ?>
                    <div id="elec_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                        <label class="block">
                            <span class="font-bold">Cooling and Lubrication</span>
                        </label>
                        <div class="flex flex-col items-start pt-4 checkbox-group">
                            <?php if (is_array($electrical_problem_data)) { ?>
                                <?php foreach ($electrical_problem_data as $item) { ?>
                                    <span class="ml-6 flex items-center">
                                        <input type="checkbox" class="major-checkbox elec-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                                        <span class="ml-4"><?php echo $item; ?></span>
                                    </span>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" class="major-checkbox elec-checkbox" id="<?php echo str_replace(' ', '_', $electrical_problem_data); ?>_checkbox" name="<?php echo str_replace(' ', '_', $electrical_problem_data); ?>_checkbox">
                                    <span class="ml-4"><?php echo $electrical_problem_data; ?></span>
                                </span>
                            <?php } ?>
                        </div>
                        <!-- Inline percentage display -->
                        <div class="pt-4">
                            <span class="font-bold">Progress: </span>
                            <span id="electrical_problem_percentage">0%</span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

            <!-- For maintenance -->

            <div class="for-maintenance-container bg-white p-4 mt-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Maintenance</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php if (!empty($battery_data)) { ?>
            <div id="battery_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Battery</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($battery_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox battery-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="battery_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($light_data)) { ?>
            <div id="light_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Light</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($light_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox light-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="light_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($oil_data)) { ?>
            <div id="oil_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Oil</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($oil_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox oil-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="oil_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <!-- Continue for other maintenance items -->
        <?php if (!empty($water_data)) { ?>
            <div id="water_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Water</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($water_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox water-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="water_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($brake_data)) { ?>
            <div id="brake_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Brake</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($brake_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox brake-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="brake_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($air_data)) { ?>
            <div id="air_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Air</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($air_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox air-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="air_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($gas_data)) { ?>
            <div id="gas_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Gas</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($gas_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox gas-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="gas_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($tire_data)) { ?>
            <div id="tire_container" class="border-2 border-gray-600 p-3 rounded-md shadow-md hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                <label class="block">
                    <span class="font-bold">Tire</span>
                </label>
                <div class="flex flex-col items-start pt-4 checkbox-group">
                    <?php foreach ($tire_data as $item) { ?>
                        <span class="ml-6 flex items-center">
                            <input type="checkbox" class="maintenance-checkbox tire-checkbox" id="<?php echo str_replace(' ', '_', $item); ?>_checkbox" name="<?php echo str_replace(' ', '_', $item); ?>_checkbox">
                            <span class="ml-4"><?php echo $item; ?></span>
                        </span>
                    <?php } ?>
                </div>
                <!-- Inline percentage display -->
                <div class="pt-4">
                    <span class="font-bold">Progress: </span>
                    <span id="tire_progress_percentage">0%</span>
                </div>
            </div>
        <?php } ?>
    </div>
</div>


    <br>
    <!-- Your existing HTML code -->
      
    
   <ul style="display: flex; justify-content: justify-between;  align-items: center; ">
        <li style="margin-top: 48px; margin-left: -15px;"><strong>Progress</strong>:</li>
        <li  class="progress mt-5" style="width: 50%; margin-left: 15px;">
           <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </li>
    </ul>

    <button id="save-progress-btn" class="btn btn-primary mt-3">Save Progress</button>
    <input type="hidden" id="user-id" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : ''; ?>">
    <input type="hidden" id="car-id" value="<?php echo $car_id; ?>">
    <input type="hidden" id="mechanic-id" value="<?php echo $_SESSION['mechanic_id']; ?>">


    <br>
    <br>
    <br>

    <!-- For Each of them percentage -->
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all checkboxes related to various sections
            const eoCheckboxes = document.querySelectorAll('.eo-checkbox');
            const mechanicalIssuesPercentage = document.getElementById('mechanical_issues_percentage');

            const elpCheckboxes = document.querySelectorAll('.elp-checkbox');
            const engineLowPowerPercentage = document.getElementById('engine_low_power_percentage');

            const elecCheckboxes = document.querySelectorAll('.elec-checkbox');
            const electricalProblemPercentage = document.getElementById('electrical_problem_percentage');

            const batteryCheckboxes = document.querySelectorAll('.battery-checkbox');
            const batteryProgressPercentage = document.getElementById('battery_progress_percentage');

            const lightCheckboxes = document.querySelectorAll('.light-checkbox');
            const lightProgressPercentage = document.getElementById('light_progress_percentage');

            const oilCheckboxes = document.querySelectorAll('.oil-checkbox');
            const oilProgressPercentage = document.getElementById('oil_progress_percentage');

            const waterCheckboxes = document.querySelectorAll('.water-checkbox');
            const waterProgressPercentage = document.getElementById('water_progress_percentage');

            const brakeCheckboxes = document.querySelectorAll('.brake-checkbox');
            const brakeProgressPercentage = document.getElementById('brake_progress_percentage');

            const airCheckboxes = document.querySelectorAll('.air-checkbox');
            const airProgressPercentage = document.getElementById('air_progress_percentage');

            const gasCheckboxes = document.querySelectorAll('.gas-checkbox');
            const gasProgressPercentage = document.getElementById('gas_progress_percentage');

            const tireCheckboxes = document.querySelectorAll('.tire-checkbox'); // New selection for tire checkboxes
            const tireProgressPercentage = document.getElementById('tire_progress_percentage'); // New percentage display

            // Function to calculate and update the percentage for each section
            function updateProgress(checkboxes, progressElement) {
                const totalCheckboxes = checkboxes.length;
                const checkedCheckboxes = document.querySelectorAll(`${checkboxes[0].className}:checked`).length;
                const percentage = Math.round((checkedCheckboxes / totalCheckboxes) * 100);
                progressElement.textContent = percentage + '%';
            }

            // Add event listeners to each checkbox for Mechanical Issues
            eoCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(eoCheckboxes, mechanicalIssuesPercentage); });
            });

            // Add event listeners to each checkbox for Fuel and Air Intake System
            elpCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(elpCheckboxes, engineLowPowerPercentage); });
            });

            // Add event listeners to each checkbox for Cooling and Lubrication
            elecCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(elecCheckboxes, electricalProblemPercentage); });
            });

            // Add event listeners to each checkbox for Battery
            batteryCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(batteryCheckboxes, batteryProgressPercentage); });
            });

            // Add event listeners to each checkbox for Light
            lightCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(lightCheckboxes, lightProgressPercentage); });
            });

            // Add event listeners to each checkbox for Oil
            oilCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(oilCheckboxes, oilProgressPercentage); });
            });

            // Add event listeners to each checkbox for Water
            waterCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(waterCheckboxes, waterProgressPercentage); });
            });

            // Add event listeners to each checkbox for Brake
            brakeCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(brakeCheckboxes, brakeProgressPercentage); });
            });

            // Add event listeners to each checkbox for Air
            airCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(airCheckboxes, airProgressPercentage); });
            });

            // Add event listeners to each checkbox for Gas
            gasCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(gasCheckboxes, gasProgressPercentage); });
            });

            // Add event listeners to each checkbox for Tire
            tireCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () { updateProgress(tireCheckboxes, tireProgressPercentage); });
            });
        });

        // Wait for DOM to load before running the script
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox groups and add event listeners
        const checkboxGroups = document.querySelectorAll('.checkbox-group');

        checkboxGroups.forEach(group => {
            const checkboxes = group.querySelectorAll('input[type="checkbox"]');
            const percentageDisplay = group.closest('.border-2').querySelector('span[id$="_percentage"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
                    const totalCount = checkboxes.length;
                    const percentage = totalCount > 0 ? Math.round((checkedCount / totalCount) * 100) : 0;
                    percentageDisplay.textContent = `${percentage}%`;
                });
            });
        });
    });
    </script>




<script>
// Function to update progress percentage
function updateProgress() {
    var totalCheckboxes = document.querySelectorAll('input[type="checkbox"]').length;
    var checkedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked').length;
    var progressPercentage = (checkedCheckboxes / totalCheckboxes) * 100;
    document.querySelector('.progress-bar').style.width = progressPercentage + '%';
    document.querySelector('.progress-bar').innerText = progressPercentage.toFixed(2) + '%';
}

document.getElementById('save-progress-btn').addEventListener('click', function() {
    var userId = document.getElementById('user-id').value;
    var carId = document.getElementById('car-id').value;
    var mechanicId = document.getElementById('mechanic-id').value;
    var overallProgressPercentage = document.querySelector('.progress-bar').innerText; // Overall progress from the main bar
    var checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');

    // If no items are checked, do nothing
    if (checkboxes.length === 0) {
        alert('No progress items selected to save.');
        return;
    }

    // Define the problem categories
    var problemCategories = {
        'eo-checkbox': 'Mechanical Issues',
        'elp-checkbox': 'Fuel and Air intake System',
        'elec-checkbox': 'Cooling and Lubrication',
        'battery-checkbox': 'Battery',
        'light-checkbox': 'Light',
        'oil-checkbox': 'Oil',
        'water-checkbox': 'Water',
        'brake-checkbox': 'Brake',
        'air-checkbox': 'Air',
        'gas-checkbox': 'Gas',
        'tire-checkbox': 'Tire'
    };

    // Loop through each checked checkbox to send individual requests
    checkboxes.forEach(function(checkbox) {
        var progressing = checkbox.nextElementSibling.innerText.trim(); // Get the label text next to the checkbox

        // Determine the progressing percentage and nameprogress based on the checkbox class
        var progressingPercentage = '0'; // Default to 0
        var nameProgress = '';

        if (checkbox.classList.contains('eo-checkbox')) {
            progressingPercentage = document.getElementById('mechanical_issues_percentage').innerText;
            nameProgress = problemCategories['eo-checkbox'];
        } else if (checkbox.classList.contains('elp-checkbox')) {
            progressingPercentage = document.getElementById('engine_low_power_percentage').innerText;
            nameProgress = problemCategories['elp-checkbox'];
        } else if (checkbox.classList.contains('elec-checkbox')) {
            progressingPercentage = document.getElementById('electrical_problem_percentage').innerText;
            nameProgress = problemCategories['elec-checkbox'];
        } else if (checkbox.classList.contains('battery-checkbox')) {
            progressingPercentage = document.getElementById('battery_progress_percentage').innerText;
            nameProgress = problemCategories['battery-checkbox'];
        } else if (checkbox.classList.contains('light-checkbox')) {
            progressingPercentage = document.getElementById('light_progress_percentage').innerText;
            nameProgress = problemCategories['light-checkbox'];
        } else if (checkbox.classList.contains('oil-checkbox')) {
            progressingPercentage = document.getElementById('oil_progress_percentage').innerText;
            nameProgress = problemCategories['oil-checkbox'];
        } else if (checkbox.classList.contains('water-checkbox')) {
            progressingPercentage = document.getElementById('water_progress_percentage').innerText;
            nameProgress = problemCategories['water-checkbox'];
        } else if (checkbox.classList.contains('brake-checkbox')) {
            progressingPercentage = document.getElementById('brake_progress_percentage').innerText;
            nameProgress = problemCategories['brake-checkbox'];
        } else if (checkbox.classList.contains('air-checkbox')) {
            progressingPercentage = document.getElementById('air_progress_percentage').innerText;
            nameProgress = problemCategories['air-checkbox'];
        } else if (checkbox.classList.contains('gas-checkbox')) {
            progressingPercentage = document.getElementById('gas_progress_percentage').innerText;
            nameProgress = problemCategories['gas-checkbox'];
        } else if (checkbox.classList.contains('tire-checkbox')) {
            progressingPercentage = document.getElementById('tire_progress_percentage').innerText;
            nameProgress = problemCategories['tire-checkbox'];
        }

        // Check if progressingPercentage is valid
        if (progressingPercentage === '0') {
            console.warn('Progressing percentage for ' + progressing + ' is defaulting to 0. Please check the respective elements.');
            return; // Skip sending this checkbox if the percentage is invalid
        }

        // Send progress data to server using AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_progress.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                console.log('Progress saved for ' + progressing + ': ' + xhr.responseText);
            } else {
                console.error('Error saving progress for ' + progressing + ': ' + xhr.responseText);
            }
        };

        // Pass user_id, car_id, mechanic_id, overall progress, progressing, progressingpercentage, and nameprogress
        xhr.send('user_id=' + userId + '&car_id=' + carId + '&mechanic_id=' + mechanicId + '&progress=' + overallProgressPercentage + '&progressing=' + encodeURIComponent(progressing) + '&progressingpercentage=' + encodeURIComponent(progressingPercentage) + '&nameprogress=' + encodeURIComponent(nameProgress));
    });

    alert('Progress saving initiated for checked items.');
});




// Function to calculate individual progress percentage based on checkbox group
function calculateIndividualProgress(checkboxClass) {
    const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
    const totalCheckboxes = checkboxes.length;
    const checkedCheckboxes = document.querySelectorAll(`.${checkboxClass}:checked`).length;
    
    if (totalCheckboxes === 0) return '0%'; // Avoid division by zero
    const percentage = Math.round((checkedCheckboxes / totalCheckboxes) * 100);
    return percentage + '%';
}



// Checkbox click event to update progress
var checkboxes = document.querySelectorAll('input[type="checkbox"]');
checkboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        updateProgress();
    });
});

// Initial progress update
updateProgress();
</script>

</body>
</html>