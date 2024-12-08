<?php
session_start();
include 'config.php';
require 'calculate_estimated_price.php';
    
// Check if the mechanic is logged in
if (!isset($_SESSION['companyid'])) {
    header('Location: index.php');
    exit();
}

// Retrieve mechanic_id from session or URL
if (isset($_GET['mechanic_id'])) {
    $_SESSION['mechanic_id'] = intval($_GET['mechanic_id']);
} elseif (!isset($_SESSION['mechanic_id'])) {
    die('Mechanic ID not specified.'); // Ensure mechanic_id is available
}

// Retrieve user_id and car_id
if (isset($_GET['user_id']) && isset($_GET['car_id'])) {
    $user_id = $_GET['user_id']; 
    $car_id = $_GET['car_id'];  
} else {
    die('User ID and Car ID not specified.');
}

$repair_data = array(); 

$user_car_query = "SELECT u.name AS user_name, u.image AS user_image, c.carmodel, c.plateno, m.name AS manufacturer_name
                   FROM user u
                   INNER JOIN car c ON u.id = c.user_id
                   LEFT JOIN manufacturer m ON c.manufacturer_id = m.id
                   WHERE u.id = '$user_id' AND c.car_id = '$car_id'";
$user_car_result = mysqli_query($conn, $user_car_query);

if ($user_car_result && mysqli_num_rows($user_car_result) > 0) {
    $user_car_info = mysqli_fetch_assoc($user_car_result);
    $user_name = $user_car_info['user_name'];
    $user_image = $user_car_info['user_image'];
    $carmodel = $user_car_info['carmodel'];
    $plateno = $user_car_info['plateno'];
    $manuname = $user_car_info['manufacturer_name'];
} else {
    die('Error fetching user and car information: ' . mysqli_error($conn));
}

// Function to fetch and store data for a specific problem
function fetchDataForProblem($conn, $user_id, $car_id, $problem)
{
    $query = "SELECT diagnosis FROM diagnose WHERE user_id = ? AND plateno = ? AND problem = ?";
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

// Fetch user information
$user_query = "SELECT name FROM user WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_info = mysqli_fetch_assoc($user_result);
    $user_name = $user_info['name'];
} else {
    die('Error fetching user information: ' . mysqli_error($conn));
}



