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

        <div class="container mt-5">
        <h2>User Vehicles</h2>

        <button type="button" class="btn-message" id="messageButton" style="margin-left: 1000px;">
            Notification <span id="notificationDot" style="display:none; color: #ffff3f;">●</span>
        </button>

        <div id="messageModal" class="modal">
            <div class="modal-content">
                <span class="close" style="margin-left: 550px;">&times;</span>
                <h2 style="margin-left: 10px;">Notification</h2>
                <table id="carList" class="table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Car Model</th>
                            <th>Body No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Car list will be populated here -->
                    </tbody>
                </table>
                <div id="carDetails" style="display: none;">
                    <div id="detailContent"></div>
                    <br>
                    <button id="backButton" class="btn-back">Back to List</button> <!-- Back button -->
                </div>
            </div>
        </div>

        <!-- Modal Functionality -->

        <script>
           let lastCarCount = 0; 
           let lastApprovedCount = 0; 

            // Function to load car list
            function loadCarList() {
                fetch('get_car_list.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log(data); // Debugging line
                        let carListBody = '';
                        let currentCarCount = data.length; // Get the current car count
                        let currentApprovedCount = 0; // Initialize approved count

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(car => {
                                carListBody += `<tr onclick="showCarDetails(${car.car_id})">
                                                    <td>${car.companyname}</td>
                                                    <td>${car.carmodel}</td>
                                                    <td>${car.bodyno}</td>
                                                </tr>`;

                                // Check if the car has an approved status
                                if (car.status === '1') {
                                    currentApprovedCount++; // Increment approved count
                                }
                            });

                            // Check if there are new approved cars
                            if (currentApprovedCount > lastApprovedCount) {
                                document.getElementById('notificationDot').style.display = 'inline'; // Show the notification dot
                            }

                            lastCarCount = currentCarCount; // Update last car count
                            lastApprovedCount = currentApprovedCount; // Update last approved count
                        } else if (data.error) {
                            carListBody = `<tr><td colspan="3">${data.error}</td></tr>`;
                        } else {
                            carListBody = '<tr><td colspan="3">No cars found.</td></tr>';
                        }

                        document.querySelector('#carList tbody').innerHTML = carListBody;
                    })
                    .catch(error => console.error('Error fetching car list:', error));
            }

            // Load the car list every 3 seconds
            setInterval(loadCarList, 3000); // Refresh car list every 3 seconds

            // Show the modal and load car list
            document.getElementById('messageButton').onclick = function() {
                document.getElementById('messageModal').style.display = 'block';
                loadCarList();
                document.getElementById('notificationDot').style.display = 'none'; // Hide notification when opened
            };

            // Close modal functionality
            document.getElementsByClassName('close')[0].onclick = function() {
                document.getElementById('messageModal').style.display = 'none';
            };

            // Close modal when clicking outside of it
            window.onclick = function(event) {
                if (event.target == document.getElementById('messageModal')) {
                    document.getElementById('messageModal').style.display = 'none';
                }
            };

            // Back button functionality
            document.getElementById('backButton').onclick = function() {
                document.getElementById('carDetails').style.display = 'none'; // Hide car details
                document.getElementById('carList').style.display = 'table'; // Show the car list again
            };

            </script>
            <!-- Car Details -->

            <script>
                function showCarDetails(carId) {
                    console.log(`Fetching details for car ID: ${carId}`); // Log the car ID
                    fetch(`get_car_details.php?car_id=${carId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Car details data:", data); // Log car details data
                            let detailHtml = `
                                <h2>Car Details</h2>
                                <p><strong>Company Name:</strong> ${data.companyname}</p>
                                <p><strong>Car Model:</strong> ${data.carmodel}</p>
                                <p><strong>Mechanic Name:</strong> ${data.firstname}</p>
                                <p><strong>Status:</strong> ${data.status === '1' ? 'Approved' : 'Not Approved'}`;
                            if (data.status === '0') {
                                detailHtml += ` - Reason: ${data.reason}`;
                            }
                            detailHtml += `</p>`;
                            
                            document.querySelector('#detailContent').innerHTML = detailHtml; // Update modal content
                            document.getElementById('carList').style.display = 'none'; // Hide the car list
                            document.getElementById('carDetails').style.display = 'block'; // Show car details
                        })
                        .catch(error => console.error('Error fetching car details:', error));
                }

                // Event listener for back button
                document.getElementById('backButton').onclick = function() {
                    document.getElementById('carDetails').style.display = 'none'; // Hide car details
                    document.getElementById('carList').style.display = 'table'; // Show the car list again
            };

        </script>

        <!-- Table for Car list -->

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
                $showModal = false; // Track if the modal needs to be shown
                $triggerModal = false; // Flag to handle modal triggers for specified ranges

                // Check if there are any rows returned
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $progressColor = '';
                        $progressStatus = '';
                        $progressPercentage = $row['progress_percentage'];

                        if ($progressPercentage <= 0) {
                            $progressColor = 'text-info';
                            $progressStatus = 'No Progress';
                        } elseif ($progressPercentage < 80) {
                            $progressColor = 'text-danger';
                            $progressStatus = 'Under Repair';
                        } elseif ($progressPercentage < 100) {
                            $progressColor = 'text-warning';
                            $progressStatus = 'Almost Done';
                        } else {
                            $progressColor = 'text-success';
                            $progressStatus = 'Done';
                        }

                        // Check if the modal should be triggered for specific ranges
                        if (
                            ($progressPercentage >= 15 && $progressPercentage <= 25) ||
                            ($progressPercentage >= 50 && $progressPercentage <= 60) ||
                            ($progressPercentage >= 80 && $progressPercentage <= 85) ||
                            $progressPercentage == 100
                        ) {
                            $triggerModal = true;
                        }

                        // Retrieve additional information from the accomplishtask table
                        $additionalInfo = "";
                        $car_id = $row['car_id'];
                        $queryAdditional = "SELECT nameprogress, progressing, progressingpercentage 
                                            FROM accomplishtask 
                                            WHERE car_id = $car_id";
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
                        echo "<td class='$progressColor' onclick='toggleDetails(this)' data-details='$additionalInfo'>$progressStatus ({$progressPercentage}%)</td>";
                        echo "</tr>";
                        echo "<tr class='details' style='display:none;'><td colspan='4' class='details-content'></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No vehicles found.</td></tr>";
                }
                ?>
                <?php if ($triggerModal): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            openModal(); // Automatically open the modal when the page loads
                        });
                    </script>
                <?php endif; ?>
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


<!-- Modal Structure -->
<div id="surveyModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Survey Prompt</h5>
                <button type="button" class="close" onclick="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>How is your experience. Would you like to proceed to the survey?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Not Now</button>
                <button type="button" class="btn btn-primary" onclick="proceedToSurvey()">Proceed</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to open the modal
    function openModal() {
        const modal = document.getElementById('surveyModal');
        modal.style.display = 'block';
    }

    // Function to close the modal
    function closeModal() {
        const modal = document.getElementById('surveyModal');
        modal.style.display = 'none';
    }

    // Function to proceed to the survey page
    function proceedToSurvey() {
        window.location.href = 'survey.php'; // Redirect to survey.php
    }
</script>

<style>
    /* Basic styling for the modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-dialog {
        position: relative;
        margin: auto;
        top: 20%;
        width: 50%;
    }
    .modal-content {
        background-color: #fff;
        border-radius: 5px;
        padding: 15px;
    }
    .modal-header, .modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

        <script>
            function handleClick(element) {
                // Toggle Details
                const detailsRow = element.parentElement.nextElementSibling;
                const detailsContent = detailsRow.querySelector('.details-content');

                if (detailsRow.style.display === 'none') {
                    detailsContent.innerHTML = element.getAttribute('data-details');
                    detailsRow.style.display = 'table-row';
                } else {
                    detailsRow.style.display = 'none';
                }

                // Check Progress
                const progress = parseInt(element.getAttribute('data-progress'));
                if (progress === 100) {
                    openModal();
                }
            }

            // Modal Functions
            function openModal() {
                const modal = document.getElementById('surveyModal');
                modal.style.display = 'block';
            }

            function closeModal() {
                const modal = document.getElementById('surveyModal');
                modal.style.display = 'none';
            }

            function proceedToSurvey() {    
                window.location.href = 'survey.php'; // Redirect to survey page
            }
        </script>

        </div>
        </div>
    </section>

    <style>
       /* Modal background overlay */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width for overlay */
            height: 100%; /* Full height for overlay */
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        }

        /* Modal content */
        .modal-content {
            position: absolute; /* Positioning context */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Offset the modal */
            width: 80%; /* Modal width */
            max-width: 600px; /* Maximum width */
            background-color: white; /* Modal background */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3); /* Enhanced shadow */
            animation: fadeIn 0.5s; /* Fade-in animation */
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Close button styles */
        .close {
            color: #ff4d4d; /* Red color for close button */
            float: right; /* Align close button to the right */
            font-size: 28px; /* Increase font size */
            font-weight: bold; /* Make bold */
            cursor: pointer; /* Pointer cursor */
        }

        /* Close button hover effect */
        .close:hover,
        .close:focus {
            color: #c00000; /* Darker red on hover */
        }

        /* Table styles */
        .table {
            width: 100%; /* Full width */
            border-collapse: collapse; /* No spacing between cells */
        }

        .table th, .table td {
            padding: 12px; /* Increased padding for cells */
            border: 1px solid #ddd; /* Border for cells */
            text-align: left; /* Left-align text */
            transition: background-color 0.3s; /* Smooth transition for hover */
        }

        .table th {
            background-color: #4a5759; /* Blue background for headers */
            color: white; /* White text for headers */
        }

        .table tr:hover {
            background-color: #f1f1f1; /* Light gray background on row hover */
        }

       /* Car Details Section Styles */
        #carDetails {
            background-color: #ffffff; /* White background for clarity */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            padding: 20px; /* Padding for inner spacing */
            margin-top: 20px; /* Space above the section */
            max-width: 600px; /* Maximum width */
            margin-left: auto; /* Centering */
            margin-right: auto; /* Centering */
        }

        /* Title Styles */
        #carDetails h2 {
            font-size: 24px; /* Larger font size for the title */
            color: #333; /* Dark gray color for the title */
            margin-bottom: 15px; /* Space below the title */
            text-align: center; /* Center the title */
        }

        /* Detail Content Styles */
        #detailContent {
            background-color: #f9f9f9; /* Light gray background for contrast */
            border: 1px solid #ddd; /* Light border for structure */
            border-radius: 5px; /* Rounded corners */
            padding: 15px; /* Padding inside the detail content */
            line-height: 1.6; /* Improved line height for readability */
            color: #555; /* Dark gray text color */
        }

        /* Media Query for Responsive Design */
        @media (max-width: 768px) {
            #carDetails {
                padding: 15px; /* Less padding on smaller screens */
            }

            #carDetails h2 {
                font-size: 20px; /* Smaller font size on small screens */
            }
        }

        /* Back to List Button Styles */
        .btn-back {
            display: inline-block; /* Inline block for button styling */
            padding: 10px 15px; /* Padding for button size */
            background-color: #007bff; /* Blue background color */
            color: white; /* White text color */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            margin-top: 15px; /* Space above the button */
        }

        /* Back button hover effect */
        .btn-back:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }



        /* Button */

        .btn-message {
            padding: 10px 20px; /* Vertical and horizontal padding */
            background-color: #007bff; /* Bootstrap primary color */
            color: white; /* White text color */
            border: none; /* Remove default border */
            border-radius: 5px; /* Rounded corners */
            font-size: 16px; /* Font size */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
        }

        /* Button hover effect */
        .btn-message:hover {
            background-color: #0056b3; /* Darker shade on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        /* Button active effect */
        .btn-message:active {
            background-color: #004494; /* Even darker shade when clicked */
            transform: translateY(0); /* Reset the lift effect */
        }
    

    </style>

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