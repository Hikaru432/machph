<?php
include 'config.php';

// Start the session to retrieve the companyid
session_start();

// Check if companyid exists in the session
if (!isset($_SESSION['companyid'])) {
    die("Error: Missing companyid in session.");
}

$companyid = $_SESSION['companyid']; // Now we safely assign the companyid from session

// Get user and car details from the URL
$user_id = $_GET['user_id'];
$car_id = $_GET['car_id'];

// Fetch user details
$user_query = "SELECT id, firstname, middlename, lastname, homeaddress, email, image FROM user WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Fetch car details
$car_query = "SELECT carmodel, plateno, year, gas FROM car WHERE car_id = '$car_id'";
$car_result = mysqli_query($conn, $car_query);
$car = mysqli_fetch_assoc($car_result);

// Fetch service details
$service_query = "SELECT * FROM service WHERE user_id = '$user_id' AND car_id = '$car_id'";
$service_result = mysqli_query($conn, $service_query);

// Service status and urgency mapping functions
function getStatus($value) {    
    switch ($value) {
        case 1: return 'Normal';
        case 2: return 'Above Normal';
        case 3: return 'Need Repair';
        default: return 'Unknown';
    }
}

function getUrgency($value) {
    switch ($value) {
        case 1: return 'Urgent need';
        case 2: return 'Urgent need';
        case 3: return 'Urgent need';
        default: return 'No urgent need';
    }
}

// Check if the companyid exists in the autoshop table
$check_query = "SELECT * FROM autoshop WHERE companyid = '$companyid'";
$check_result = mysqli_query($conn, $check_query);
if (mysqli_num_rows($check_result) == 0) {
    die("Error: Invalid companyid.");
}

// Check if details have been approved
$check_query = "SELECT * FROM detailsapprove WHERE user_id = '$user_id' AND car_id = '$car_id' AND companyid = '$companyid'";
$check_result = mysqli_query($conn, $check_query);

$verify_status = 'Not Approved'; 