// Define the problem parts mapping array
$problem_parts_mapping = array(
    'Mechanical Issues' => array(
        'Piston' => ['Cylinder Liner', 'Piston', 'Piston Rings', 'Connecting Rod', 'Cylinder Head Gasket', 'Crankshaft Bearings', 'Crankcase Gaskets'],
        'Valve Train' => ['Camshaft(s)', 'Valves', 'Timing Chain/Built', 'Valve Springs', 'Rocker Arms', 'Valve Guides', 'Valve Train Gaskets'],
        'Timing Belt' => ['Timing Belt', 'Timing Chain Tensioner', 'Timing Chain Guide', 'Timing Gear', 'Crankshaft Sprocket', 'Camshaft Cover Gasket'],
    ),
    'Fuel and Air Intake System' => array(
        'Fuel Injection System' => ['Fuel Injectors', 'Fuel Pump', 'Fuel Pressure Regulator', 'Fuel Filter', 'Fuel Rail', 'Mass Airflow Sensor (MAF)', 'Engine Control Unit (ECU)'],
        'Air Filter' => ['Air Filter', 'Air Filter Housin', 'Air Intake Hose'],
        'Throttle Body' => ['Throttle Body', 'Throttle Position Sensor', 'Throttle Cable', 'Bypass Valve', 'Throttle Body Gasket', 'Throttle Actuator'],
    ),
    'Cooling and Lubrication' => array(
        'Coolant Leaks' => ['Radiator', 'Radiator Cap', 'Radiator Hoses', 'Thermostat Housing', 'Coolant Reservoir Tank'],
        'Oil Leaks' => ['Engine Oil Seals', 'Valve Cover Gasket', 'Cylinder Head Gasket', 'Crankcase Gasket', 'Oil Pan Gasket', 'Drain Plug and Washer', 'Oil Filter', 'Camshaft End Plug', 'Breather Hoses and PCV'],
        // 'Water Pump' => ['Water Pump Housing', 'Water Pump Impeller', 'Water Pump Seal', 'Water Pump Bearing', 'Water Pump Gasket', 'Water Pump Pulley', 'Water Pump Belt Tensioner', 'Water Pump Belt or Chain', 'Water Pump Bypass Valve', 'Water Pump Housing Bolts', 'Coolant Inlet/Outlet Ports', 'Coolant Temperature Sensor', 'Heater Core Hose Fittings'],
    ),
    'Battery' => array (
        'Battery age' => ['Battery Replacement', 'Battery Load Test', 'Charging System Inspection', 'Battery Tender or Trickle Charger'],
        'Overcharging or Undercharging' => ['Alternator Inspection', 'Voltage Regulator Replacement','Charging System Voltage Test', 'Battery Load Test', 'Electrical System Inspection', 'Battery Replacement'],
        'Corrosion' => ['Terminal Cleaning', 'Terminal Protectors', 'Battery Terminal Replacements', 'Sealant or Dielectric Grease', 'Regular Maintenance', 'Environmental Protection']
    ),
    'Light' => array (
        'Faulty Bulbs' => ['Bulb Socket Cleaning', 'Bulb Replacement', 'LED Conversion Kits', 'Halogen/HID Bulb Replacement'],
        'Electrical Wiring Problems' => ['Grounding Point Inspection', 'Wire Harness Repair Kit', 'Voltage Meter/Test Light', 'Wire Connectors/Terminals'],
        'Switch Malfunction' => ['Steering Column Covers', 'OEM Replacement Switches', 'Aftermarket Switches', 'Steering Column Switch Assembly'],
    ),
    'Oil' => array (
        'Oil Leaks' => ['Gasket Replacement', 'Seal Replacement', 'Oil Pan Replacement', 'Oil Filter Housing Gasket Replacement', 'Oil Cooler Replacement'],
        'Oil Consumption' => ['PCV Valve Replacement', 'Valve Stem Seal Replacement', 'Engine Overhaul/Rebuild', 'Oil Consumption Additives'],
        'Oil Contamination' => ['Oil Change', 'Oil Flush', 'Air Filter Replacement', 'Coolant System Inspection'],
    ),
    'Water' => array (
        'Coolant Leaks' => ['Radiator Hose Replacement', 'Radiator Cap Replacement', 'Radiator Replacement', 'Water Pump Replacement'],
        'Coolant Loss' => ['Coolant Reservoir Cap Replacement', 'Coolant Reservoir Replacement', 'Coolant System Pressure Test', 'Head Gasket Replacement'],
        'Coolant Contamination' => ['Flush and Refill Coolant', 'Thermostat Replacement', 'Cylinder Head Inspection', 'Heater Core Replacement'],
    ),
    'Brake' => array (
        'Brake Fluid Leaks' =>  ['Brake Hose Replacement', 'Brake Line Replacement', 'Brake Caliper Rebuild/Replacement'],
        'Brake Pad Wear' => ['Brake Pad Replacement', 'Brake Rotor Resurfacing/Replacement', 'Brake Caliper Inspection'],
        'Brake Fluid Contamination' => ['Brake Fluid Flush', 'Brake Master Cylinder Replacement', 'Brake Bleeding'],
    ),
    'Air' => array (
        'Air Filter Clogging' => ['Air Filter Replacement', 'Air Intake Hose Replacement', 'Mass Air Flow Sensor (MAF) Cleaning/Replacement'],
        'Vacuum Leaks' => ['Vacuum Hose Replacement', 'Intake Manifold Gasket Replacement', 'Throttle Body Gasket Replacement'],
        'Fuel System Issues' => ['Fuel Filter Replacement', 'Fuel Pump Replacement', 'Fuel Injector Cleaning/Replacement'],
    ),
    'Gas' => array (
        'Fuel System Leaks' => ['Fuel Line Replacement', 'Fuel Tank Replacement', 'Fuel Injector O-Ring Replacement'],
        'Fuel Pump Failure' => ['Fuel Pump Replacement', 'Fuel Pump Relay Replacement', 'Fuel Filter Replacement'],
        'Fuel Evaporation and Vapor Management' => ['Fuel Cap Replacement', 'Evaporative Emissions (EVAP) System Inspection', 'Vapor Canister Purge Solenoid Replacement'],
    ),
    'Tire' => array (
        'Tire Wear' => ['Tire Replacement', 'Wheel Alignment', 'Tire Rotation'],
        'Tire Punctures or Damage' => ['Tire Patch or Plug', 'Tire Repair Kit', 'Spare Tire Installation'],
        'Underinflation or Overinflation' => ['Tire Pressure Monitoring System', '(TPMS) Sensor Replacement', 'Tire Pressure Gaugem', 'Tire Inflation Tools'],
    )
);

