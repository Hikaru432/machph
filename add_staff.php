<?php
session_start();
include 'config.php';

$companyid = $_SESSION['companyid'];
// echo "Company ID: " . $companyid; 

if(!isset($_SESSION['companyid'])){
   header('location:clogin.php');
   exit();
} 

$companyid = $_SESSION['companyid'];

// Function to sanitize input data
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars($data));
}

if(isset($_POST['submit'])){
    // Handle form submission
    $firstname = sanitize($conn, $_POST['firstname']);
    $middlename = sanitize($conn, $_POST['middlename']);
    $lastname = sanitize($conn, $_POST['lastname']);
    $address = sanitize($conn, $_POST['address']);
    $employment = sanitize($conn, $_POST['employment']);
    $jobrole = sanitize($conn, $_POST['jobrole']);

    // Insert mechanic data into the database
    $insert_query = "INSERT INTO mechanic (firstname, middlename, lastname, address, employment, jobrole, companyid) 
                     VALUES ('$firstname', '$middlename', '$lastname', '$address', '$employment', '$jobrole', '$companyid')";
    $insert_result = mysqli_query($conn, $insert_query);
    if($insert_result) {
        echo "<script>alert('Mechanic added successfully');</script>";
    } else {
        echo "<script>alert('Failed to add mechanic');</script>";
    }
}

// Fetch mechanics data for the current company
$mechanics_query = "SELECT * FROM mechanic WHERE companyid = $companyid";
$mechanics_result = mysqli_query($conn, $mechanics_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        /* Adjustments for better layout */
        .form-group {
            margin-bottom: 15px;
        }
        .btn-group {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Gwapo ko -->
<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
         <a class="nav-link active text-white" aria-current="page" href="admin.php?companyid=<?php echo $companyid; ?>">Home</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <!-- <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">User</a>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php">Sales</a> 
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="addproduct.php">Products</a>
                </li>
                <li class="nav-item"> <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php">Customer</a> 
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-white" href="#">Sales</a></li>
                        <li><a class="dropdown-item text-white" href="#">Products</a></li>
                        <li><a class="dropdown-item text-white" href="#">Customer</a></li>
                        <li><a class="dropdown-item text-white" href="#">Sales Report</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white" href="#">Something else here</a></li>
                    </ul>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="logout.php">Logout</a> 
                </li>
            </ul>
        </div> -->
    </div>
</nav>

<div class="container mt-5">
    <h2>Add Staff</h2>
    <form method="post">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="firstname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="middlename" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middlename" name="middlename">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" required>
        </div>
        <div class="form-group">
            <label for="employment" class="form-label">Employment</label>
            <select class="form-select" id="employment" name="employment" required>
                <option value="">Select Employment Type</option>
                <option value="Full-Time">Full-Time</option>
                <option value="Part-Time">Part-Time</option>
                <option value="Temporary">Temporary</option>
            </select>
        </div>

        <div class="form-group">
            <label for="jobrole" class="form-label">Job Role</label>
            <select class="form-select" id="jobrole" name="jobrole" required>
                <option selected disabled value="">Choose...</option>
                <option value="Service Technician">Service Technician</option>
                <option value="Diagnostic Technician">Diagnostic Technician</option>
                <option value="Master Technician">Master Technician</option>
                <option value="Service Advisor">Service Advisor</option>
                <option value="Shop Foreman">Shop Foreman</option>
            </select>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary" name="submit">Add</button>
        </div>
    </form>

    <h2 class="mt-5">Mechanics</h2>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Address</th>
                <th scope="col">Employment</th>
                <th scope="col">Job Role</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($mechanics_result)): ?>
            <tr>
                <td><?php echo $row['firstname']; ?></td>
                <td><?php echo $row['lastname']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['employment']; ?></td>
                <td><?php echo $row['jobrole']; ?></td>
                <td><?php echo $row['active'] ? 'Active' : 'Inactive'; ?></td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">Edit</a>
                    <?php if($row['active']): ?>
                        <a href="#" class="btn btn-sm btn-warning">Deactivate</a>
                    <?php else: ?>
                        <a href="#" class="btn btn-sm btn-success">Activate</a>
                    <?php endif; ?>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <br>
    <br>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>

