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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
    /* Add these custom styles to the head section */
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-urgent {
        background-color: rgba(239, 68, 68, 0.1);
        color: rgb(239, 68, 68);
    }

    .status-normal {
        background-color: rgba(34, 197, 94, 0.1);
        color: rgb(34, 197, 94);
    }

    .status-above-normal {
        background-color: rgba(234, 179, 8, 0.1);
        color: rgb(234, 179, 8);
    }

    .checkbox-card {
        transition: all 0.2s ease-in-out;
    }

    .checkbox-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    </style>

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




<div class="container-fluid px-6 py-8 bg-gray-50">
    <!-- Add the form tag here -->
    <form action="update_repair.php" method="post">
        <!-- Hidden input fields -->
        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
        <input type="hidden" name="mechanic_id" value="<?php echo $mechanic_id; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="approval_status" id="approval_status" value="">

        <!-- Profile section -->
        <div class="max-w-full bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-100">
            <ul class="flex flex-wrap gap-6 items-center">
                <li class="flex-shrink-0">
                    <?php
                    if (isset($user_data['image']) && !empty($user_data['image'])) {
                        echo '<img src="image.php?id=' . $user_data['id'] . '" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">';
                    } else {
                        echo '<img src="images/default-avatar.png" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">';
                    }
                    ?>
                </li>
                <li class="flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <p class="mb-2"><span class="font-semibold text-gray-700">User Name:</span><br><?php echo '<span class="text-gray-600">'. $user_data['name'] . '</span>'; ?></p>
                        <p class="mb-2"><span class="font-semibold text-gray-700">Manufacturer:</span><br><?php echo '<span class="text-gray-600">'. $car_data['manufacturer_name'] . '</span>'; ?></p>
                        <p class="mb-2"><span class="font-semibold text-gray-700">Car Model:</span><br><?php echo '<span class="text-gray-600">'. $car_data['carmodel'] . '</span>'; ?></p>
                        <p class="mb-2"><span class="font-semibold text-gray-700">Plate #:</span><br><?php echo '<span class="text-gray-600">'. $car_data['plateno'] . '</span>'; ?></p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Primary Engine System section -->
        <div class="max-w-full bg-white rounded-xl shadow-sm p-8 mb-8 border border-gray-100">
            <h1 class="text-3xl font-bold mb-8 text-gray-800 border-b pb-4 flex items-center">
                <i class="fas fa-engine fa-fw mr-3 text-blue-600"></i>
                Primary Engine System
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Engine overhaul -->
                <div id="eo_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-cogs fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Mechanical Issues</span>
                        <?php if ($service_data['eo'] == 1): ?>
                            <span class="status-badge status-urgent ml-2">Urgent Need</span>
                        <?php endif; ?>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_overhaul_problems[]" value="Piston and Piston Rings" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Piston</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_overhaul_problems[]" value="Valve Train" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Valve Train</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_overhaul_problems[]" value="Timing Chain or Belt" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Timing Belt</span>
                        </label>
                    </div>
                </div>

                <!-- Engine low power -->
                <div id="elp_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-gas-pump fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Fuel and Air intake System</span>
                        <?php if ($service_data['elp'] == 2): ?>
                            <span class="status-badge status-urgent ml-2">Urgent Need</span>
                        <?php endif; ?>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_low_power_problems[]" value="Fuel Injection" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Fuel Injection</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_low_power_problems[]" value="Air Filter" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Air Filter</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="engine_low_power_problems[]" value="Throttle Body" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Throttle Body</span>
                        </label>
                    </div>
                </div>

                <!-- Electrical problem -->
                <div id="ep_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-temperature-low fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Cooling and Lubrication</span>
                        <?php if ($service_data['ep'] == 3): ?>
                            <span class="status-badge status-urgent ml-2">Urgent Need</span>
                        <?php endif; ?>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="electrical_problems[]" value="Coolant Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Coolant Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="electrical_problems[]" value="Oil Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Oil Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="electrical_problems[]" value="Water Pump" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Water Pump</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance section -->
        <div class="max-w-full bg-white rounded-xl shadow-sm p-8 mb-8 border border-gray-100">
            <h1 class="text-3xl font-bold mb-8 text-gray-800 border-b pb-4 flex items-center">
                <i class="fas fa-wrench fa-fw mr-3 text-blue-600"></i>
                Maintenance
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Battery -->
                <div id="battery_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-car-battery fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Battery</span>
                        <span class="status-badge <?php echo ($service_data['battery'] == 1) ? 'status-normal' : 
                            (($service_data['battery'] == 2) ? 'status-above-normal' : 'status-urgent'); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['battery']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="battery_problems[]" value="Battery Age" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Battery Age</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="battery_problems[]" value="Overcharging or Undercharging" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Overcharging or Undercharging</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="battery_problems[]" value="Corrosion" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Corrosion</span>
                        </label>
                    </div>
                </div>

                <!-- Light -->
                <div id="light_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-lightbulb fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Light</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['light']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['light']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="light_problems[]" value="Faulty Bulbs" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Faulty Bulbs</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="light_problems[]" value="Inspect fuses" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Electrical Wiring Problems</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="light_problems[]" value="Check wirings" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Switch Malfunction</span>
                        </label>
                    </div>
                </div>

                <!-- Oil -->
                <div id="oil_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-oil-can fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Oil</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['oil']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['oil']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="oil_problems[]" value="Oil Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Oil Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="oil_problems[]" value="Oil Consumptionl" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Oil Consumption</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="oil_problems[]" value="Oil Contamination" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Oil Contamination</span>
                        </label>
                    </div>
                </div>

                <!-- Water -->
                <div id="water_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-tint fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Water</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['water']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['water']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="water_problems[]" value="Coolant Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Coolant Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="water_problems[]" value="Coolant Loss" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Coolant Loss</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="water_problems[]" value="Coolant Contamination" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Coolant Contamination</span>
                        </label>
                    </div>
                </div>

                <!-- Brake -->
                <div id="brake_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-brake fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Brake</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['brake']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['brake']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="brake_problems[]" value="Brake Fluid Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Brake Fluid Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="brake_problems[]" value="Brake Pad Wear" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Brake Pad Wear</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="brake_problems[]" value="Brake Fluid Contamination" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Brake Fluid Contamination</span>
                        </label>
                    </div>
                </div>
                
                <!-- Air -->
                <div id="air_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-wind fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Air</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['air']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['air']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="air_problems[]" value="Air Filter Clogging" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Air Filter Clogging</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="air_problems[]" value="Vacuum Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Vacuum Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="air_problems[]" value="Fuel System Issues" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Fuel System Issues</span>
                        </label>
                    </div>
                </div>

                <!-- Gas -->
                <div id="gas_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-gas-pump fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Gas</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['gas']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['gas']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="gas_problems[]" value="Fuel System Leaks" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Fuel System Leaks</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="gas_problems[]" value="Fuel Pump Failure" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Fuel Pump Failure</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="gas_problems[]" value="Fuel Evaporation and Vapor Management" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Fuel Evaporation and Vapor Management</span>
                        </label>
                    </div>
                </div>

                <!-- Tire -->
                <div id="tire_container" class="checkbox-card bg-white rounded-xl p-6 border border-gray-200">
                    <label class="block mb-6">
                        <i class="fas fa-tire fa-fw mr-2 text-blue-600"></i>
                        <span class="font-bold text-lg text-gray-800">Tire</span>
                        <span class="status-badge <?php echo mapMaintenanceStatusAndColor($service_data['tire']); ?> ml-2">
                            <?php echo mapMaintenanceStatus($service_data['tire']); ?>
                        </span>
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="tire_problems[]" value="Tire Wear" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Tire Wear</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="tire_problems[]" value="Tire Punctures or Damage" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Tire Punctures or Damage</span>
                        </label>
                        <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                            <input type="checkbox" name="tire_problems[]" value="Underinflation or Overinflation" 
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">Underinflation or Overinflation</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval section -->
        <div class="max-w-full bg-white rounded-xl shadow-sm p-8 mb-8 border border-gray-100">
            <div class="max-w-3xl mx-auto">
                <label class="block text-lg font-medium text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-check-circle fa-fw mr-2 text-blue-600"></i>
                    Approval Status
                </label>
                <div class="flex items-center space-x-8 mb-6">
                    <label class="inline-flex items-center p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" id="approve" name="approval_status" value="1" checked
                               class="form-radio h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="ml-3 text-gray-700 font-medium">Approve</span>
                    </label>
                    <label class="inline-flex items-center p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <input type="radio" id="not_approve" name="approval_status" value="0"
                               class="form-radio h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="ml-3 text-gray-700 font-medium">Not Approve</span>
                    </label>
                </div>
                
                <select name="not_approve_reason" id="not_approve_reason" 
                        class="hidden w-full p-4 text-gray-700 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="">Select Reason</option>
                    <option value="Lack of expertise">Lack of expertise</option>
                    <option value="Lack of tools">Lack of tools</option>
                    <option value="No available parts">No available parts</option>
                </select>

                <button type="submit" class="mt-8 w-full md:w-auto px-8 py-4 bg-blue-600 text-white font-medium rounded-lg 
                                          hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 
                                          transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Submit Assessment</span>
                </button>
            </div>
        </div>
    </form>
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