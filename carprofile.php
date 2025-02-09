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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <title>Profile</title>
        <link rel="stylesheet" href="css/carprofile.css">

        <style>
            body {
                background-color: #f8f9fa;
                }

                .navbar {
                    background-color: #b30036 !important;
                }

                .navbar-brand,
                .nav-link {
                    color: white !important;
                }
                
                .fonts-size {
                    font-size: 16px;
                    font-weight: 600;
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
    
    <!-- Sectioning -->
    <section class="carprofile-section-1" style="width: 800px; left: 5px; top: 57px; height: 1500px;">
    <div class="container car-profile-container my-5">
        <div class="text-center car-profile">
            <?php
                if (empty($fetch['image'])) {
                    echo '<img src="images/default-avatar.png" alt="Default Avatar" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">';
                } else {
                    // Display the BLOB image using image.php script
                    echo '<img src="image.php?id=' . $fetch['id'] . '" alt="User Image" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">';
                }
            ?>
            <h1 class="font-weight-bold text-primary"><?php echo $fetch['name']; ?></h1>
        </div>
    </div>

            <div class="car-info">
                <!-- Display car information -->
                <div class="fullname-container-carprofile-1">
                    <h4 class="carprofile-container-1"><strong>Plate No:</strong><p class="profile-box"><?php echo $car_data['plateno']; ?></p></h4>
                    <h4 class="carprofile-container-2"><strong>Manufacturer:</strong><p class="profile-box"><?php echo $car_data['manuname']; ?></p></h4>
                    <h4 class="carprofile-container-3"><strong>Car Model:</strong><p class="profile-box"><?php echo $car_data['carmodel']; ?></p></h4>
                    <h4 class="carprofile-container-4"><strong>Year:</strong><p class="profile-box"><?php echo $car_data['year']; ?></p></h4>
                    <h4 class="carprofile-container-5"><strong>Body no:</strong><p class="profile-box"><?php echo $car_data['bodyno']; ?></p></h4>
                    <h4 class="carprofile-container-6"><strong>Engine cc:</strong><p class="profile-box"><?php echo $car_data['enginecc']; ?></p></h4>
                    <h4 class="carprofile-container-7"><strong>Color:</strong><p class="profile-box"><?php echo $car_data['color']; ?></p></h4>
                    <h4 class="carprofile-container-7"><strong>Gas:</strong><p class="profile-box"><?php echo $car_data['gas']; ?></p></h4>
                </div>
            </div>

                <!-- For Booking container -->

                <div style="position: relative; margin-left: 20px; margin-top: 50px;">
                    <div>
                    <!-- <h2 class="text-xl font-bold">Bookings</h2> -->
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">Booking</a>

                    <!-- Booking Modal -->
                    
                    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bookingModalLabel">Book a Date</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Hidden input to hold the companyname for the booking -->
                                    <input type="hidden" id="companyname" value="<?php echo htmlspecialchars($companyname); ?>">

                                    <!-- Car selection dropdown -->
                                    <label for="carSelect">Select Your Car:</label>
                                    <select id="carSelect" class="form-select mb-3">
                                        <option value="">-- Choose a Car --</option>
                                        <?php
                                        // Fetch cars for the logged-in user
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
                                            <!-- Booked dates will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>


    <!-- Include required JavaScript files -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


                    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
                    <!-- For booking -->

                    <script>
                    $(document).ready(function() {
                        // Initialize the calendar but keep it hidden
                        $("#calendar").datepicker({
                            dateFormat: 'yy-mm-dd',
                            onSelect: function(dateText) {
                                // AJAX request to store the booking
                                const selectedCarId = $('#carSelect').val();
                                const companyid = $('#companyid').val(); // Fetch the companyid from the hidden input

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
                                    companyname: '<?php echo $companyname; ?>' // Use companyname from the session
                                },
                                success: function(response) {
                                    alert(response);
                                    loadBookings(selectedCarId); // Pass car_id to loadBookings
                                }
                            });
                            }
                        });

                        // Show/hide the calendar based on car selection
                        $('#carSelect').change(function() {
                            if ($(this).val()) {
                                $("#calendar").show(); // Show the calendar when a car is selected
                                loadBookings($(this).val()); // Load bookings for the selected car
                            } else {
                                $("#calendar").hide(); // Hide the calendar when no car is selected
                                $('#bookingTable tbody').empty(); // Clear bookings when no car is selected
                            }
                        });

                        // Fetch and display bookings for the selected car
                        function loadBookings(carId) {
                            $.ajax({
                                url: 'fetch_bookings.php',
                                method: 'GET',
                                data: { 
                                    user_id: <?php echo $user_id; ?>, 
                                    car_id: carId 
                                }, // Include car_id
                                success: function(data) {
                                    $('#bookingTable tbody').html(data);
                                }
                            });
                        }

                        // Initial load of bookings (no car selected)
                        $('#carSelect').trigger('change'); // Trigger change to load bookings for the initially selected car if any
                    });


                    </script>
                    
                    </div>
                </div>

            <div>

        </section>



    <!-- For section 2 -->

        <section class="carprofile-section-2" style="left: 800px; width: 1100px; top: 57px;  height: 1500px;">
        <div class="major-maintenance-container" style="margin-left: 300px;">
            <form method="post" action="">  
                <br>
                <br>
                <br>
                <br>
                <div class="data-major-container">
                <h1 style="font-size: 40px;"><strong>Primary Engine System</strong></h1>
                <br>
                    <label class="fonts-size" for="eo">Mechanical Issues <input class="data-major-major-boxes" type="checkbox" name="eo" value="1"></label>
                    <label class="fonts-size" for="elp">Fuel and Air intake System  <input class="data-major-major-boxes" type="checkbox" name="elp" value="2"></label>
                    <label class="fonts-size" for="ep">Cooling and Lubrication <input class="data-major-major-boxes" type="checkbox" name="ep" value="3"></label>

                </div>
                <br>
                <br>
                <h1 style="font-size: 40px;"><strong>Maintenance</strong></h1>
                <br>
            <div class="data-maintenance-container">
            <div class="maintenance-range-alignment"></div>
                <div class="battery-check-box fonts-size">
                    <label for="battery">Battery</label>
                    <input type="range" name="battery" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'batteryLabel')">
                    <span class="box-range" id="batteryLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="light">Light</label>
                    <input class="box-1" type="range" name="light" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'lightLabel')">
                    <span  class="box-range box-1" id="lightLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="oil">Oil</label>
                    <input  class="box-2" type="range" name="oil" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'oilLabel')">
                    <span  class="box-range" id="oilLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="water">Water</label>
                    <input  class="box-3" type="range" name="water" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'waterLabel')">
                    <span  class="box-range" id="waterLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="brake">Brake</label>
                    <input  class="box-4" type="range" name="brake" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'brakeLabel')">
                    <span  class="box-range" id="brakeLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="air">Air</label>
                    <input  class="box-5" type="range" name="air" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'airLabel')">
                    <span  class="box-range" id="airLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="gas">Gas</label>
                    <input  class="box-6" type="range" name="gas" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'gasLabel')">
                    <span  class="box-range" id="gasLabel">Normal</span>
                </div>
                <br>
                <div class="battery-check-box fonts-size">
                    <label for="tire">Tire</label>
                    <input  class="box-7" type="range" name="tire" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'tireLabel')">
                    <span  class="box-range" id="tireLabel">Normal</span>
                </div>
                <br>
                <br>
                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                <input type="hidden" name="companyid" value="<?php echo $autoshop_data['companyid']; ?>">
                <button type="submit" style="margin-left: 500px;">Submit</button>
            </div> 


                </div>
            </form>
        </div>

        
            
        </section>
    

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"></script>
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
