<?php
session_start();

// Redirect to login.php if the user is not logged in or session variable is not set
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Include the database configuration file
include 'config.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Perform the query to fetch unique car information for the specific user including progress data
$query = "SELECT manufacturer.name AS manuname, car.carmodel, car.color, car.car_id, MAX(accomplishtask.progress_percentage) AS progress_percentage
          FROM car
          LEFT JOIN accomplishtask ON car.car_id = accomplishtask.car_id
          LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
          WHERE car.user_id = $user_id
          GROUP BY car.car_id"; // Group by car_id to avoid duplicates

$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="css/home-second.css">
    <link rel="stylesheet" href="css/carusers.css">
</head>

<body class="bg-gray-100">
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
                <a href="home.php" class="sidebar-link">
                    <span class="active" style="margin-left: 13px;">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
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
            <a href="index.php" target="_blanck" class="sidebar-link">
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
    
    <!-- Sectioning -->
    <section class="absolute top-20 left-20 h-screen" style="width: 1290px;">
        
        <div class="container" style="margin-top: 5px;">
        <br>
        <h1>Welcome to <?php echo isset($company_info['companyname']) ? $company_info['companyname'] : ''; ?></h1>
        <br>

        <div class="container mt-5">
        <h2>User Vehicles</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Manufacturer</th>
                    <th>Car Model</th>
                    <th>Color</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are any rows returned
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Determine the progress color and status based on percentage
                        $progressColor = '';
                        $progressStatus = '';
                        if ($row['progress_percentage'] <= 0) {
                            $progressColor = 'text-info';
                            $progressStatus = 'No Progress';
                        } elseif ($row['progress_percentage'] < 80) {
                            $progressColor = 'text-danger';
                            $progressStatus = 'Under Repair';
                        } elseif ($row['progress_percentage'] < 100) {
                            $progressColor = 'text-warning';
                            $progressStatus = 'Almost Done';
                        } else {
                            $progressColor = 'text-success';
                            $progressStatus = 'Done';
                        }

                        // Retrieve additional information from the accomplishtask table
                        $additionalInfo = ""; // Placeholder for additional information
                        $car_id = $row['car_id']; // Now we can access car_id since it is included in the query
                        $queryAdditional = "SELECT nameprogress, progressing, progressingpercentage 
                                            FROM accomplishtask 
                                            WHERE car_id = $car_id"; // Use the car_id retrieved above
                        $resultAdditional = mysqli_query($conn, $queryAdditional);

                        if ($resultAdditional) {
                            while ($info = mysqli_fetch_assoc($resultAdditional)) {
                                $additionalInfo .= "<strong>{$info['nameprogress']}</strong>: {$info['progressing']} ({$info['progressingpercentage']})<br>";
                            }
                        }
                        echo "<tr>";
                        echo "<td>{$row['manuname']}</td>";
                        echo "<td>{$row['carmodel']}</td>";
                        echo "<td>{$row['color']}</td>";
                        echo "<td class='$progressColor' onclick='toggleDetails(this)' data-details='$additionalInfo'>$progressStatus ({$row['progress_percentage']}%)</td>";
                        echo "</tr>";
                        echo "<tr class='details' style='display:none;'><td colspan='4' class='details-content'></td></tr>"; // Row for additional details
                    }
                } else {
                    echo "<tr><td colspan='4'>No vehicles found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <script>
        function toggleDetails(element) {
            const detailsRow = element.parentElement.nextElementSibling; // Get the next row
            const detailsContent = detailsRow.querySelector('.details-content'); // Get the content cell

            // Toggle the display of the details row
            if (detailsRow.style.display === 'none') {
                detailsContent.innerHTML = element.getAttribute('data-details'); // Set the additional info
                detailsRow.style.display = 'table-row'; // Show the details row
            } else {
                detailsRow.style.display = 'none'; // Hide the details row
            }
        }
        </script>

        </div>
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
