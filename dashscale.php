<?php
session_start();
include 'config.php';

// Check if the user is logged in and the company ID is set
if (!isset($_SESSION['companyid']) || !isset($_GET['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_GET['companyid'];  // Get companyid from URL

// Fetch the company name
$query = "SELECT companyname FROM autoshop WHERE companyid = '$companyid'";
$result = mysqli_query($conn, $query);
$company_data = mysqli_fetch_assoc($result);
$company_name = $company_data['companyname'];

// Fetch all scale data for the company
$categories = ['Tangibles', 'Reliability', 'Responsiveness', 'Assurance', 'Empathy'];
$category_results = [];

foreach ($categories as $category) {
    // Query to fetch ratings for each category
    $category_query = "SELECT AVG(rating) AS avg_rating FROM scale 
                       WHERE autoshop_id = '$companyid' AND category = '$category'";
    $category_result = mysqli_query($conn, $category_query);
    $category_data = mysqli_fetch_assoc($category_result);
    $category_results[$category] = $category_data['avg_rating'] ?: 0;
}

// Calculate the overall satisfaction based on the average of all categories
$overall_avg_rating = array_sum($category_results) / count($category_results);

// Satisfaction level based on overall rating
function getSatisfaction($avg_rating) {
    if ($avg_rating >= 4.5) {
        return 'Extremely Not Satisfied';
    } elseif ($avg_rating >= 4) {
        return 'Not Satisfied';
    } elseif ($avg_rating >= 3) {
        return 'Satisfied';
    } elseif ($avg_rating >= 2) {
        return 'Very Satisfied';
    } else {
        return 'Extremely Satisfied';
    }
}

// Check the last satisfaction record for the company
function getLastSatisfaction($companyid, $conn) {
    $query = "SELECT satisfaction_level FROM trackingrecord 
              WHERE companyid = '$companyid' ORDER BY created_at DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['satisfaction_level'];
    }
    return null;  // No previous record found
}

// Record Overall Satisfaction to the Tracking Table only if it has changed
function recordSatisfaction($companyid, $satisfaction_level, $conn) {
    // Check if the current satisfaction level is different from the last one
    $last_satisfaction = getLastSatisfaction($companyid, $conn);
    if ($last_satisfaction !== $satisfaction_level) {
        $date = date('Y-m-d');
        $week = date('W');  // Get current week number
        $month = date('n'); // Get current month number

        // Insert into trackingrecord table
        $query = "INSERT INTO trackingrecord (companyid, satisfaction_level, record_date, record_week, record_month)
                  VALUES ('$companyid', '$satisfaction_level', '$date', '$week', '$month')";
        mysqli_query($conn, $query);
    }
}

// Record the overall satisfaction based on the calculated average
$satisfaction_level = getSatisfaction($overall_avg_rating);
recordSatisfaction($companyid, $satisfaction_level, $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Survey Results Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">Admin</a>
    </div>
</nav>


<div class="container py-5">
    <!-- Dashboard Header -->
    <div class="text-center mb-4">
        <h1 class="fw-bold">Survey Results Dashboard</h1>
        <h3 class="text-primary">Company: <?php echo $company_name; ?></h3>
        <p class="text-muted">Track and improve your customer satisfaction based on survey ratings.</p>
    </div>

    <!-- Category Cards -->
    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white bg-primary text-center">
                        <h5 class="mb-0"><?php echo $category; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h4 class="text-success">Average Rating</h4>
                            <p class="fs-3 fw-bold mb-1">
                                <?php echo number_format($category_results[$category], 2); ?>
                            </p>
                            <p class="satisfaction fs-5 mb-0">Satisfaction: 
                                <span class="fw-bold text-<?php echo getSatisfaction($category_results[$category]) == 'Extremely Satisfied' ? 'success' : (getSatisfaction($category_results[$category]) == 'Very Satisfied' ? 'info' : (getSatisfaction($category_results[$category]) == 'Satisfied' ? 'warning' : 'danger')) ?>">
                                    <?php echo getSatisfaction($category_results[$category]); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Overall Satisfaction -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card shadow-lg">
                    <div class="card-header text-white bg-success text-center">
                        <h4 class="mb-0">Overall Satisfaction</h4>
                    </div>
                    <div class="card-body text-center">
                        <h4 class="text-primary">Average Rating</h4>
                        <p class="fs-2 fw-bold mb-1">
                            <?php echo number_format($overall_avg_rating, 2); ?>
                        </p>
                        <p class="satisfaction-status fs-4 mb-0">
                            Overall Satisfaction: 
                            <span class="fw-bold 
                                <?php 
                                    // Map satisfaction levels to appropriate colors
                                    switch($satisfaction_level) {
                                        case 'Extremely Satisfied':
                                            echo 'text-success'; // Green
                                            break;
                                        case 'Very Satisfied':
                                            echo 'text-info'; // Light Blue
                                            break;
                                        case 'Satisfied':
                                            echo 'text-warning'; // Yellow
                                            break;
                                        case 'Not Satisfied':
                                            echo 'text-danger'; // Red
                                            break;
                                        case 'Extremely Not Satisfied':
                                            echo 'text-dark'; // Dark Gray
                                            break;
                                    }
                                ?>">
                                <?php echo $satisfaction_level; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    <!-- Final Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info text-center py-4">
                <h4 class="fw-bold">Final Summary</h4>
                <p class="mb-0">Use the insights from the survey ratings to improve customer satisfaction and enhance your service quality in targeted areas.</p>
            </div>
        </div>
    </div>

   <!-- Satisfaction Tracking Table (Based on Overall Satisfaction) -->
        <div class="container py-5">
            <h2 class="text-center mb-4 text-dark">Satisfaction Tracking Records</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover shadow-lg rounded">
                    <thead class="thead-light bg-primary text-white">
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Week</th>
                            <th class="text-center">Month</th>
                            <th class="text-center">Satisfaction Level</th>
                            <th class="text-center">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch tracked satisfaction data (only based on overall satisfaction)
                        $tracking_query = "SELECT * FROM trackingrecord WHERE companyid = '$companyid' ORDER BY created_at DESC";
                        $tracking_result = mysqli_query($conn, $tracking_query);

                        while ($tracking_row = mysqli_fetch_assoc($tracking_result)):
                        ?>
                            <tr class="text-center">
                                <td><?php echo $tracking_row['record_date']; ?></td>
                                <td><?php echo $tracking_row['record_week']; ?></td>
                                <td><?php echo $tracking_row['record_month']; ?></td>
                                <td class="satisfaction-level">
                                    <?php 
                                        // Apply color to satisfaction level
                                        $satisfaction_level = $tracking_row['satisfaction_level'];
                                        $color_class = '';
                                        switch ($satisfaction_level) {
                                            case 'Extremely Satisfied':
                                                $color_class = 'text-success';
                                                break;
                                            case 'Very Satisfied':
                                                $color_class = 'text-info';
                                                break;
                                            case 'Satisfied':
                                                $color_class = 'text-warning';
                                                break;
                                            case 'Not Satisfied':
                                                $color_class = 'text-danger';
                                                break;
                                            case 'Extremely Not Satisfied':
                                                $color_class = 'text-dark';
                                                break;
                                        }
                                    ?>
                                    <span class="<?php echo $color_class; ?>"><?php echo $satisfaction_level; ?></span>
                                </td>
                                <td><?php echo $tracking_row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
