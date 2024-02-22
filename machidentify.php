<?php
session_start();

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

include 'config.php';

// Retrieve user_id and car_id parameters from the URL
if (isset($_GET['user_id']) && isset($_GET['car_id'])) {
    $user_id = $_GET['user_id'];
    $car_id = $_GET['car_id'];

    // Fetch user data based on user_id
    $user_select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'");
    $user_data = ($user_select) ? mysqli_fetch_assoc($user_select) : die('Error fetching user data: ' . mysqli_error($conn));

    // Fetch car data based on car_id
    $car_select = mysqli_query($conn, "SELECT * FROM car WHERE user_id = '$user_id' AND car_id = '$car_id'");
    $car_data = ($car_select) ? mysqli_fetch_assoc($car_select) : die('Error fetching car data: ' . mysqli_error($conn));

    // Fetch service data based on user_id and car_id
    $service_select = mysqli_query($conn, "SELECT * FROM service WHERE user_id = '$user_id' AND car_id = '$car_id'");
    $service_data = ($service_select) ? mysqli_fetch_assoc($service_select) : die('Error fetching service data: ' . mysqli_error($conn));
} else {
    die('User ID and Car ID not specified.');
}

function mapMaintenanceStatus($status) {
    switch ($status) {
        case '1':
            return 'Normal';
        case '2':
            return 'Above Normal';
        case '3':
            return 'Urgent Maintenance';
        default:
            return ''; // Return an empty string for other values
    }
}

// For color

function mapMaintenanceStatusAndColor($status) {
    switch ($status) {
        case '1':
            return 'text-green-500'; // Normal color green
        case '2':
            return 'text-yellow-500'; // Above Normal color yellow
        case '3':
            return 'text-red-500'; // Urgent Maintenance color red
        default:
            return 'text-gray-600'; // Default color gray
    }
}


