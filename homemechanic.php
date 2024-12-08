<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['companyid']) || empty($_SESSION['companyid'])) {
    header('Location: homemechanic.php'); // Redirect to login page if not logged in
    exit();
}

// Get mechanic_id from URL
if (!isset($_GET['mechanic_id']) || empty($_GET['mechanic_id'])) {
    echo "<script>alert('Back to Dashboard!'); window.location.href='homemechanic.php';</script>";
    exit();
}

$mechanic_id = intval($_GET['mechanic_id']); 
$_SESSION['mechanic_id'] = $mechanic_id;

// Retrieve mechanic details from the database based on the mechanic_id
$query_mechanic = "SELECT * FROM mechanic WHERE mechanic_id = $mechanic_id";
$result_mechanic = mysqli_query($conn, $query_mechanic);
$mechanic = mysqli_fetch_assoc($result_mechanic);

if (!$mechanic) {
    echo "<script>alert('Mechanic not found!'); window.location.href='admin.php';</script>";
    exit();
}

// Retrieve autoshop details from the database based on the companyid
$companyid = $_SESSION['companyid'];
$query_company = "SELECT * FROM autoshop WHERE companyid = $companyid";
$result_company = mysqli_query($conn, $query_company);
$autoshop = mysqli_fetch_assoc($result_company);


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
        <a class="navbar-brand text-white" href="admin.php">Mechanic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="admin.php?companyid=<?php echo $companyid; ?>">Home</a>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php?mechanic_id=<?php echo $mechanic_id; ?>">Job</a> 
                </li>

                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger">0</span></a>
                </li>
               
            </ul>
        </div>
    </div>
</nav>

<h1 class="welcome-message">Welcome, <span class="mechanic-name"><?php echo isset($mechanic['firstname']) ? htmlspecialchars($mechanic['firstname']) : 'Guest'; ?></span>!</h1>
<br>

<div id="table-content-placeholder">
    <!-- Table content will be loaded here -->
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
    function loadTableContent() {
        $.get('table_content.php', { mechanic_id: <?php echo $mechanic_id; ?> }, function(data) {
            $('#table-content-placeholder').html(data);
        }).fail(function() {
            console.log('Failed to load table content');
        });
    }

    loadTableContent();

    function reloadTable() {
        $.get('table_content.php', { mechanic_id: <?php echo $mechanic_id; ?> }, function(data) {
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

    .welcome-message {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff; /* Bootstrap primary color */
        text-align: center;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .mechanic-name {
        color: black; /* Contrast color for the name */
    }

    .greeting-message {
        font-size: 1.2rem;
        color: #666; /* A softer color for the greeting */
        text-align: center;
        margin-bottom: 20px;
    }
</style>

</body>
</html>
