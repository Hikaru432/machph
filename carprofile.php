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

$select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'") or die('query failed');
if (mysqli_num_rows($select) > 0) {
    $fetch = mysqli_fetch_assoc($select);
} else {
    die('No user found');
}

// Retrieve car_id parameter from the URL
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];

    // Fetch specific car data based on car_id
    $car_select = mysqli_query($conn, "SELECT * FROM car WHERE user_id = '$user_id' AND car_id = '$car_id'");

    if (mysqli_num_rows($car_select) > 0) {
        $car_data = mysqli_fetch_all($car_select, MYSQLI_ASSOC);

        // Fetch service data for the given car from the service table
        $service_select = mysqli_query($conn, "SELECT * FROM service WHERE user_id = '$user_id' AND car_id = '$car_id'");
        if (mysqli_num_rows($service_select) > 0) {
            $service_data = mysqli_fetch_all($service_select, MYSQLI_ASSOC);
        } else {
            $service_data = array(); // Initialize as an empty array
        }
    } else {
        $car_data = array(); // Initialize as an empty array
    }
} else {
    // Handle the case where car_id is not set in the URL
    die('Car ID not specified.');
}

// for saving data to service table
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
    $sql = "INSERT INTO service (user_id, eo, elp, ep, battery, light, oil, water, brake, air, gas, tire, car_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssss",
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
        $car_id // Use the retrieved car_id
    );

    $stmt->execute();
    $stmt->close();

    echo '<script>alert("Done submit");</script>';

// Redirect back to carusers.php
    echo '<script>window.location.href = "carusers.php";</script>';
    exit();;
 }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>Profile</title>
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/carprofile.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <div class="container-navbar-home">
        <div class="wrapper">
            <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                <ion-icon style="color:white; font-size: 35px; margin-left: -10px;" name="grid-outline"></ion-icon>
                </button>
                <div class="sidebar-logo">
                    <a href="home.php">MachPH</a>
                </div>
            </div>
            <ul class="sidebar-nav">
            <li class="sidebar-item">
              <!--  <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                    <a href="home.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
               <!-- <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                    <a href="profile.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Profile</span>
                    </a>
                </li>
                <li class="sidebar-item">
               <!-- <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                    <a href="carusers.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Car user</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-protection"></i>
                        <span>Auth</span>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="register.php" class="sidebar-link">User register</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="carregistration.php" class="sidebar-link">Car register</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="update_profile.php" class="sidebar-link">Update profile</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Appointment</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                <li class="sidebar-item">
                                    <a href="identify.php" class="sidebar-link">Identifying</a>
                                </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Notification</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Setting</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
            <a href="login.php" target="_blanck" class="sidebar-link">
                <i class="lni lni-exit"></i>
                <span>Logout</span>
            </a>
            </div>
            </aside>
            <div class="main p-3">
                <div class="text-center bg-secondary">
                    <!-- ... (unchanged) ... -->
                </div>
            </div>
        </div>
    </div>
   <!-- Sectioning -->
   <section class="carprofile-section-1">
        <div class="container-car-profile">
            <div class="car-profile">
                <?php
                    if ($fetch['image'] == '') {
                        echo '<img src="images/default-avatar.png">';
                    } else {
                        echo '<img src="uploaded_img/' . $fetch['image'] . '">';
                    }
                ?>
                <h3><?php echo $fetch['name']; ?></h3>
                <h3><?php echo isset($car_data['name']) ? $car_data['name'] : ''; ?></h3>
            </div>
        </div>

        <div class="car-info">
            <?php if (!empty($car_data)) : ?>
                <div class="fullname-container-carprofile-1">
                    <?php foreach ($car_data as $car) : ?>
                        <h4 class="carprofile-container-1">Plate No:<p class="profile-box"><?php echo $car['plateno']; ?></p></h4>
                        <h4 class="carprofile-container-2">Manufacturer:<p class="profile-box"><?php echo $car['manufacturer']; ?></p></h4>
                        <h4 class="carprofile-container-3">Car Model:<p class="profile-box"><?php echo $car['carmodel']; ?></p></h4>
                        <h4 class="carprofile-container-4">Year:<p class="profile-box"><?php echo $car['year']; ?></p></h4>
                        <h4 class="carprofile-container-5">Body no:<p class="profile-box"><?php echo $car['bodyno']; ?></p></h4>
                        <h4 class="carprofile-container-6">Engine cc:<p class="profile-box"><?php echo $car['enginecc']; ?></p></h4>
                        <h4 class="carprofile-container-7">Gas:<p class="profile-box"><?php echo $car['gas']; ?></p></h4>
                        <!-- ... (continue with other fields) ... -->
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>


    </section>

       <!-- Upper nav -->

       <div class="nav-container">
        <div class="upper-nav-container">
                <div class="img-icon-upper-nav">
                    <li><img class="upper-nav-icon-three" src="img/facebook.png" alt="facebook"></li>
                    <li><img class="upper-nav-icon-three" src="img/instagram.png" alt="instagram"></li>
                    <li><img class="upper-nav-icon-three-chrome"src="img/chrome.png" alt="chrome"></li>
                </div>
        
            <div class="upper-nav-icons-info">
                <li><img src="img/gmail.png" alt=""><span>hikaru@gmail.com</span></li>
                <li><img src="img/call.png" alt=""><span>0948-501-1228</span></li> 
            </div>

            <div class="upper-nav-button">
                <a href="">Appointment</a>
            </div>
        </div>
    </div>

<!-- For section 2 -->



    <section class="carprofile-section-2">
     <div class="major-maintenance-container">
        <form method="post" action="">
            <div class="data-major-container">
              <h1>Major</h1>
                <label for="eo">Engine Overhaul <input class="data-major-major-boxes" type="checkbox" name="eo" value="1"></label>
                <label for="elp">Engine Low Power <input class="data-major-major-boxes" type="checkbox" name="elp" value="2"></label>
                <label for="ep">Electrical Problems <input class="data-major-major-boxes" type="checkbox" name="ep" value="3"></label>

            </div>
            
         <div class="data-maintenance-container">
            <h2>Maintenance</h2>
        <div class="maintenance-range-alignment"></div>
            <div class="battery-check-box">
                <label for="battery">Battery</label>
                <input type="range" name="battery" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'batteryLabel')">
                <span class="box-range" id="batteryLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="light">Light</label>
                <input class="box-1" type="range" name="light" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'lightLabel')">
                <span  class="box-range box-1" id="lightLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="oil">Oil</label>
                <input  class="box-2" type="range" name="oil" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'oilLabel')">
                <span  class="box-range" id="oilLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="water">Water</label>
                <input  class="box-3" type="range" name="water" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'waterLabel')">
                <span  class="box-range" id="waterLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="brake">Brake</label>
                <input  class="box-4" type="range" name="brake" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'brakeLabel')">
                <span  class="box-range" id="brakeLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="air">Air</label>
                <input  class="box-5" type="range" name="air" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'airLabel')">
                <span  class="box-range" id="airLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="gas">Gas</label>
                <input  class="box-6" type="range" name="gas" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'gasLabel')">
                <span  class="box-range" id="gasLabel">Normal</span>
            </div>
            <div class="battery-check-box">
                <label for="tire">Tire</label>
                <input  class="box-7" type="range" name="tire" min="1" max="3" step="1" value="1" oninput="updateLabel(this, 'tireLabel')">
                <span  class="box-range" id="tireLabel">Normal</span>
            </div>
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
              <button type="submit">Submit</button>
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
