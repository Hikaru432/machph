<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Fetch metrics for dashboard
$done_repair_count_query = "SELECT COUNT(DISTINCT a.user_id, a.car_id) AS done_count 
                            FROM accomplishtask a
                            INNER JOIN car c ON a.car_id = c.car_id
                            WHERE a.progress_percentage = 100 AND c.companyid = '$companyid'";

$progressing_count_query = "SELECT COUNT(DISTINCT a.user_id, a.car_id) AS progressing_count 
                            FROM accomplishtask a
                            INNER JOIN car c ON a.car_id = c.car_id
                            WHERE a.progress_percentage < 100 AND c.companyid = '$companyid'";

$revisit_count_query = "
    SELECT COUNT(*) AS revisit_count 
    FROM service s
    INNER JOIN car c ON s.car_id = c.car_id
    WHERE c.companyid = '$companyid' AND (
        SELECT COUNT(*) 
        FROM service s2
        WHERE s2.user_id = s.user_id
        AND s2.car_id = s.car_id
    ) > 1
";

$done_repair_count = mysqli_fetch_assoc(mysqli_query($conn, $done_repair_count_query))['done_count'] ?? 0;
$progressing_count = mysqli_fetch_assoc(mysqli_query($conn, $progressing_count_query))['progressing_count'] ?? 0;
$revisit_count = mysqli_fetch_assoc(mysqli_query($conn, $revisit_count_query))['revisit_count'] ?? 0;

$query = "
    SELECT 
        u.name AS user_name,
        c.carmodel,
        c.plateno,
        c.bodyno,
        c.enginecc,
        c.year,
        a.progress_percentage,
        (
            SELECT COUNT(*) - 1
            FROM service s
            WHERE s.user_id = a.user_id
            AND s.car_id = a.car_id
        ) AS revisit_count
    FROM 
        accomplishtask a
    INNER JOIN 
        user u ON a.user_id = u.id
    INNER JOIN 
        car c ON a.car_id = c.car_id
    WHERE 
        a.progress_percentage = 100 
        AND c.companyid = '$companyid'
    GROUP BY 
        a.user_id, a.car_id
    ORDER BY 
        a.progressingpercentage DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">Home</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="admin.php?companyid=<?php echo $companyid; ?>">Home</a>
                </li> -->
            </ul>
        </div>
    </div>
</nav>
    <br>
    <div class="container mt-5">
    <!-- Dashboard Metrics -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-success shadow-lg">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-3">Complete Repairs</h5>
                        <p class="card-text fs-1 mb-0"><?php echo $done_repair_count; ?></p>
                    </div>
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-warning shadow-lg">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-3">Ongoing Repairs</h5>
                        <p class="card-text fs-1 mb-0"><?php echo $progressing_count; ?></p>
                    </div>
                    <i class="fas fa-spinner fa-3x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger shadow-lg">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-3">Revisits</h5>
                        <p class="card-text fs-1 mb-0"><?php echo $revisit_count; ?></p>
                    </div>
                    <i class="fas fa-redo-alt fa-3x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Repair Table -->
    <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover shadow-sm rounded">
        <thead class="table-dark">
            <tr>
                <th>User Name</th>
                <th>Car Model</th>
                <th>Plate Number</th>
                <th>Body Number</th>
                <th>Engine CC</th>
                <th>Year</th>
                <th>Done Repair</th>
                <th>Revisit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['user_name']}</td>
                            <td>{$row['carmodel']}</td>
                            <td>{$row['plateno']}</td>
                            <td>{$row['bodyno']}</td>
                            <td>{$row['enginecc']}</td>
                            <td>{$row['year']}</td>
                            <td>{$row['progress_percentage']}%</td>
                            <td>{$row['revisit_count']}</td>
                          </tr>";
                }
            } else {
                echo '<tr><td colspan="8" class="text-center">No records found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add FontAwesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
