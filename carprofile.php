    <?php
    session_start();


    // Redirect to login.php if the user is not logged in
    if (!isset($_SESSION['user_id'])) {
        header('location:login.php');
        exit();
    }

    // Redirect to login.php after logout
    if (isset($_GET['logout'])) {
        unset($_SESSION['user_id']);
        session_destroy();
        header('location:login.php');
        exit();
    }

    include 'config.php';

    $user_id = $_SESSION['user_id'];
    $companyid = isset($_POST['companyid']) ? $_POST['companyid'] : null;

    $select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($select) > 0) {
        $fetch = mysqli_fetch_assoc($select);
    } else {
        die('No user found');
    }

    // Retrieve car_id and companyname parameters from the URL
    if (isset($_GET['car_id']) && isset($_GET['companyname'])) {
        $car_id = $_GET['car_id'];
        $companyname = $_GET['companyname'];
        
        // Fetch specific car data based on car_id
        $car_select = mysqli_query($conn, "SELECT car.*, manufacturer.name AS manuname FROM car LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id WHERE car.user_id = '$user_id' AND car.car_id = '$car_id'");
        if (mysqli_num_rows($car_select) > 0) {
            $car_data = mysqli_fetch_assoc($car_select);
        } else {
            die('Car not found.');
        }
        
        // Fetch specific autoshop data based on companyname
        $autoshop_select = mysqli_query($conn, "SELECT * FROM autoshop WHERE companyname = '$companyname' OR companyid = '$companyid'");
        if (mysqli_num_rows($autoshop_select) > 0) {
            $autoshop_data = mysqli_fetch_assoc($autoshop_select);
        } else {
            die('Autoshop not found.');
        }
    } else {
        // Handle the case where car_id or companyname is not set in the URL
        die('Car ID or company name not specified.');
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve user information from the session
        $user_id = $_SESSION['user_id'];
    
        // Retrieve car_id from the form data
        $car_id = isset($_POST['car_id']) ? $_POST['car_id'] : null;
    
        // Validate and sanitize car_id
        $car_id = filter_var($car_id, FILTER_VALIDATE_INT);
    
        if ($car_id === false || $car_id === null) {
            die('Invalid car ID.');
        }
    
        // Perform the insertion into the service table
        $sql = "INSERT INTO service (user_id, eo, elp, ep, battery, light, oil, water, brake, air, gas, tire, car_id, companyid, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssss",
            $user_id,
            $_POST["eo"],
            $_POST["elp"],
            $_POST["ep"],
            $_POST["battery"],
            $_POST["light"],
            $_POST["oil"],
            $_POST["water"],
            $_POST["brake"],
            $_POST["air"],
            $_POST["gas"],
            $_POST["tire"],
            $car_id,
            $companyid
        );
    
        $stmt->execute();
        $stmt->close();
    
        // Update companyid in the user table
        $update_user_sql = "UPDATE user SET companyid = ? WHERE id = ?";
        $update_user_stmt = $conn->prepare($update_user_sql);
        $update_user_stmt->bind_param("ss", $companyid, $user_id);
        $update_user_stmt->execute();
        $update_user_stmt->close();
    
        // Update companyid in the car table
        $update_car_sql = "UPDATE car SET companyid = ? WHERE car_id = ?";
        $update_car_stmt = $conn->prepare($update_car_sql);
        $update_car_stmt->bind_param("ss", $companyid, $car_id);
        $update_car_stmt->execute();
        $update_car_stmt->close();
    
        echo '<script>alert("Done submit");</script>';
    
        // Redirect back to carusers.php
        echo '<script>window.location.href = "home.php";</script>';
        exit();
    }


    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Car Profile</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <style>
            :root {
                --primary-color: #b30036;
                --primary-hover: #8b002a;
                --secondary-color: #f8f9fa;
                --text-dark: #2c3e50;
                --text-light: #ffffff;
                --border-color: #e9ecef;
                --card-shadow: 0 2px 10px rgba(179, 0, 54, 0.1);
                --hover-shadow: 0 4px 15px rgba(179, 0, 54, 0.15);
            }

            body {
                background-color: var(--secondary-color);
                color: var(--text-dark);
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                line-height: 1.6;
            }

            .navbar {
                background-color: #b30036 !important;
                box-shadow: var(--card-shadow);
            }

            .navbar-brand, .nav-link {
                color: var(--text-light) !important;
            }

            .nav-link:hover {
                opacity: 0.9;
            }

            .main-container {
                display: flex;
                gap: 2rem;
                padding: 2rem;
                max-width: 1440px;
                margin: 0 auto;
            }

            .profile-container, .maintenance-container {
                flex: 1;
                min-width: 0;
            }

            .profile-section {
                background: var(--text-light);
                border-radius: 15px;
                box-shadow: var(--card-shadow);
                padding: 2rem;
                margin-bottom: 2rem;
                transition: all 0.3s ease;
            }

            .profile-image {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                border: 3px solid var(--primary-color);
                padding: 3px;
                margin-bottom: 1.5rem;
                transition: transform 0.3s ease;
            }

            .profile-image:hover {
                transform: scale(1.05);
            }

            .car-info {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .info-card {
                background: var(--secondary-color);
                padding: 1.25rem;
                border-radius: 10px;
                border: 1px solid var(--border-color);
                transition: all 0.3s ease;
            }

            .info-card:hover {
                border-color: #b30036;
                transform: translateY(-3px);
                box-shadow: var(--hover-shadow);
            }

            .maintenance-section {
                background: var(--text-light);
                border-radius: 15px;
                padding: 2rem;
                box-shadow: var(--card-shadow);
            }

            .checkbox-group {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
                margin: 1.5rem 0;
            }

            .custom-checkbox {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem;
                background: var(--secondary-color);
                border-radius: 10px;
                border: 1px solid var(--border-color);
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .custom-checkbox:hover {
                border-color: #b30036;
                transform: translateY(-2px);
                box-shadow: var(--card-shadow);
            }

            .range-slider {
                background: var(--secondary-color);
                padding: 1.25rem;
                border-radius: 10px;
                margin: 1rem 0;
                border: 1px solid var(--border-color);
                transition: all 0.2s ease;
            }

            .range-slider:hover {
                border-color: #b30036;
                box-shadow: var(--card-shadow);
            }

            .range-slider input[type="range"] {
                width: 60%;
                height: 6px;
                border-radius: 5px;
                background: #dee2e6;
            }

            .range-slider input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 18px;
                height: 18px;
                background: #b30036;
                border-radius: 50%;
                cursor: pointer;
                border: 2px solid var(--text-light);
                transition: all 0.2s ease;
            }

            .range-slider input[type="range"]::-webkit-slider-thumb:hover {
                transform: scale(1.1);
            }

            .badge {
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .badge.bg-primary {
                background-color: #b30036 !important;
            }

            .btn-custom, .btn-booking {
                background-color: #b30036;
                color: var(--text-light);
                padding: 0.75rem 2rem;
                border-radius: 25px;
                border: none;
                transition: all 0.3s ease;
            }

            .btn-custom:hover, .btn-booking:hover {
                background-color: #8b002a;
                color: var(--text-light);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(179, 0, 54, 0.2);
            }

            /* Modal Styling */
            .modal-content {
                border-radius: 15px;
                border: none;
                box-shadow: var(--hover-shadow);
            }

            .modal-header {
                background-color: #b30036;
                color: var(--text-light);
                border-radius: 15px 15px 0 0;
                padding: 1.5rem;
            }

            .modal-body {
                padding: 2rem;
            }

            .form-select {
                border-radius: 8px;
                padding: 0.75rem;
                border: 1px solid var(--border-color);
                transition: all 0.2s ease;
            }

            .form-select:focus {
                border-color: #b30036;
                box-shadow: 0 0 0 2px rgba(179, 0, 54, 0.1);
            }

            /* Section Headers */
            h3 {
                color: #b30036;
                font-weight: 600;
                margin-bottom: 1.5rem;
                position: relative;
                padding-bottom: 0.5rem;
            }

            h3::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 50px;
                height: 3px;
                background-color: #b30036;
                border-radius: 3px;
            }

            /* Responsive Design */
            @media (max-width: 992px) {
                .main-container {
                    flex-direction: column;
                }

                .car-info {
                    grid-template-columns: 1fr;
                }
            }

            /* Smooth Animations */
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .table th {
                background-color: #b30036 !important;
                color: white;
            }
        </style>
    </head>
    <body>

    <nav class="navbar navbar-expand-lg navbar-dark" style="width: 100%; font-size: 15px;">
            <div class="container">
                <a class="navbar-brand" href="home.php">MachPH</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav" style="margin-right: 100px;">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item "><a class="nav-link" href="home.php">Dashboard</a></li>
                        <li class="nav-item "><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item "><a class="nav-link" href="shop.php">Shop</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    </ul>
                </div>
            </div>
        </nav>  
    
    <div class="main-container">
        <!-- Left side - Profile Section -->
        <div class="profile-container">
            <div class="profile-section">
                <?php
                    if (empty($fetch['image'])) {
                        echo '<img src="images/default-avatar.png" alt="Default Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">';
                    } else {
                        echo '<img src="image.php?id=' . $fetch['id'] . '" alt="User Image" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">';
                    }
                ?>
                <h2 class="fw-bold text-primary mb-4"><?php echo $fetch['name']; ?></h2>
                
                <div class="car-info">
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Plate No</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['plateno']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Manufacturer</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['manuname']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Car Model</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['carmodel']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Year</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['year']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Body no</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['bodyno']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Engine cc</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['enginecc']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Color</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['color']; ?></p>
                    </div>
                    <div class="info-card">
                        <h6 class="text-muted mb-1">Gas</h6>
                        <p class="fw-bold mb-0"><?php echo $car_data['gas']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Booking button and modal -->
            <div class="text-center">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">Booking</a>
            </div>

                <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="bookingModalLabel">Book a Date</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="companyname" value="<?php echo htmlspecialchars($companyname); ?>">

                                <label for="carSelect">Select Your Car:</label>
                                <select id="carSelect" class="form-select mb-3">
                                    <option value="">-- Choose a Car --</option>
                                    <?php
                                    $car_query = "SELECT * FROM car WHERE user_id = '$user_id'";
                                    $car_result = mysqli_query($conn, $car_query);
                                    while ($car = mysqli_fetch_assoc($car_result)) {
                                        echo "<option value='{$car['car_id']}'>{$car['carmodel']} - {$car['plateno']}</option>";
                                    }
                                    ?>
                                </select>

                            <div id="calendar" style="display: none;"></div>

                            <table class="table mt-3" id="bookingTable">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Car Model</th>
                                        <th>Date</th>
                                        <th>Bookings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Maintenance Section -->
        <div class="maintenance-container">
            <div class="maintenance-section">
                <form method="post" action="">
                    <h3 class="mb-4">Primary Engine System</h3>
                    <div class="checkbox-group">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="eo" value="1">
                            Mechanical Issues
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="elp" value="2">
                            Fuel and Air intake System
                        </label>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="ep" value="3">
                            Cooling and Lubrication
                        </label>
                    </div>

                <h3 class="mt-5 mb-4">Maintenance Status</h3>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Battery</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="battery" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'batteryLabel')">
                            <span id="batteryLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Light</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="light" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'lightLabel')">
                            <span id="lightLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Oil</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="oil" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'oilLabel')">
                            <span id="oilLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Water</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="water" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'waterLabel')">
                            <span id="waterLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Brake</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="brake" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'brakeLabel')">
                            <span id="brakeLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Air</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="air" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'airLabel')">
                            <span id="airLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Gas</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="gas" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'gasLabel')">
                            <span id="gasLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>
                <div class="range-slider">
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Tire</span>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" name="tire" min="1" max="3" step="1" value="1" 
                                   oninput="updateLabel(this, 'tireLabel')">
                            <span id="tireLabel" class="badge bg-primary">Normal</span>
                        </div>
                    </label>
                </div>

                    <div class="text-center mt-5">
                        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                        <input type="hidden" name="companyid" value="<?php echo $autoshop_data['companyid']; ?>">
                        <button type="submit" class="btn btn-booking">Submit Maintenance Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include required JavaScript files -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


    <script>
    $(document).ready(function() {
        $("#calendar").datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(dateText) {
                const selectedCarId = $('#carSelect').val();
                const companyid = $('#companyid').val();

                if (!selectedCarId) {
                    alert("Please select a car first.");
                    return;
                }

                $.ajax({
                url: 'book_date.php',
                method: 'POST',
                data: { 
                    date: dateText, 
                    user_id: <?php echo $user_id; ?>, 
                    car_id: selectedCarId,
                    companyname: '<?php echo $companyname; ?>'
                },
                success: function(response) {
                    alert(response);
                    loadBookings(selectedCarId);
                }
            });
            }
        });

        $('#carSelect').change(function() {
            if ($(this).val()) {
                $("#calendar").show();
                loadBookings($(this).val());
            } else {
                $("#calendar").hide();
                $('#bookingTable tbody').empty();
            }
        });

        function loadBookings(carId) {
            $.ajax({
                url: 'fetch_bookings.php',
                method: 'GET',
                data: { 
                    user_id: <?php echo $user_id; ?>, 
                    car_id: carId 
                },
                success: function(data) {
                    $('#bookingTable tbody').html(data);
                }
            });
        }

        $('#carSelect').trigger('change');
    });


    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="nav.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
    function updateLabel(input, labelId) {
        var label = document.getElementById(labelId);

        switch (input.value) {
            case '1':
                label.textContent = 'Normal';
                break;
            case '2':
                label.textContent = 'Above normal';
                break;
            case '3':
                label.textContent = 'Repair';
                break;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateLabel(document.querySelector('input[name="battery"]'), 'batteryLabel');
        updateLabel(document.querySelector('input[name="light"]'), 'lightLabel');
        updateLabel(document.querySelector('input[name="oil"]'), 'oilLabel');
        updateLabel(document.querySelector('input[name="water"]'), 'waterLabel');
        updateLabel(document.querySelector('input[name="brake"]'), 'brakeLabel');
        updateLabel(document.querySelector('input[name="air"]'), 'airLabel');
        updateLabel(document.querySelector('input[name="gas"]'), 'gasLabel');
        updateLabel(document.querySelector('input[name="tire"]'), 'tireLabel');
    });
    </script>

    

    </body>
    </html>