$select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'") or die('query failed');
if(mysqli_num_rows($select) > 0){
   $fetch = mysqli_fetch_assoc($select);
} else {
   die('No user found');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/machidentify.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

 
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Mechanic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
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
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
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

<div class="container mx-auto mt-8">

    <!-- User Profile Section -->
    <div class="profile-container bg-white p-6 rounded-lg shadow-md">
   
    <div class="profile">
        <?php
            if($fetch['image'] == ''){
                echo '<img src="images/default-avatar.png">';
            }else{
                echo '<img src="uploaded_img/'.$fetch['image'].'">';
            }
        ?>
    </div>
        <p class="mb-2">User Name: <?php echo $user_data['name']; ?></p>
        <p class="mb-2">Manufacturer: <?php echo $car_data['manufacturer']; ?></p>
        <p class="mb-2">Car Model: <?php echo $car_data['carmodel']; ?></p>
        <p class="mb-2">Plate #: <?php echo $car_data['plateno']; ?></p>
        <!-- Display other user information as needed -->
    </div>

    <!-- Car Details Section -->
    <div class="profile-container bg-white p-6 mt-8 rounded-lg shadow-md">
      
        <form action="update_repair.php" method="post" class="mt-4">
            
                 <!-- Major Section -->
        <h2 class="text-xl font-bold mb-4">Major</h2>

        <div id="eo_container" class="bg-white p-4 rounded-md shadow-md">
            <label class="block text-sm font-medium text-gray-600"><span class="font-bold text=[#000000]">Engine Overhaul:</span>
                <span id="eo_status" class="ml-2 text-red-500"><?php echo ($service_data['eo'] == 1) ? 'Urgent Need' : ''; ?></span>
                <input type="checkbox" name="engine_overhaul_problems[]" value="Piston ring" class="mr-2">
                <span>Piston Ring</span>
                <input type="checkbox" name="engine_overhaul_problems[]" value="Head gaskit" class="mr-2">
                <span>Head Gasket</span>
                <input type="checkbox" name="engine_overhaul_problems[]" value="Oil circulation" class="mr-2">
                <span>Oil Circulation</span>
            </label>
        </div>

            <div id="elp_container" class="bg-white p-4 rounded-md shadow-md">
                <label class="block text-sm font-medium text-gray-600"><span class="font-bold text=[#000000]">Engine Low Power:</span>
                    <span id="elp_status" class="ml-2 text-red-500"><?php echo ($service_data['elp'] == 2) ? 'Urgent Need' : ''; ?></span>
                    <input type="checkbox" name="engine_low_power_problems[]" value="Mass air flow" class="mr-2">
                    <span>Mass Air Flow (MAF)</span>
                    <input type="checkbox" name="engine_low_power_problems[]" value="Throttle body" class="mr-2">
                    <span>Throttle Body</span>
                    <input type="checkbox" name="engine_low_power_problems[]" value="Fuel system" class="mr-2">
                    <span>Fuel System</span>
                </label>
            </div>

            <div id="ep_container" class="bg-white p-4 rounded-md shadow-md">
                <label class="block text-sm font-medium text-gray-600"><span class="font-bold text=[#000000">Electrical Problem:</span>
                    <span id="ep_status" class="ml-2 text-red-500"><?php echo ($service_data['ep'] == 3) ? 'Urgent Need' : ''; ?></span>
                    <input type="checkbox" name="electrical_problems[]" value="Spark plugs" class="mr-2">
                    <span>Spark Plugs</span>
                    <input type="checkbox" name="electrical_problems[]" value="Ignition system" class="mr-2">
                    <span>Ignition System</span>
                    <input type="checkbox" name="electrical_problems[]" value="Electronic module" class="mr-2">
                    <span>Electronic Module</span>
                </label>
            </div>  



           
        <div class="maintenance-container">
           
                <!-- Maintenance Section -->
                <h2 class="text-xl font-bold mb-4">Maintenance</h2>

                <!-- Battery -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['battery']); ?>"><span class="text-black font-bold">Battery:</span>
                    <?php echo mapMaintenanceStatus($service_data['battery']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="battery_problems[]" value="Battery voltage" class="mr-2">
                        <span>Battery Voltage</span>

                        <input type="checkbox" name="battery_problems[]" value="Battery terminals" class="mr-2">
                        <span>Battery Terminals</span>

                        <input type="checkbox" name="battery_problems[]" value="Load test" class="mr-2">
                        <span>Load Test</span>
                    </div>
                </div>

                <!-- Light -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['light']); ?>"><span class="text-black font-bold">Light:</span>
                    <?php echo mapMaintenanceStatus($service_data['light']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="light_problems[]" value="Check bulbs" class="mr-2">
                        <span>Check Bulbs</span>

                        <input type="checkbox" name="light_problems[]" value="Inspect fuses" class="mr-2">
                        <span>Inspect Fuses</span>

                        <input type="checkbox" name="light_problems[]" value="Check wirings" class="mr-2">
                        <span>Check Wirings</span>
                    </div>
                </div>

                <!-- Oil -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['oil']); ?>"><span class="text-black font-bold">Oil:</span>
                    <?php echo mapMaintenanceStatus($service_data['oil']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="oil_problems[]" value="Oil level" class="mr-2">
                        <span>Oil Level</span>

                        <input type="checkbox" name="oil_problems[]" value="Change oil" class="mr-2">
                        <span>Change Oil</span>

                        <input type="checkbox" name="oil_problems[]" value="Oil filter" class="mr-2">
                        <span>Oil Filter</span>
                    </div>
                </div>

                <!-- Water -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['water']); ?>"><span class="text-black font-bold">Water:</span>
                    <?php echo mapMaintenanceStatus($service_data['water']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="water_problems[]" value="Colant level" class="mr-2">
                        <span>Colant Level</span>

                        <input type="checkbox" name="water_problems[]" value="Radiator cap" class="mr-2">
                        <span>Radiator Cap</span>

                        <input type="checkbox" name="water_problems[]" value="Coolant radiator" class="mr-2">
                        <span>Colant Radiator</span>
                    </div>
                </div>

                <!-- Brake -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['brake']); ?>"><span class="text-black font-bold">Brake:</span>
                    <?php echo mapMaintenanceStatus($service_data['brake']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="brake_problems[]" value="Brake fluid" class="mr-2">
                        <span>Brake Fluid</span>

                        <input type="checkbox" name="brake_problems[]" value="Brake pad" class="mr-2">
                        <span>Brake Pad</span>

                        <input type="checkbox" name="brake_problems[]" value="Break leaks" class="mr-2">
                        <span>Brake Leaks</span>
                    </div>
                </div>

                <!-- Air -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['air']); ?>"><span class="text-black font-bold">Air:</span>
                    <?php echo mapMaintenanceStatus($service_data['air']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="air_problems[]" value="Air intake system" class="mr-2">
                        <span>Air Intake System</span>

                        <input type="checkbox" name="air_problems[]" value="HVAC system" class="mr-2">
                        <span>HVAC System</span>

                        <input type="checkbox" name="air_problems[]" value="Temperature control" class="mr-2">
                        <span>Temperature Control</span>
                    </div>
                </div>

                <!-- Gas -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['gas']); ?>"><span class="text-black font-bold">Gas:</span>
                    <?php echo mapMaintenanceStatus($service_data['gas']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="gas_problems[]" value="Fuel system" class="mr-2">
                        <span>Fuel System</span>

                        <input type="checkbox" name="gas_problems[]" value="Fuel injector" class="mr-2">
                        <span>Fuel Injector</span>

                        <input type="checkbox" name="gas_problems[]" value="Engine light" class="mr-2">
                        <span>Engine Light</span>
                    </div>
                </div>

                <!-- Tire -->
                <div class="mb-4">
                <label class="block text-sm font-medium <?php echo mapMaintenanceStatusAndColor($service_data['tire']); ?>"><span class="text-black font-bold">Tire:</span>
                    <?php echo mapMaintenanceStatus($service_data['tire']); ?>
                </label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="tire_problems[]" value="Tire pressure" class="mr-2">
                        <span>Tire Pressure</span>

                        <input type="checkbox" name="tire_problems[]" value="Wheel alignment" class="mr-2">
                        <span>Wheel Alignment</span>

                        <input type="checkbox" name="tire_problems[]" value="Tire repair" class="mr-2">
                        <span>Tire Repair</span>
                    </div>
                </div>


            </div>

            <!-- Hidden input fields for car_id and user_id -->
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
              <input type="hidden" name="approval_status" id="approval_status" value="">

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-600">Approval Status:</label>
                <div class="flex items-center">
                    <label for="approve" class="ml-2">Approve</label>
                    <input type="radio" id="approve" name="approval_status" value="1" checked>
                    <label for="not_approve" class="ml-2">Not Approve</label>
                    <input type="radio" id="not_approve" name="approval_status" value="0">

                    <!-- Dropdown for Not Approve with reasons -->
                    <select name="not_approve_reason" id="not_approve_reason" class="ml-2 hidden">
                        <option value="">Select Reason</option>
                        <option value="Lack of expertise">Lack of expertise</option>
                        <option value="Lack of tools">Lack of tools</option>
                        <option value="No available parts">No available parts</option>
                    </select>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit"
                class="mt-4 bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring focus:border-blue-300">
                Submit
            </button>
        </form>
    </div>
</div>

<!-- Include Bootstrap JS and custom scripts here -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
    crossorigin="anonymous"></script>
    <script>
    // Function to hide/show container based on Urgent Need status
    function toggleContainerDisplay(status, containerId) {
        var containerElement = document.getElementById(containerId);
        containerElement.style.display = status ? 'block' : 'none';
    }

    // Call the function for each field
    toggleContainerDisplay(<?php echo ($service_data['eo'] == 1) ? 'true' : 'false'; ?>, 'eo_container');
    toggleContainerDisplay(<?php echo ($service_data['elp'] == 2) ? 'true' : 'false'; ?>, 'elp_container');
    toggleContainerDisplay(<?php echo ($service_data['ep'] == 3) ? 'true' : 'false'; ?>, 'ep_container');

</script>

<!-- approval status before submitting the form -->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get radio buttons and dropdown
        const approveRadio = document.getElementById("approve");
        const notApproveRadio = document.getElementById("not_approve");
        const notApproveReasonDropdown = document.getElementById("not_approve_reason");

        // Add event listener to radio buttons
        approveRadio.addEventListener("change", function () {
            notApproveReasonDropdown.classList.add("hidden");
        });

        notApproveRadio.addEventListener("change", function () {
            notApproveReasonDropdown.classList.remove("hidden");
        });

        // Add event listener to form submit
        const form = document.querySelector("form");
        form.addEventListener("submit", function (event) {
            // Check if Not Approve is selected and a reason is chosen
            if (notApproveRadio.checked && notApproveReasonDropdown.value !== "") {
                // Add a hidden input field to store the selected reason
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "not_approve_reason";
                hiddenInput.value = notApproveReasonDropdown.value;

                // Append the hidden input to the form
                form.appendChild(hiddenInput);
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get all select elements
        var selects = document.querySelectorAll("select");

        // Check if there are saved values in local storage
        var savedValues = JSON.parse(localStorage.getItem("selectedValues")) || {};

        // Set the selected values for each select element
        selects.forEach(function (select) {
            var fieldName = select.name;
            var savedValue = savedValues[fieldName];

            if (savedValue !== undefined) {
                // Set the selected attribute for the saved value
                var option = select.querySelector("option[value='" + savedValue + "']");
                if (option) {
                    option.selected = true;
                }
            }
        });

        // Add a submit event listener to the form
        var form = document.querySelector("form");
        form.addEventListener("submit", function () {
            // Save the selected values to local storage
            var selectedValues = {};

            selects.forEach(function (select) {
                selectedValues[select.name] = select.value;
            });

            localStorage.setItem("selectedValues", JSON.stringify(selectedValues));
        });
    });
</script>


<!--Approval parts-->
<script>
    function updateApprovalStatus() {
        var approvalStatus = document.querySelector('input[name="approval_status"]:checked').value;
        document.getElementById('approval_status').value = approvalStatus;
    }
</script>

<script>
    // Add event listener to toggle the visibility of the dropdown
    document.getElementById('not_approve').addEventListener('change', function () {
        var dropdown = document.getElementById('not_approve_reason');
        dropdown.classList.toggle('hidden', this.value !== '0');
    });
</script>

</body>
</html>
