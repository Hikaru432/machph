<?php
session_start();

// Redirect to login.php if the user is not logged in
if (!isset($_SESSION['companyid'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Retrieve mechanic_id and car_id parameters from the URL
if (isset($_GET['mechanic_id']) && isset($_GET['car_id'])) {
    $mechanic_id = intval($_GET['mechanic_id']); // Ensure it's an integer
    $car_id = intval($_GET['car_id']); // Ensure it's an integer

    // Fetch mechanic data based on mechanic_id
    $mechanic_select = mysqli_query($conn, "SELECT * FROM mechanic WHERE mechanic_id = '$mechanic_id'");
    $mechanic_data = ($mechanic_select) ? mysqli_fetch_assoc($mechanic_select) : die('Error fetching mechanic data: ' . mysqli_error($conn));

    // Fetch car data based on car_id
    $car_select = mysqli_query($conn, "SELECT car.*, manufacturer.name AS manufacturer_name FROM car LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id WHERE car.car_id = '$car_id'");
    $car_data = ($car_select) ? mysqli_fetch_assoc($car_select) : die('Error fetching car data: ' . mysqli_error($conn));

    // Fetch service data based on car_id
    $service_select = mysqli_query($conn, "SELECT * FROM service WHERE car_id = '$car_id'");
    $service_data = ($service_select) ? mysqli_fetch_assoc($service_select) : die('Error fetching service data: ' . mysqli_error($conn));

    // Fetch user data based on user_id
    $user_id = $car_data['user_id'];
    $user_select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'");
    $user_data = ($user_select) ? mysqli_fetch_assoc($user_select) : die('Error fetching user data: ' . mysqli_error($conn));

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
} else {
    die('Mechanic ID and Car ID not specified.');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

 
</head>
<body>

 
<body class="bg-gray-100">


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




<div class="container mx-auto mt-8">
   
    <!-- For profile -->
    <ul class="flex justify-normal items-center " id="container">
    <li>
    <?php
        if (isset($user_data['image']) && !empty($user_data['image'])) {
            // Display the BLOB image using image.php script
            echo '<img src="image.php?id=' . $user_data['id'] . '" class="w-20 h-20 rounded-full">';
        } else {
            // Display the default avatar if no image is found
            echo '<img src="images/default-avatar.png" class="w-20 h-20 rounded-full">';
        }
        ?>
    </li>

        <li class="px-4"><p class="mb-2 font-medium"><strong>User Name</strong>: <?php echo '<span class="font-normal">'. $user_data['name'] . '</span>'; ?></p></li>
        <li class="px-4"> <p class="mb-2 font-medium"><strong>Manufacturer</strong>: <?php echo '<span class="font-normal">'. $car_data['manufacturer_name'] . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium"><strong>Car Model</strong>: <?php echo '<span class="font-normal">'. $car_data['carmodel'] . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium"><strong>Plate #</strong>:  <?php echo '<span class="font-normal">'. $car_data['plateno'] . '</span>'; ?></p></li>
    </ul>

    <!-- Car details -->

    <div class="w-full h-screen">
        <form action="update_repair.php" method="post">
          <div style="margin-top: 50px;">

            <h1 class="text-3xl font-bold">Primary Engine System</h1>
                <!-- Engine overhaul -->
                <div class="flex justify-evenly flex-row items-center py-4">

                    <div id="eo_container" >
                        <label class="block">
                            <span class="font-bold text-">Mechanical Issues</span>
                            <span id="eo_status" class="ml-9 text-red-500"><?php echo ($service_data['eo'] == 1) ? 'Urgent Need' : ''; ?></span>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_overhaul_problems[]" value="Piston and Piston Rings" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Piston</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_overhaul_problems[]" value="Valve Train" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Valve Train</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_overhaul_problems[]" value="Timing Chain or Belt" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Timing Belt</span>
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Engine low power -->
                    <div id="elp_container" >
                        <label class="block">
                            <span class="font-bold text-">Fuel and Air intake System</span>
                            <span id="eo_status" class="ml-9 text-red-500"><?php echo ($service_data['elp'] == 2) ? 'Urgent Need' : ''; ?></span>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_low_power_problems[]" value="Fuel Injection" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Fuel Injection</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_low_power_problems[]" value="Air Filter" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Air Filter</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="engine_low_power_problems[]" value="Throttle Body" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Throttle Body</span>
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Electrical problem -->
                    <div id="ep_container" >
                        <label class="block">
                            <span class="font-bold text-">Cooling and Lubrication</span>
                            <span id="eo_status" class="ml-9 text-red-500"><?php echo ($service_data['ep'] == 3) ? 'Urgent Need' : ''; ?></span>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="electrical_problems[]" value="Coolant Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Coolant Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="electrical_problems[]" value="Oil Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Oil Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="electrical_problems[]" value="Water Pump" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Water Pump</span>
                                </span>
                            </div>
                        </label>
                    </div>
                        
                </div>
            </div>

            <br>
            <hr style="border-color: black;">
            <br>
    <!-- Minor part -->

     <div>
            <h1 class="text-3xl font-bold">Maintenance</h1>
                <div class="flex justify-evenly flex-wrap items-center py-4">
                        <!-- Battery part -->
                        <div id="battery_container"; class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['battery']); ?>">
                                <span class="font-bold text-black">Battery:</span>
                                <span class="text-sm font-medium"><?php echo mapMaintenanceStatus($service_data['battery']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="battery_problems[]" value="Battery Age" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Battery Age</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="battery_problems[]" value="Overcharging or Undercharging" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Overcharging or Undercharging</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="battery_problems[]" value="Corrosion" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Corrosion</span>
                                </span>
                            </div>
                        </div>

                        <!-- Light -->
                        <div id="light_container" class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['light']); ?>">
                                <span class="font-bold text-black">Light:</span>
                                <span class="text-sm font-medium"><?php echo mapMaintenanceStatus($service_data['light']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="light_problems[]" value="Faulty Bulbs" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Faulty Bulbs</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="light_problems[]" value="Inspect fuses" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Electrical Wiring Problems</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="light_problems[]" value="Check wirings" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Switch Malfunction</span>
                                </span>
                            </div>
                        </div>

                        <!-- Oil -->

                        <div id="oil_container" class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['oil']); ?>">
                                <span class="font-bold text-black">Oil:</span>
                                <span class="text-sm font-medium "><?php echo mapMaintenanceStatus($service_data['oil']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="oil_problems[]" value="Oil Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Oil Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="oil_problems[]" value="Oil Consumptionl" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Oil Consumption</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="oil_problems[]" value="Oil Contamination" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Oil Contamination</span>
                                </span>
                            </div>
                        </div>

                        <!-- Water -->
                        <div id="water_container" class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['water']); ?>">
                                <span class="font-bold text-black">Water:</span>
                                <span class="text-sm font-medium"><?php echo mapMaintenanceStatus($service_data['water']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="water_problems[]" value="Coolant Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Coolant Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="water_problems[]" value="Coolant Loss" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Coolant Loss</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="water_problems[]" value="Coolant Contamination" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Coolant Contamination</span>
                                </span>
                            </div>
                        </div>

                        <!-- Brake -->
                        <div id="brake_container" class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['brake']); ?>">
                                <span class="font-bold text-black">Brake:</span>
                                <span class="text-sm font-medium "><?php echo mapMaintenanceStatus($service_data['brake']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="brake_problems[]" value="Brake Fluid Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Brake Fluid Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="brake_problems[]" value="Brake Pad Wear" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Brake Pad Wear</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="brake_problems[]" value="Brake Fluid Contamination" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Brake Fluid Contamination</span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Air -->
                        <div id="air_container" class="maintenance-container">
                            <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['air']); ?>">
                                <span class="font-bold text-black">Air:</span>
                                <span class="text-sm font-medium "><?php echo mapMaintenanceStatus($service_data['air']); ?></span>
                            </label>
                            <div class="flex flex-col items-start pt-4 checkbox-group">
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="air_problems[]" value="Air Filter Clogging" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Air Filter Clogging</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="air_problems[]" value="Vacuum Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Vacuum Leaks</span>
                                </span>
                                <span class="ml-6 flex items-center">
                                    <input type="checkbox" name="air_problems[]" value="Fuel System Issues" class="form-checkbox h-5 w-5 text-gray-600">
                                    <span class="ml-4">Fuel System Issues</span>
                                </span>
                            </div>
                        </div>

                    <!-- Gas -->

                    <div id="gas_container" class="maintenance-container">
                        <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['gas']); ?>">
                            <span class="font-bold text-black">Gas:</span>
                            <span class="text-sm font-medium "><?php echo mapMaintenanceStatus($service_data['gas']); ?></span>
                        </label>
                        <div class="flex flex-col items-start pt-4 checkbox-group">
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="gas_problems[]" value="Fuel System Leaks" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Fuel System Leaks</span>
                            </span>
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="gas_problems[]" value="Fuel Pump Failure" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Fuel Pump Failure</span>
                            </span>
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="gas_problems[]" value="Fuel Evaporation and Vapor Management" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Fuel Evaporation and Vapor Management</span>
                            </span>
                        </div>
                    </div>

                    <!-- Tire -->

                    <div id="tire_container" class="maintenance-container" >
                        <label class="block <?php echo mapMaintenanceStatusAndColor($service_data['tire']); ?>">
                            <span class="font-bold text-black">Tire:</span>
                            <span class="text-sm font-medium "><?php echo mapMaintenanceStatus($service_data['tire']); ?></span>
                        </label>
                        <div class="flex flex-col items-start pt-4 checkbox-group">
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="tire_problems[]" value="Tire Wear" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Tire Wear</span>
                            </span>
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="tire_problems[]" value="Tire Punctures or Damage" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Tire Punctures or Damage</span>
                            </span>
                            <span class="ml-6 flex items-center">
                                <input type="checkbox" name="tire_problems[]" value="Underinflation or Overinflation" class="form-checkbox h-5 w-5 text-gray-600">
                                <span class="ml-4">Underinflation or Overinflation</span>
                            </span>
                        </div>
                    </div>

        </div>

        <br>
        <hr style="border-color: black;">
        <br>


            <!-- Hidden input fields for car_id and user_id -->
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <input type="hidden" name="mechanic_id" value="<?php echo $mechanic_id; ?>">
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
            <br>
            <br>
            <br>
            <br>
        </form>
    </div>
</div>

<!-- Include Bootstrap JS and custom scripts here -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
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
            if (notApproveRadio.checked && notApproveReasonDropdown.value === "") {
                alert("Please select a reason for not approving.");
                event.preventDefault(); // Prevent form submission if reason is not selected
            } else if (notApproveRadio.checked) {
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