function displaySectionSelect($problem_parts_mapping)
{
    echo '<label for="section-select"></label>';
    echo '<select id="section-select" onchange="navigateToSection()" style="padding: 8px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px;">';
    echo '<option value="" style="color: #999;">Select Section</option>';
    foreach ($problem_parts_mapping as $problem => $parts) {
        echo '<option value="' . $problem . '" style="color: #333;">' . $problem . '</option>';
    }
    echo '</select>';
}


// Function to display problem parts with price and quantity inputs and show real-time receipt
function displayProblemParts($problem_parts_mapping, $user_id, $car_id)
{
    // Display the form
    echo '<form method="post" action="save_checkbox.php">'; 
    echo '<input type="hidden" name="user_id" value="' . $user_id . '">';
    echo '<input type="hidden" name="car_id" value="' . $car_id . '">';
    echo '<input type="hidden" name="mechanic_id" value="' . $_SESSION['mechanic_id'] . '">';

    // Display checkboxes for problem parts
    foreach ($problem_parts_mapping as $problem => $parts) {
        echo '<div id="' . str_replace(' ', '_', $problem) . '" class="for-major-container bg-gray-100 p-4 rounded-md shadow-md mb-4" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">';
        echo '<h2 class="text-2xl font-bold mb-4">' . $problem . '</h2>';
        echo '<div class="grid grid-cols-3 gap-4">';
        foreach ($parts as $part => $subparts) {
            echo '<div>'; // Start a new grid item
            echo '<strong style="margin-bottom: 10px; display: block;">' . $part . '</strong>';
            foreach ($subparts as $subpart) {
                echo '<div style="margin-bottom: 10px;">'; // Start a new row for checkboxes
                // Include the category in the name attribute for uniqueness
                echo '<input type="checkbox" class="part-checkbox" id="' . str_replace(' ', '_', $subpart) . '" name="repairrecord[' . $problem . '][]" value="' . $subpart . '" style="margin-right: 5px;">';
                echo '<label for="' . str_replace(' ', '_', $subpart) . '" style="margin-right: 10px;">' . $subpart . '</label>';
    
                // Input field for quantity
                echo '<input type="number" class="quantity-input" name="quantity[' . $subpart . ']" value="0" min="0" style="width: 60px; margin-right: 5px;">';
    
                echo '</div>'; // End row
            }
            echo '<div style="margin-bottom: 10px;">';
            echo '<input type="text" name="other_product[' . $problem . '][' . str_replace(' ', '_', $part) . ']" placeholder="Other" style="margin-right: 5px;">';
            echo '</div>';
            echo '</div>'; // End grid item
        }
        echo '</div>';
        echo '</div>';
    }
    

    
    // Receipt section
    echo '<div class="receipt" style="border: 1px solid #ccc; padding: 10px; margin: 10px; max-width: 400px; width: 100%;">';
    echo '<h3 style="margin-bottom: 10px;">Quotation</h3>';
    echo '<table id="receipt-table" style="width: 100%; border-collapse: collapse; border: 1px solid #000;">';
    echo '<tr><th style="border: 1px solid #000; padding: 8px;">Part</th><th style="border: 1px solid #000; padding: 8px;">Quantity</th></tr>';
    echo '</table>';
    echo '</div>';


       // Display the form submit button
    echo '<div class="mt-4" style="margin-left: 50px;">';
    echo '<button type="submit" name="submit" value="Submit" class="btn btn-primary">Proceed</button>';
    // echo '<a href="homemechanic.php" class="btn btn-danger">Invalid</a>';
    echo '<br>';
    echo '</div>';
    echo '</form>'; 


    echo '<script>';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '  var checkboxes = document.querySelectorAll(".part-checkbox, .quantity-input");';
    echo '  checkboxes.forEach(function(checkbox) {';
    echo '    checkbox.addEventListener("change", updateReceipt);';
    echo '  });';
    echo '  document.getElementById("submit-btn").addEventListener("click", submitForm);';
    echo '  updateReceipt();'; // Update receipt initially
    echo '});';

    echo 'function updateReceipt() {';
    echo '  var table = document.getElementById("receipt-table");';
    echo '  var checkboxes = document.querySelectorAll(".part-checkbox:checked");';
    echo '  table.innerHTML = "<tr><th>Part</th><th>Quantity</th></tr>";';
    echo '  checkboxes.forEach(function(checkbox) {';
    echo '    var quantityInput = checkbox.parentElement.querySelector(".quantity-input");';
    echo '    var quantity = parseInt(quantityInput.value);';
    echo '    table.innerHTML += "<tr><td>" + checkbox.value + "</td><td>" + quantity + "</td></tr>";';
    echo '  });';
    echo '}';
        


    echo 'function submitForm() {';
    echo '  document.querySelector("form").submit();';
    echo '}';
    echo '</script>';

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

    <!-- For profile -->
    <ul class="flex justify-normal items-center mt-8" id="container">
    <li>
        <?php if (empty($user_image)): ?>
            <img src="images/default-avatar.png" class="w-20 h-20 rounded-full">
        <?php else: ?>
            <!-- Use image.php to serve the BLOB image -->
            <img src="image.php?id=<?php echo $user_id; ?>" class="w-20 h-20 rounded-full">
        <?php endif; ?>
    </li>
            <li class="px-4"><p class="mb-2 font-medium"><strong>User Name</strong>: <?php echo '<span class="font-normal">'.$user_name.'</span>'; ?></p></li>
            <li class="px-4"><p class="mb-2 font-medium"><strong>Manufacturer</strong>: <?php echo '<span class="font-normal">'.$manuname.'</span>'; ?></p></li>
            <li class="px-4"><p class="mb-2 font-medium"><strong>Car Model</strong>: <?php echo '<span class="font-normal">'.$carmodel.'</span>'; ?></p></li>
            <li class="px-4"><p class="mb-2 font-medium"><strong>Plate #</strong>: <?php echo '<span class="font-normal">'.$plateno.'</span>'; ?></p></li>
        </ul>
         <center><hr style="width: 1200px; ; border: 3px solid black;"></center>
         <br>

    <div class="for-major-container bg-white p-4 rounded-md shadow-md mb-4">
    <h2 class="text-2xl font-bold mb-4">Primary Engine System</h2>
    <div style="margin-left: 1050px;">
        <?php
            // Call the function to display the select menu for navigation
        displaySectionSelect($problem_parts_mapping);
        ?>
    </div>
    <br>

     <div class="bg-gray-100 p-4 rounded-md shadow-md grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (!empty($engine_overhaul_data)) { ?>
                <div>
                    <strong>Mechanical Issues:</strong>
                    <?php echo implode(', ', $engine_overhaul_data); ?>
                </div>
            <?php }  ?>

            <?php if (!empty($engine_low_power_data)) { ?>
                <div>
                    <strong>Fuel and Air intake System:</strong>
                    <?php echo implode(', ', $engine_low_power_data); ?>
                </div>
            <?php }  ?>

            <?php if (!empty($electrical_problem_data)) { ?>
                <div>
                    <strong>Cooling and Lubrication:</strong>
                    <?php echo implode(', ', $electrical_problem_data); ?>
                </div>
            <?php }  ?>
        </div>
    </div>

    <!-- Maintenance -->
    
    <div class="for-maintenance-container bg-white p-4 mt-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Maintenance</h2>

    <div class="bg-gray-100 p-4 rounded-md shadow-md grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (!empty($battery_data)) { ?>
            <div>
                <strong>Battery:</strong>
                <?php echo implode(', ', $battery_data); ?>
            </div>
        <?php }  ?>

        <?php if (!empty($light_data)) { ?>
            <div>
                <strong>Light:</strong>
                <?php echo implode(', ', $light_data); ?>
            </div>
        <?php } ?>

        <?php if (!empty($oil_data)) { ?>
            <div>
                <strong>Oil:</strong>
                <?php echo implode(', ', $oil_data); ?>
            </div>
        <?php } ?>

        <?php if (!empty($water_data)) { ?>
            <div>
                <strong>Water:</strong>
                <?php echo implode(', ', $water_data); ?>
            </div>
        <?php }  ?>

        <?php if (!empty($brake_data)) { ?>
            <div>
                <strong>Brake:</strong>
                <?php echo implode(', ', $brake_data); ?>
            </div>
        <?php }  ?>

        <?php if (!empty($air_data)) { ?>
            <div>
                <strong>Air:</strong>
                <?php echo implode(', ', $air_data); ?>
            </div>
        <?php }  ?>

        <?php if (!empty($gas_data)) { ?>
            <div>
                <strong>Gas:</strong>
                <?php echo implode(', ', $gas_data); ?>
            </div>
        <?php } ?>

        <?php if (!empty($tire_data)) { ?>
            <div>
                <strong>Tire:</strong>
                <?php echo implode(', ', $tire_data); ?>
            </div>
        <?php }  ?>
    </div>
