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

    <nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="#">Mechanic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">Home</a>
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
    <!-- Your existing HTML code -->
      
    <ul style="display: flex; justify-content: justify-between;  align-items: center; ">
        <li style="margin-top: 48px; margin-left: -15px;"><strong>Progress</strong>:</li>
        <li  class="progress mt-5" style="width: 50%; margin-left: 15px;">
           <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </li>
    </ul>
    
         <!-- <div class="progress mt-5" style="width: 50%;">
           <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
         </div> -->
     

        <button id="save-progress-btn" class="btn btn-primary mt-3">Save Progress</button>
        <input type="hidden" id="user-id" value="<?php echo $_SESSION['user_id']; ?>">




    <br>
    <br>
    <br>
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
    var progressPercentage = document.querySelector('.progress-bar').innerText;

    // Send progress data to server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_progress.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Progress saved successfully.');
        } else {
            alert('Error saving progress: ' + xhr.responseText);
        }
    };
    xhr.send('user_id=' + userId + '&progress=' + progressPercentage);
});
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