if (isset($_POST['done'])) {
    $verify_status = 'Approved'; 
    // Insert or Update when 'done' is clicked
    if (mysqli_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO detailsapprove (user_id, car_id, companyid, verify) VALUES ('$user_id', '$car_id', '$companyid', '$verify_status')";
        if (mysqli_query($conn, $insert_query)) {
            header("Location: homemanager.php"); // Redirect to homemanager.php
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        $update_query = "UPDATE detailsapprove SET verify = '$verify_status' WHERE user_id = '$user_id' AND car_id = '$car_id' AND companyid = '$companyid'";
        if (mysqli_query($conn, $update_query)) {
            header("Location: homemanager.php"); // Redirect to homemanager.php
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['close'])) {
    // Set the verify column to NULL when Close is clicked
    $reset_query = "UPDATE detailsapprove SET verify = NULL WHERE user_id = '$user_id' AND car_id = '$car_id' AND companyid = '$companyid'";
    if (mysqli_query($conn, $reset_query)) {
        header("Location: homemanager.php"); // Redirect to homemanager.php
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}



if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $homeaddress = $_POST['homeaddress'];
    $email = $_POST['email'];
    $car_id = $_POST['car_id'];

    // Update the user details in the database
    $update_query = "UPDATE user SET firstname = '$firstname', middlename = '$middlename', lastname = '$lastname', homeaddress = '$homeaddress', email = '$email' WHERE id = '$user_id'";

    if (mysqli_query($conn, $update_query)) {
        // After updating user details, update car details (if needed)
        $car_query = "UPDATE car SET car_id = '$car_id' WHERE car_id = '$car_id'"; // Update query for the car table
        if (mysqli_query($conn, $car_query)) {
            // Redirect back to the same page to see the updated details
            header("Location: view_user_car_details.php?user_id=$user_id&car_id=$car_id");
            exit();
        } else {
            echo "Error updating car details: " . mysqli_error($conn);
        }
    } else {
        echo "Error updating user: " . mysqli_error($conn);
    }
}


// Car updates
if (isset($_POST['update_car'])) {
    $car_id = $_POST['car_id'];
    $carmodel = mysqli_real_escape_string($conn, $_POST['carmodel']);
    $plateno = mysqli_real_escape_string($conn, $_POST['plateno']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $gas = mysqli_real_escape_string($conn, $_POST['gas']);

    $update_query = "UPDATE car SET carmodel = '$carmodel', plateno = '$plateno', year = '$year', gas = '$gas' WHERE car_id = '$car_id'";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Car details updated successfully!'); window.location.href='view_user_car_details.php?user_id=$user_id&car_id=$car_id';</script>";
    } else {
        echo "<script>alert('Error updating car details: " . mysqli_error($conn) . "');</script>";
    }
} 

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User and Car Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles for cards */
        .card {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            border: none;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #007bff;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .service-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">SE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemanager.php">Service</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- User detail -->

<div class="container my-5">
    <!-- User Details Section -->
    <div class="card mb-4">
        <div class="card-header">
            User Details
            <!-- Edit Button -->
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Name:</strong> <?php echo "{$user['firstname']} {$user['middlename']} {$user['lastname']}"; ?></p>
                    <p><strong>Address:</strong> <?php echo $user['homeaddress']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                </div>
                <div class="col-md-4">
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>" alt="User Image" class="img-fluid img-thumbnail" width="150">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="view_user_car_details.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <input type="hidden" name="car_id" value="<?php echo isset($car['car_id']) ? $car['car_id'] : $_GET['car_id']; ?>">
                    <div class="mb-3">
                        <label for="editFirstName" class="form-label">First Name</label>
                        <input type="text" name="firstname" id="editFirstName" class="form-control" value="<?php echo $user['firstname']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editMiddleName" class="form-label">Middle Name</label>
                        <input type="text" name="middlename" id="editMiddleName" class="form-control" value="<?php echo $user['middlename']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="editLastName" class="form-label">Last Name</label>
                        <input type="text" name="lastname" id="editLastName" class="form-control" value="<?php echo $user['lastname']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" name="homeaddress" id="editAddress" class="form-control" value="<?php echo $user['homeaddress']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_user" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Car Details Section -->
<div class="container my-5">
    <!-- Car Details Section -->
    <div class="card mb-4">
        <div class="card-header">
            Car Details
            <!-- Edit Button -->
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#editCarModal">Edit</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Car Model:</strong> <?php echo $car['carmodel']; ?></p>
                    <p><strong>Plate No:</strong> <?php echo $car['plateno']; ?></p>
                    <p><strong>Year:</strong> <?php echo $car['year']; ?></p>
                    <p><strong>Gas Type:</strong> <?php echo $car['gas']; ?></p>
                </div>
                <div class="col-md-4">
                    <!-- If you have an image for the car, you can display it here -->
                    <!-- <img src="path_to_car_image.jpg" alt="Car Image" class="img-fluid img-thumbnail" width="150"> -->
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Edit Car Modal -->
<div class="modal fade" id="editCarModal" tabindex="-1" aria-labelledby="editCarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCarModalLabel">Edit Car Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                    <div class="mb-3">
                        <label for="editCarmodel" class="form-label">Car Model</label>
                        <input type="text" name="carmodel" id="editCarmodel" class="form-control" value="<?php echo $car['carmodel']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPlateno" class="form-label">Plate No</label>
                        <input type="text" name="plateno" id="editPlateno" class="form-control" value="<?php echo $car['plateno']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editYear" class="form-label">Year</label>
                        <select name="year" id="editYear" class="form-select" required>
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = date("Y");
                            for ($year = 1990; $year <= $currentYear; $year++) {
                                // Check if the current year matches the database year
                                $selected = ($year == $car['year']) ? "selected" : "";
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editGas" class="form-label">Gas Type</label>
                        <select name="gas" id="editGas" class="form-select" required>
                            <option value="Regular" <?php echo ($car['gas'] == "Regular") ? "selected" : ""; ?>>Regular</option>
                            <option value="Premium" <?php echo ($car['gas'] == "Premium") ? "selected" : ""; ?>>Premium</option>
                            <option value="Diesel" <?php echo ($car['gas'] == "Diesel") ? "selected" : ""; ?>>Diesel</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_car" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Service Details Section -->
    <div class="card mb-4 shadow-lg rounded">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Service Details</h5>
        </div>
        <div class="card-body">
            <?php
            if (mysqli_num_rows($service_result) > 0) {
                while ($service = mysqli_fetch_assoc($service_result)) {
                    echo "<div class='service-details'>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Service No:</strong> <span class='text-muted'>{$service['serviceno']}</span></p>
                                </div>
                            </div>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Engine Oil (eo):</strong> <span class='" . getUrgencyClass($service['eo']) . "'>" . getUrgency($service['eo']) . "</span></p>
                                </div>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Fuel and Air Intake System (elp):</strong> <span class='" . getUrgencyClass($service['elp']) . "'>" . getUrgency($service['elp']) . "</span></p>
                                </div>
                            </div>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Cooling and Lubrication (ep):</strong> <span class='" . getUrgencyClass($service['ep']) . "'>" . getUrgency($service['ep']) . "</span></p>
                                </div>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Battery:</strong> <span class='" . getStatusClass($service['battery']) . "'>" . getStatus($service['battery']) . "</span></p>
                                </div>
                            </div>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Light:</strong> <span class='" . getStatusClass($service['light']) . "'>" . getStatus($service['light']) . "</span></p>
                                </div>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Oil:</strong> <span class='" . getStatusClass($service['oil']) . "'>" . getStatus($service['oil']) . "</span></p>
                                </div>
                            </div>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Water:</strong> <span class='" . getStatusClass($service['water']) . "'>" . getStatus($service['water']) . "</span></p>
                                </div>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Brake:</strong> <span class='" . getStatusClass($service['brake']) . "'>" . getStatus($service['brake']) . "</span></p>
                                </div>
                            </div>
                            <div class='row mb-3'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Air:</strong> <span class='" . getStatusClass($service['air']) . "'>" . getStatus($service['air']) . "</span></p>
                                </div>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Gas:</strong> <span class='" . getStatusClass($service['gas']) . "'>" . getStatus($service['gas']) . "</span></p>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-12 col-md-6'>
                                    <p><strong>Tire:</strong> <span class='" . getStatusClass($service['tire']) . "'>" . getStatus($service['tire']) . "</span></p>
                                </div>
                            </div>
                        </div>";
                }
            } else {
                echo "<p>No services found for this car.</p>";
            }
            ?>
        </div>
    </div>
</div>


<div class="d-flex justify-content-end mt-4">
<div class="d-flex justify-content-end mt-4">
<form action="" method="POST">
    <button type="submit" name="done" class="btn btn-success">Done</button>
    <button type="submit" name="close" class="btn btn-danger">Back</button>
</form>
</div>

</div>



<!-- Helper Functions for Status Classes -->
<?php
function getStatusClass($value) {
    switch ($value) {
        case 1: return 'text-success';  // Normal
        case 2: return 'text-warning';  // Above Normal
        case 3: return 'text-danger';   // Need Repair
        default: return 'text-muted';   // Unknown
    }
}

function getUrgencyClass($value) {
    switch ($value) {
        case 1: 
        case 2: 
        case 3: return 'text-danger'; // Urgent need
        default: return 'text-muted'; // No urgent need
    }
}
?>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
