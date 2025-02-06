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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MachPH Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .btn-custom {
            background-color: rgba(139, 0, 42, 0.82);
            color: #fff;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: rgba(139, 0, 42, 0.9);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table th {
            background-color: #8b002a !important;
            color: white;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">MachPH</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>  
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Welcome to <?php echo isset($company_info['companyname']) ? $company_info['companyname'] : ''; ?></h1>
        </div>
        
        <div class="d-flex justify-content-end mb-4">
            <a href="shop.php?companyid=<?php echo $company_info['companyid']; ?>" class="btn btn-lg btn-custom">Visit Shop</a>
        </div>

        <div class="card shadow-sm p-4">
            <h2 class="fw-bold mb-3">Your Vehicles</h2>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Manufacturer</th>
                            <th>Model</th>
                            <th>Plate No</th>
                            <th>Color</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($car_select)) : ?>
                        <tr>
                            <td><?php echo $row['manuname'] ?? ''; ?></td>
                            <td><?php echo $row['carmodel'] ?? ''; ?></td>
                            <td><?php echo $row['plateno'] ?? ''; ?></td>
                            <td><?php echo $row['color'] ?? ''; ?></td>
                            <td>
                                <a href="carprofile.php?car_id=<?php echo $row['car_id']; ?>&companyname=<?php echo $company_info['companyname']; ?>" class="btn btn-sm btn-custom">View Profile</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
