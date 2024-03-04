<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the role of mechanic
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Retrieve mechanic details from the database based on the session user_id
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM mechanic WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$mechanic = mysqli_fetch_assoc($result);

// Retrieve user details from the database based on the user_id
$query_user = "SELECT name FROM user WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);
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
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">Home</a>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php">Job</a> 
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger">0</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-white" href="#">Action</a></li>
                        <li><a class="dropdown-item text-white" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<h1>Welcome <?php echo $user['name']; ?></h1>
<div id="table-content-placeholder">
    <!-- Table content will be loaded here -->
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
    function loadTableContent() {
        $.get('table_content.php', function(data) {
            $('#table-content-placeholder').html(data);
        }).fail(function() {
            console.log('Failed to load table content');
        });
    }

    loadTableContent();

    function reloadTable() {
        $.get('table_content.php', function(data) {
            var oldRowCount = $('#carTable tbody tr').length;
            $('#table-content-placeholder').html(data);
            var newRowCount = $('#carTable tbody tr').length;
            var newRowsCount = newRowCount - oldRowCount;
            if (newRowsCount > 0) {
                $('#notification-badge').text(newRowsCount);
                // Highlight new rows
                var newRows = $('#carTable tbody tr:lt(' + newRowsCount + ')');
                newRows.addClass('highlighted');
                setTimeout(function(){
                    newRows.removeClass('highlighted');
                }, 5000); // Highlight remains for 5 seconds (5000 milliseconds)
            }
        }).fail(function() {
            console.log('Failed to load table content');
        });
    }

    setInterval(reloadTable, 10000);

    // Notification badge click event
    $('#notification-badge').click(function() {
        // Clear notification badge
        $('#notification-badge').text('0');
    });
});
</script>
<style>
    .highlighted {
        background-color: black;
    }

</style>
</body>
</html>
