<?php
include 'config.php';

session_start(); // Start the session

if (isset($_POST['submit'])) {
    // Check if the necessary form fields are set
    if (
        isset($_POST['plateno']) && isset($_POST['manufacturer']) &&
        isset($_POST['carmodel']) && isset($_POST['year']) &&
        isset($_POST['bodyno']) && isset($_POST['enginecc']) && isset($_POST['gas'])
    ) {
        // Retrieve car details from the form
        $plateno = mysqli_real_escape_string($conn, $_POST['plateno']);
        $manufacturer_id = mysqli_real_escape_string($conn, $_POST['manufacturer']);
        $carmodel = mysqli_real_escape_string($conn, $_POST['carmodel']);
        $year = mysqli_real_escape_string($conn, $_POST['year']);
        $bodyno = mysqli_real_escape_string($conn, $_POST['bodyno']);
        $enginecc = mysqli_real_escape_string($conn, $_POST['enginecc']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $gas = mysqli_real_escape_string($conn, $_POST['gas']);

        // Retrieve the manufacturer name from the selected manufacturer_id
        $manufacturer_query = mysqli_query($conn, "SELECT name FROM manufacturer WHERE id = '$manufacturer_id'");
        $manufacturer_row = mysqli_fetch_assoc($manufacturer_query);
        $manuname = $manufacturer_row['name'];

        // Check if 'plateno' already exists in the 'car' table
        $check_plateno_query = mysqli_query($conn, "SELECT * FROM car WHERE plateno = '$plateno'");

        if (mysqli_num_rows($check_plateno_query) > 0) {
            // 'plateno' already exists, display an error message or handle accordingly
            $message[] = 'Car with Plate number ' . $plateno . ' already registered.';
        } else {
            // Retrieve user information from the session
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Proceed with the car registration
                $insert = mysqli_query($conn, "INSERT INTO car (plateno, manufacturer_id, carmodel, year, bodyno, enginecc, gas, user_id, color) 
                VALUES('$plateno', '$manufacturer_id', '$carmodel', '$year', '$bodyno', '$enginecc', '$gas', '$user_id','$color')");

                if ($insert) {
                    $message[] = 'Register another car!';
                } else {
                    $message[] = 'Registration failed!';
                }
            } else {
                // Session user_id not set, display an error message
                $message[] = 'User information not available. Cannot register the car.';
            }
        }
    } else {
        $message[] = 'All form fields are required.';
    }
}

// Retrieve user information from the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Perform the query to retrieve all cars associated with the user
    $car_select = mysqli_query($conn, "SELECT * FROM car WHERE user_id = '$user_id'");

    // Check if the query was successful
    if (!$car_select) {
        die('Error in car query: ' . mysqli_error($conn));
    }
}

// Handle form submission to add a new manufacturer
if(isset($_POST['add_manufacturer'])) {
    $manufacturer_name = mysqli_real_escape_string($conn, $_POST['manufacturer_name']);
    
    $insert_manufacturer = mysqli_query($conn, "INSERT INTO manufacturer (name) VALUES ('$manufacturer_name')");
    
    if($insert_manufacturer) {
        $message[] = 'Manufacturer added successfully!';
    } else {
        $message[] = 'Failed to add manufacturer.';
    }
}

// Handle form submission to add a new car model
if(isset($_POST['add_car_model'])) {
    $car_model_name = mysqli_real_escape_string($conn, $_POST['car_model_name']);
    $manufacturer_id = mysqli_real_escape_string($conn, $_POST['manufacturer_id']);
    
    $insert_car_model = mysqli_query($conn, "INSERT INTO car_model (name, manufacturer_id) VALUES ('$car_model_name', '$manufacturer_id')");
    
    if($insert_car_model) {
        $message[] = 'Vehicle model added successfully!';
    } else {
        $message[] = 'Failed to add vehicle model.';
    }
}

// Retrieve list of manufacturers from the database
$manufacturer_query = mysqli_query($conn, "SELECT * FROM manufacturer");
if (!$manufacturer_query) {
    die('Error in manufacturer query: ' . mysqli_error($conn));
}

// Fetch manufacturers from the database
$manufacturer_query = mysqli_query($conn, "SELECT * FROM manufacturer");

