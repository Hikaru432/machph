<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Validate and sanitize user inputs for user_id and car_id
$user_id = isset($_GET['user_id']) ? filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT) : null;
$car_id = isset($_GET['car_id']) ? filter_var($_GET['car_id'], FILTER_SANITIZE_NUMBER_INT) : null;

if (!$user_id || !$car_id) {
    die('Invalid user ID or car ID.');
}

$repair_data = array();  // Associative array to store diagnoses for different problems

// Function to fetch and store data for a specific problem
function fetchDataForProblem($conn, $user_id, $car_id, $problem)
{
    $query = "SELECT diagnosis FROM repair WHERE user_id = ? AND plateno = ? AND problem = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $problem);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['diagnosis'];
    }

    mysqli_stmt_close($stmt);
    return $data;
}

// Function to fetch approval reason from approvals table
function getApprovalReason($conn, $user_id, $car_id) {
    $approval_query = "SELECT reason FROM approvals WHERE user_id = ? AND car_id = ?";
    $stmt = mysqli_prepare($conn, $approval_query);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $approval_info = mysqli_fetch_assoc($result);
        return $approval_info['reason'];
    } else {
        return 'Error fetching reason: ' . mysqli_error($conn);
    }
}

// For Major
$engine_overhaul_data = fetchDataForProblem($conn, $user_id, $car_id, 'Engine Overhaul');
$engine_low_power_data = fetchDataForProblem($conn, $user_id, $car_id, 'Engine Low Power');
$electrical_problem_data = fetchDataForProblem($conn, $user_id, $car_id, 'Electrical Problem');

// For Maintenance
$battery_data = fetchDataForProblem($conn, $user_id, $car_id, 'Battery');
$light_data = fetchDataForProblem($conn, $user_id, $car_id, 'Light');
$oil_data = fetchDataForProblem($conn, $user_id, $car_id, 'Oil');
$water_data = fetchDataForProblem($conn, $user_id, $car_id, 'Water');
$brake_data = fetchDataForProblem($conn, $user_id, $car_id, 'Brake');
$air_data = fetchDataForProblem($conn, $user_id, $car_id, 'Air');
$gas_data = fetchDataForProblem($conn, $user_id, $car_id, 'Gas');
$tire_data = fetchDataForProblem($conn, $user_id, $car_id, 'Tire');

// Fetch user and car information
$user_car_query = "SELECT user.name, user.image, car.carmodel, car.plateno FROM user
                    JOIN car ON user.id = car.user_id
                    WHERE user.id = ? AND car.car_id = ?";
$stmt = mysqli_prepare($conn, $user_car_query);
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $car_id);
mysqli_stmt_execute($stmt);
$user_car_result = mysqli_stmt_get_result($stmt);

