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

// Perform the query
$car_select = mysqli_query($conn, "SELECT * FROM car WHERE user_id = '$user_id'");

// Check if the query was successful
if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
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
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="css/home-second.css">
    <link rel="stylesheet" href="css/carusers.css">
</head>

<body>
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
            <a href="login.php" target="_blanck" class="sidebar-link">
                <i class="lni lni-exit"></i>
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
                <a href="carprofile.php">Appointment</a>
            </div>
        </div>
    </div>

    <!-- Sectioning -->
    <section class="cars-container">
        
        <div class="container">
            <h2>Your Cars</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Car Name</th>
                        <th>Plate No</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // Check if there are rows to fetch
                    while ($row = mysqli_fetch_assoc($car_select)) :
                    ?>
                       <tr>
                        <td><?php echo isset($row['manufacturer']) ? $row['manufacturer'] : ''; ?></td>
                        <td><?php echo isset($row['plateno']) ? $row['plateno'] : ''; ?></td>
                        <td><a href="carprofile.php?car_id=<?php echo $row['car_id']; ?>">View Profile</a></td>
                </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>


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