if (!$manufacturer_query) {
    die('Error in manufacturer query: ' . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Car</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>

<body>
<div class="form-container">
    <form action="" method="post" enctype="multipart/form-data">
        <h3>Register Vehicle</h3>
        <?php
        if (isset($message)) {
            foreach ($message as $message) {
                echo '<div class="message">' . $message . '</div>';
            }
        }
        ?>
        <input type="text" name="plateno" placeholder="Enter Plate number" class="box" required>

        <select name="manufacturer" placeholder="Enter manufacturer" id="manufacturer" class="box">
            <option>select manufacturer</option>
            <?php
            // Display manufacturers from the database
            while ($manufacturer = mysqli_fetch_assoc($manufacturer_query)) {
                echo '<option value="' . $manufacturer['id'] . '">' . $manufacturer['name'] . '</option>';
            }
            ?>
        </select>
        <div class="flex justify-between items-center" style="margin-left: 220px;">
            <span class="font-bold" style="font-size: 12px; margin-right: 5px; margin-top: 8px;">Manufacturer & Vehicle model</span>
            <button class="bg-gray-300 w-12 h-6 mt-2 " style="border-radius: 5px; font-size: 15px;" type="button" id="addManufacturerBtn" class="btn">
                Add</button>
        </div>
        <select name="carmodel" placeholder="Enter car model" class="box" id="carmodel">
            <option>select vehicle model</option>
        </select>
        <select name="year" class="box" required>
            <option value="">Select Year</option>
            <?php
            // Generate options for years from 1990 to the current year
            $currentYear = date("Y");
            for ($year = 1990; $year <= $currentYear; $year++) {
                echo "<option value=\"$year\">$year</option>";
            }
            ?>
        </select>
        <input type="text" name="bodyno" placeholder="Enter body number" class="box" required>
        <select name="color" class="box" required>
            <option value="">Select Color</option>
            <option value="Red">Red</option>
            <option value="Blue">Blue</option>
            <option value="Green">Green</option>
            <option value="Black">Black</option>
            <option value="White">White</option>
            <option value="Silver">Silver</option>
            <option value="Gray">Gray</option>
            <option value="Yellow">Yellow</option>
            <option value="Orange">Orange</option>
            <option value="Purple">Purple</option>
            <option value="Brown">Brown</option>
        </select>

        <select name="enginecc" placeholder="Enter Engine cc" class="box" required>
            <option value="">Select cc</option>
            <option value="100">100</option>
            <option value="110">110</option>
            <option value="115">115</option>
            <option value="125">125</option>
            <option value="135">135</option>
            <option value="150">150</option>
            <option value="155">155</option>
            <option value="170">170</option>
            <option value="175">175</option>
            <option value="200">200</option>
            <option value="250">250</option>
            <option value="300">300</option>
            <option value="350">350</option>
            <option value="400">400</option>
            <option value="450">450</option>
            <option value="500">500</option>
            <option value="600">600</option>
            <option value="650">650</option>
            <option value="700">700</option>
            <option value="800">800</option>
            <option value="900">900</option>
            <option value="1000">1000</option>
        </select>

        <select name="gas" placeholder="Gas" class="box" required>
            <option value="Regular">Regular</option>
            <option value="Premium">Premium</option>
            <option value="Diesel">Diesel</option>
        </select>

        <input type="submit" name="submit" value="Register Now" class="btn">
        <p> Back to <a href="home.php"> home</a></p>
    </form>

    <div class="mx-9" id="addManufacturerForm" style="display: none;">
        <!-- Form to add a new manufacturer -->
        <form action="" method="post" class="mt-8">
            <h3>Add Manufacturer</h3>
            <input type="text" name="manufacturer_name" placeholder="Manufacturer Name" class="box" required>
            <button type="submit" name="add_manufacturer" class="btn">Add Manufacturer</button>
        </form>

        <!-- Form to add a new car model -->
        <form action="" method="post" class="mt-8">
            <h3>Add Vehicle Model</h3>
            <input type="text" name="car_model_name" placeholder="Car Model Name" class="box" required>
            <select name="manufacturer_id" class="box" required>
                <option value="">Select Manufacturer</option>
                <?php
                // Display manufacturers from the database
                mysqli_data_seek($manufacturer_query, 0); // Reset the pointer to the beginning
                while ($manufacturer = mysqli_fetch_assoc($manufacturer_query)) {
                    echo '<option value="' . $manufacturer['id'] . '">' . $manufacturer['name'] . '</option>';
                }
                ?>
            </select>
            <button type="submit" name="add_car_model" class="btn">Add Vehicle Model</button>
        </form>
    </div>
</div>

    <!-- For the sectioning -->

    

    <!-- ... (your existing script section) ... -->

    <!-- <script> -->
    

    <script>
    // Function to update car model options based on the selected manufacturer
    function updateCarModels() {
        var manufacturer_id = $('#manufacturer').val();
        $.ajax({
            url: 'get_carmodels.php',
            type: 'POST',
            data: {
                manufacturer_id: manufacturer_id
            },
            success: function(response) {
                $('#carmodel').html(response);
            }
        });
    }

    // Attach the function to the change event of the manufacturer dropdown
    $(document).ready(function() {
        $('#manufacturer').change(updateCarModels);
    });
</script>

<script>
    document.getElementById('addManufacturerBtn').addEventListener('click', function () {
        var addManufacturerForm = document.getElementById('addManufacturerForm');
        if (addManufacturerForm.style.display === 'none') {
            addManufacturerForm.style.display = 'block';
        } else {
            addManufacturerForm.style.display = 'none';
        }
    });
</script>
</body>
</html>
