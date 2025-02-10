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
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            background: white;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-custom {
            background: linear-gradient(45deg, #8b002a, #c50040);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-custom:hover {
            background: linear-gradient(45deg, #c50040, #8b002a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(197, 0, 64, 0.3);
            color: white;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #c50040;
            box-shadow: 0 0 0 0.2rem rgba(197, 0, 64, 0.25);
        }

        .navbar {
            background: #b30036 !important;
            /* padding: 15px; */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: 70px
        }

        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: white;
        }

        .navbar .nav-link {
            color: white !important;
        }

        .navbar .nav-link:hover {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            background: linear-gradient(45deg, #e3f2fd, #bbdefb);
            color: #0d47a1;
        }

        /* Hide the addForms section by default */
        #addForms {
            display: none;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .text-primary {
            color: #c50040 !important;
        }

        h3, h4 {
            font-weight: 600;
            margin-bottom: 25px;
            color: #c50040;
        }

        hr {
            margin: 25px 0;
            opacity: 0.1;
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        /* Animation for form appearance */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: slideIn 0.5s ease-out;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                padding: 20px;
            }
            
            .btn-custom, .btn-secondary, .btn-success {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>

    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="home.php">Dashboard</a>
    </div>
</nav>

<br>
<br>

<div class="container">
    <div class="card">
        <h3 class="text-center mb-4">
            <i class="fas fa-car mr-2"></i> Register Vehicle
        </h3>
        
        <?php if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="alert alert-info">' . $msg . '</div>';
            }
        } ?>
        
        <form action="" method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-id-card mr-2"></i>Plate Number</label>
                    <input type="text" name="plateno" class="form-control" required>
                    
                    <label class="form-label mt-2"><i class="fas fa-truck mr-2"></i>Manufacturer</label>
                    <select name="manufacturer" id="manufacturer" class="form-select" required>
                        <option value="">Select Manufacturer</option>
                        <?php while ($manufacturer = mysqli_fetch_assoc($manufacturer_query)) {
                            echo '<option value="' . $manufacturer['id'] . '">' . $manufacturer['name'] . '</option>';
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2"><i class="fas fa-car-side mr-2"></i>Car Model</label>
                    <select name="carmodel" id="carmodel" class="form-select" required>
                        <option value="">Select Model</option>
                    </select>
                    
                    <label class="form-label mt-2"><i class="fas fa-car-side mr-2"></i>Body Number</label>
                    <input type="text" name="bodyno" class="form-control" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-calendar-alt mr-2"></i>Year</label>
                    <select name="year" class="form-select" required>
                        <option value="">Select Year</option>
                        <?php for ($year = 1990; $year <= date("Y"); $year++) {
                            echo "<option value='$year'>$year</option>";
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2"><i class="fas fa-tachometer-alt mr-2"></i>Engine CC</label>
                    <select name="enginecc" class="form-select" required>
                        <option value="">Select CC</option>
                        <?php for ($cc = 100; $cc <= 1000; $cc += 50) {
                            echo "<option value='$cc'>$cc</option>";
                        } ?>
                    </select>
                    
                    <label class="form-label mt-2"><i class="fas fa-paint-brush mr-2"></i>Color</label>
                    <select name="color" class="form-select" required>
                        <option value="">Select Color</option>
                        <option value="Red">Red</option>
                        <option value="Blue">Blue</option>
                        <option value="Black">Black</option>
                        <option value="White">White</option>
                    </select>
                    
                    <label class="form-label mt-2"><i class="fas fa-gas-pump mr-2"></i>Gas Type</label>
                    <select name="gas" class="form-select" required>
                        <option value="Regular">Regular</option>
                        <option value="Premium">Premium</option>
                        <option value="Diesel">Diesel</option>
                    </select>
                </div>
                
                <div class="col-12 text-center mt-3">
                    <button type="submit" name="submit" class="btn btn-custom">
                        <i class="fas fa-check-circle mr-2"></i>Register Now
                    </button>
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
    $(document).ready(function() {
        // Hide addForms section initially (redundant but good practice)
        $('#addForms').hide();

        // Toggle form visibility with animation
        $('#toggleForm').click(function() {
            $('#addForms').slideToggle(400, 'swing');
        });

        $(document).ajaxStart(function() {
            $('#carmodel').html('<option>Loading...</option>');
        });

        $('#manufacturer').change(function() {
            const manufacturerId = $(this).val();
            if (manufacturerId) {
                $.post('get_carmodels.php', { 
                    manufacturer_id: manufacturerId 
                }, function(response) {
                    $('#carmodel').html(response).fadeIn();
                });
            } else {
                $('#carmodel').html('<option value="">Select Model</option>');
            }
        });
    });
</script>
</body>
</html>
