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
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : null;

// Perform the modified query to include the manufacturer's name
$car_select = mysqli_query($conn, "SELECT car.*, manufacturer.name AS manuname FROM car LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id WHERE car.user_id = '$user_id'");

// Check if the query was successful
if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}

// Retrieve the companyname parameter
if(isset($_GET['companyname'])){
    $companyname = $_GET['companyname'];
}else{
    // Handle the case where companyname is not provided
    // For example, redirect to a different page or show an error message
}

// Perform the query to fetch additional information about the selected card based on companyname
$company_select = mysqli_query($conn, "SELECT * FROM autoshop WHERE companyname = '$companyname'");

// Check if the query was successful
if (!$company_select) {
    die('Error in company query: ' . mysqli_error($conn));
}

// Fetch the additional information about the selected card
$company_info = mysqli_fetch_assoc($company_select);

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="css/home-second.css">
    <link rel="stylesheet" href="css/carusers.css">
</head>

<body>
    <nav class="fixed w-full h-20 bg-black flex justify-between items-center px-4 text-gray-100 font-medium">
        <ul>
           <li></li>
           <li></li>
        </ul>
    </nav>
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
                    <span class="active" style="margin-left: 13px;">Dashboard</span>
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
               <a href="vehicleuser.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="sidebar-link">
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
                        <span>Appointent</span>
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
            <a href="index.php" target="_blanck" class="sidebar-link">
                <i class="lni "></i>
                <span>Logout</span>
            </a>
            </div>
        </aside>
        <div class="main p-3">
            <div class="text-center bg-secondary">
                <li></li>
            </div>
        </div>
    </div>
    
    <!-- Sectioning -->
    <section class="absolute top-20 left-64 h-screen" style="width: 80%;">
        
        <div class="container" style="margin-top: 5px;">
        <br>
        <h1>Welcome to <?php echo isset($company_info['companyname']) ? $company_info['companyname'] : ''; ?></h1>
        <br>

        <div style="margin-left: 70%; font-weight: 600;">
        <a href="shop.php?companyid=<?php echo $company_info['companyid']; ?>">Shop</a>
        </div>

            <h2 class="text-xl font-bold">Your Vehicle</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Manufacturer</th>
                        <th>model</th>
                        <th>Plate No</th>
                        <th>color</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // Check if there are rows to fetch
                    while ($row = mysqli_fetch_assoc($car_select)) :
                    ?>
                       <tr>
                            <td><?php echo isset($row['manuname']) ? $row['manuname'] : ''; ?></td>
                            <td><?php echo isset($row['carmodel']) ? $row['carmodel'] : ''; ?></td>
                            <td><?php echo isset($row['plateno']) ? $row['plateno'] : ''; ?></td>
                            <td><?php echo isset($row['color']) ? $row['color'] : ''; ?></td>
                            <td><a href="carprofile.php?car_id=<?php echo $row['car_id']; ?>&companyname=<?php echo $company_info['companyname']; ?>">View Profile</a></td>
                      </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="container">



    </section>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="nav.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Swiper -->
    <script src="home.js"></script>

</body>

</html>