if ($user_car_result && mysqli_num_rows($user_car_result) > 0) {
    $user_car_info = mysqli_fetch_assoc($user_car_result);
    $carmodel = $user_car_info['carmodel'];
    $plateno = $user_car_info['plateno'];
} else {
    die('Error fetching user and car information: ' . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation</title>
    <link rel="stylesheet" href="css/machvalidate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="homemechanic.php">Home</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Dropdown
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
            </li>
            <li class="nav-item">
            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
            </li>
        </ul>
        <form class="d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        </div>
    </div>
</nav>

<div class="container mx-auto mt-5">
    <!-- User and Car Information -->

    <ul class="flex justify-normal items-center " id="container">
        <li>
          <?php
            if($user_car_info['image'] == ''){
                  echo '<img src="images/default-avatar.png" class="w-20 h-20 rounded-full">';
                }else{
                    echo '<img src="uploaded_img/' . $user_car_info['image'] . '" class="w-20 h-20 rounded-full">';
                }
            ?>
        </li>
        <li class="px-4"><p class="mb-2 font-medium">User Name: <?php echo '<span class="font-normal">'.$user_car_info['name'] . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium">Car Model: <?php echo '<span class="font-normal">'. $carmodel . '</span>'; ?></p></li>
        <li class="px-4"><p class="mb-2 font-medium">Plate #:  <?php echo '<span class="font-normal">'. $plateno . '</span>'; ?></p></li>
    </ul>

    <div class="for-major-container bg-gray-100 p-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Major</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php if (!empty($engine_overhaul_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Engine Overhaul:</strong><br>
                <?php echo implode(', ', $engine_overhaul_data); ?>
            </div>
        <?php } ?>

        <?php if (!empty($engine_low_power_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Engine Low Power:</strong><br>
                <?php echo implode(', ', $engine_low_power_data); ?>
            </div>
        <?php } ?>

        <?php if (!empty($electrical_problem_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Electrical Problems:</strong><br>
                <?php echo implode(', ', $electrical_problem_data); ?>
            </div>
        <?php } ?>
    </div>
</div>

    <!-- Maintenance part -->
    <div class="for-maintenance-container bg-white p-4 mt-4 rounded-md shadow-md">
    <h2 class="text-2xl font-bold mb-4">Maintenance</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php if (!empty($battery_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Battery:</strong><br>
                <?php echo implode(', ', $battery_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($light_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Light:</strong><br>
                <?php echo implode(', ', $light_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($oil_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Oil:</strong><br>
                <?php echo implode(', ', $oil_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($water_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Water:</strong><br>
                <?php echo implode(', ', $water_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($brake_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Brake:</strong><br>
                <?php echo implode(', ', $brake_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($air_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Air:</strong><br>
                <?php echo implode(', ', $air_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($gas_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Gas:</strong><br>
                <?php echo implode(', ', $gas_data); ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($tire_data)) { ?>
            <div class="px-4 py-2 border border-black">
                <strong>Tire:</strong><br>
                <?php echo implode(', ', $tire_data); ?>
            </div>
        <?php } ?>
    </div>
</div>

    <!-- Approval Reason -->
<!-- Approval Reason -->
<div class="bg-white p-4 rounded-md shadow-md">
    <p><span class="font-bold">Reason:</span> <?php echo getApprovalReason($conn, $user_id, $car_id); ?></p>
</div>

<button id="validBtn" class="mt-4 bg-green-500 text-white p-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring focus:border-green-300">
    Valid
</button>
<button id="invalidBtn" class="mt-4 bg-red-500 text-white p-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring focus:border-red-300">
    Invalid
</button>

<!-- Modal -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="validationModalLabel">Validation Status</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <p id="validationMessage"></p>
            <form id="validationForm">
               <div class="mb-3">
                  <label for="comment" class="form-label">Comment</label>
                  <textarea class="form-control" id="comment" rows="3"></textarea>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button id="saveChangesBtn" type="button" class="btn btn-primary">Save changes</button>
         </div>
      </div>
   </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
   function showModal(message) {
      // Set the validation message in the modal body
      document.getElementById('validationMessage').innerText = message;
      // Open the modal
      var myModal = new bootstrap.Modal(document.getElementById('validationModal'));
      myModal.show();
   }

   document.getElementById('validBtn').addEventListener('click', function () {
    updateValidation('valid');
});

document.getElementById('invalidBtn').addEventListener('click', function () {
    // Show the modal
    showModal('Please enter your comment for invalidation.');
    // Focus on the comment textarea
    document.getElementById('comment').focus();
});

document.getElementById('saveChangesBtn').addEventListener('click', function () {
    const comment = document.getElementById('comment').value;
    if (comment !== '') {
        updateValidation('invalid', comment);
    } else {
        // If comment is empty, show an alert
        showAlert('Please enter a comment for invalidation.');
    }
});

function updateValidation(status, comment = null) {
    const user_id = <?php echo $user_id; ?>;
    const car_id = <?php echo $car_id; ?>;

    // Use AJAX to update validation status
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_validation.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Close the modal
            var myModal = new bootstrap.Modal(document.getElementById('validationModal'));
            myModal.hide();
            // Display a success message if status is valid
            if (status === 'valid') {
                showAlert('Validation status updated successfully.');
            } else {
                // Display a success message for invalid status
                showAlert('Validation status updated successfully.');
                // Redirect to homemanager.php after a short delay for invalid status
                setTimeout(function () {
                    window.location.href = 'homemanager.php';
                }, 1000); // 1000 milliseconds = 1 second
            }
        }
    };

    const data = 'user_id=' + user_id + '&car_id=' + car_id + '&status=' + status + '&comment=' + comment;
    xhr.send(data);
}

function showAlert(message) {
    alert(message);
}

function showModal(message) {
    document.getElementById('validationMessage').innerText = message;
    var myModal = new bootstrap.Modal(document.getElementById('validationModal'));
    myModal.show();
}
</script>

</body>
</html>
