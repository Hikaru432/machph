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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 750px;
            margin: 20px auto;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .btn-custom {
            background: linear-gradient(to right, #8b002a, #c50040);
            color: white;
            font-weight: bold;
            border: none;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: linear-gradient(to right, #c50040, #8b002a);
        }
       
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .navbar {
            background-color: black;
            padding: 10px;
        }
        .navbar-brand, .nav-link {
            color: white !important;
            font-size: 18px;
        }
        #addForms {
            display: none;
            padding: 20px;
            border-radius: 12px;
            background-color: #f1f1f1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-secondary {
            margin-top: 15px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="home.php">Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h3 class="text-center text-primary mb-3" style="color: #c50040;">Register Vehicle</h3>
        
        <?php if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="alert alert-info">' . $msg . '</div>';
            }
        } ?>
        
        <form action="" method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plate Number</label>
                    <input type="text" name="plateno" class="form-control" required>
                    
                    <label class="form-label mt-2">Manufacturer</label>
                    <select name="manufacturer" id="manufacturer" class="form-select" required>
                        <option value="">Select Manufacturer</option>
                        <?php while ($manufacturer = mysqli_fetch_assoc($manufacturer_query)) {
                            echo '<option value="' . $manufacturer['id'] . '">' . $manufacturer['name'] . '</option>';
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2">Car Model</label>
                    <select name="carmodel" id="carmodel" class="form-select" required>
                        <option value="">Select Model</option>
                    </select>
                    
                    <label class="form-label mt-2">Body Number</label>
                    <input type="text" name="bodyno" class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" required>
                        <option value="">Select Year</option>
                        <?php for ($year = 1990; $year <= date("Y"); $year++) {
                            echo "<option value='$year'>$year</option>";
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2">Engine CC</label>
                    <select name="enginecc" class="form-select" required>
                        <option value="">Select CC</option>
                        <?php for ($cc = 100; $cc <= 1000; $cc += 50) {
                            echo "<option value='$cc'>$cc</option>";
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2">Color</label>
                    <select name="color" class="form-select" required>
                        <option value="">Select Color</option>
                        <option value="Red">Red</option>
                        <option value="Blue">Blue</option>
                        <option value="Black">Black</option>
                        <option value="White">White</option>
                    </select>
                    
                    <label class="form-label mt-2">Gas Type</label>
                    <select name="gas" class="form-select" required>
                        <option value="Regular">Regular</option>
                        <option value="Premium">Premium</option>
                        <option value="Diesel">Diesel</option>
                    </select>
                </div>
                
                <div class="col-12 text-center mt-3">
                    <button type="submit" name="submit" class="btn btn-custom w-100">Register Now</button>
                    <p class="mt-3">Back to <a href="home.php" class="text-primary">Home</a></p>
                </div>
            </div>
        </form>
    </div>

    <div class="text-center">
        <button class="btn btn-secondary" id="toggleForm">Add Manufacturer/Model</button>
    </div>
    
    <div class="card mt-4" id="addForms">
        <h4 class="text-center text-secondary">Add Manufacturer</h4>
        <form action="" method="post">
            <input type="text" name="manufacturer_name" class="form-control mb-2" placeholder="Manufacturer Name" required>
            <button type="submit" name="add_manufacturer" class="btn btn-success w-100">Add Manufacturer</button>
        </form>
        <hr>
        <h4 class="text-center text-secondary">Add Vehicle Model</h4>
        <form action="" method="post">
            <input type="text" name="car_model_name" class="form-control mb-2" placeholder="Car Model Name" required>
            <select name="manufacturer_id" class="form-select mb-2" required>
                <option value="">Select Manufacturer</option>
                <?php mysqli_data_seek($manufacturer_query, 0);
                while ($manufacturer = mysqli_fetch_assoc($manufacturer_query)) {
                    echo '<option value="' . $manufacturer['id'] . '">' . $manufacturer['name'] . '</option>';
                } ?>
            </select>
            <button type="submit" name="add_car_model" class="btn btn-success w-100">Add Model</button>
        </form>
    </div>
</div>

<script>
    $('#toggleForm').click(function () {
        $('#addForms').slideToggle();
    });

    $('#manufacturer').change(function () {
        $.post('get_carmodels.php', { manufacturer_id: $(this).val() }, function(response) {
            $('#carmodel').html(response);
        });
    });
</script>
</body>
</html>