</div>

<br>
<br>
<center><hr style="width: 1200px; ; border: 3px solid black;"></center>
<br>
<div>
    <h3 style="padding-left: 20px; font-weight: 700;">Parts</h3>
    <br>
    <?php
    // Call the function to display checkboxes
    displayProblemParts($problem_parts_mapping, $user_id, $car_id);

    ?>
</div>

<button onclick="scrollToTop()" class="scroll-to-top" style="position: fixed; top: 200px; width: 200px; height: 200px; right: 20px; background-color: #007bff; color: #ffffff; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer; z-index: 9999; opacity: 0; transition: opacity 0.3s ease;">Scroll to Top</button>


<script>
function navigateToSection() {
    var selectElement = document.getElementById("section-select");
    var selectedSection = selectElement.value;
    if (selectedSection) {
        var sectionId = selectedSection.replace(/ /g, '_');
        var sectionElement = document.getElementById(sectionId);
        if (sectionElement) {
            sectionElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
}
</script>

<script>
// JavaScript code for scrolling to top and showing the button
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: "smooth" // For smooth scrolling behavior
    });
}

// Show the button when user scrolls down
window.onscroll = function() {
    var button = document.querySelector('.scroll-to-top');
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        button.classList.add('show');
    } else {
        button.classList.remove('show');
    }
};
</script>
    
</body>
</html>
    