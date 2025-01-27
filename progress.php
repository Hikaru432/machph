<?php
session_start();

// Include the database configuration file
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Get the car_id from the URL
if (!isset($_GET['car_id'])) {
    echo "No car selected.";
    exit();
}

$car_id = intval($_GET['car_id']);

// Perform the query to fetch the progress details for the selected car
$query = "SELECT accomplishtask.nameprogress, accomplishtask.progressing, accomplishtask.progressingpercentage 
          FROM accomplishtask 
          WHERE car_id = $car_id";
$result = mysqli_query($conn, $query);

// Perform the query to fetch image, category, and comment from the pictureprogress table
$queryPicture = "SELECT category, comment, picture, created_at 
                 FROM pictureprogress 
                 WHERE car_id = $car_id";
$pictureResult = mysqli_query($conn, $queryPicture);

// Check if queries were successful
if (!$result) {
    die('Error fetching progress details: ' . mysqli_error($conn));
}
if (!$pictureResult) {
    die('Error fetching picture details: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Progress Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="backgroud-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand" href="vehicleuser.php">Vehicle</a>
        </div>
    </nav>
<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Car Progress Details</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Progress Name</th>
                        <th>Status</th>
                        <th>Progress Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['nameprogress']}</td>";
                            echo "<td>{$row['progressing']}</td>";
                            echo "<td>{$row['progressingpercentage']}%</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No progress details found for this car.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow mt-4">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0">Additional</h3>
        </div>
        <div class="card-body">
            <?php
            if (mysqli_num_rows($pictureResult) > 0) {
                while ($row = mysqli_fetch_assoc($pictureResult)) {
                    echo "<div class='mb-4'>";
                    echo "<h5 class='fw-bold'>Category: {$row['category']}</h5>";
                    echo "<p><strong>Comment:</strong> {$row['comment']}</p>";
                    echo "<div class='text-center'>";
                    echo "<img src='data:image/jpeg;base64," . base64_encode($row['picture']) . "' alt='Progress Image' class='img-fluid rounded' style='max-height: 300px;'>";
                    echo "</div>";
                    echo "<p class='text-muted text-end'>Uploaded on: {$row['created_at']}</p>";
                    echo "</div><hr>";
                }
            } else {
                echo "<p class='text-center'>No additional progress details found for this car.</p>";
            }
            ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="javascript:history.back();" class="btn btn-secondary">&larr; Back</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
