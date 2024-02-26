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

// Perform the query to retrieve cars from all users
$car_select = mysqli_query($conn, "SELECT car.*, user.name as username, validation.status as validation_status, validation.comment
                                    FROM car 
                                    JOIN user ON car.user_id = user.id
                                    LEFT JOIN validation ON car.user_id = validation.user_id AND car.car_id = validation.car_id");

// Check if the query was successful
if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home mechanic</title>
    <link rel="stylesheet" href="css/homemechanic.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
        <nav class="navbar navbar-expand-lg bg-black">
            <div class="container-fluid">
                <a class="navbar-brand text-white" href="#">Mechanic</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" aria-current="page" href="repairtable.php">Job</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger"></span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

    <!-- displaying the table -->
    <div id="table-content-placeholder"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- For comment -->
    <script>
    $(document).ready(function() {
        // Function to load table content
        function loadTableContent() {
            $.get('table_content.php', function(data) {
                $('#table-content-placeholder').html(data);
            });
        }

        // Load table content initially
        loadTableContent();

        // Refresh table content every second
        setInterval(loadTableContent, 1000);

        // Check for new rows every 5 seconds
        setInterval(function() {
            $.get('check_for_new_rows.php', function(data) {
                // Update notification badge with count of new rows
                $('#notification-badge').text(data);
            });
        }, 5000);

        // Event listener for invalid comment links
        $(document).on('click', '.invalid-comment-link', function(e) {
            e.preventDefault();
            var comment = $(this).data('comment');
            $('#invalidComment').text('Invalid Comment: ' + comment);
        });
    });
</script>

    
</body>
</html>
