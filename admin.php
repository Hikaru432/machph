<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Query to get company data
$query = "SELECT * FROM autoshop WHERE companyid = $companyid";
$result = mysqli_query($conn, $query);

// If company data found, fetch it
if ($result && mysqli_num_rows($result) > 0) {
    $company_data = mysqli_fetch_assoc($result);
} else {
    // Handle error if company data is not found
    echo "<script>alert('Company data not found!');</script>";
}

// Fetch all mechanics for the company
$mechanic_query = "SELECT * FROM mechanic WHERE companyid = '$companyid'";
$mechanic_result = mysqli_query($conn, $mechanic_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <style>
    .login-form-container {
        position: absolute;
        top: 10px;
        right: 10px;
        max-width: 300px;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="#">Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">User</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="addproduct.php">Products</a>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="logout.php">Logout</a> 
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Display mechanic list -->
<div class="login-form-container" style="margin-top: 80px;">
    <h3>Select Mechanic</h3>
    <ul class="list-group">
        <?php
        if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
            while ($mechanic = mysqli_fetch_assoc($mechanic_result)) {
                echo '<li class="list-group-item"><a href="homemechanic.php?mechanic_id=' . $mechanic['mechanic_id'] . '">' . $mechanic['firstname'] . ' ' . $mechanic['lastname'] . '</a></li>';
            }
        } else {
            echo '<li class="list-group-item">No mechanics found.</li>';
        }
        ?>
    </ul>
</div>

<div class="container-fluid welcome-container d-flex align-items-center justify-content-start vh-100 bg-light" style="margin-top: -120px;">
    <div class=" p-5 text-center">
        <h1 class="display-4 font-weight-bold mb-3">
            Welcome, <?php echo isset($company_data['companyname']) ? $company_data['companyname'] : 'Valued Partner'; ?>!
        </h1>
       
<br>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="homemanager.php?companyid=<?php echo $companyid; ?>" class="btn btn-primary btn-lg px-5 py-3">Service Executive</a>
            <a href="add_staff.php?companyid=<?php echo $companyid; ?>" class="btn btn-secondary btn-lg px-5 py-3">Add Staff</a>
            <a href="addproduct.php?companyid=<?php echo $companyid; ?>" class="btn btn-success btn-lg px-5 py-3">Add Product</a>
        </div>
    </div>
</div>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>
