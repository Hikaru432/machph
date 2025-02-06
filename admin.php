<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Query to get company data
$query = "SELECT * FROM autoshop WHERE companyid = '$companyid'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $company_data = mysqli_fetch_assoc($result);
    $company_role = $company_data['role'];
} else {
    echo "<script>alert('Company data not found!');</script>";
    exit();
}

// Fetch all mechanics for the company
$mechanic_query = "SELECT * FROM mechanic WHERE companyid = '$companyid'";
$mechanic_result = mysqli_query($conn, $mechanic_query);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            width: 250px; height: 100vh; background-color: #343a40;
            padding-top: 20px; position: fixed;
        }
        .sidebar a {
            color: white; padding: 10px 20px; display: block; text-decoration: none;
        }
        .sidebar a:hover { background-color: #495057; }
        .content { margin-left: 270px; padding: 20px; }
        .mechanic-list-container {
            position: absolute; top: 80px; right: 20px; width: 280px;
            background: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px; padding: 20px; z-index: 10;
        }
        .mechanic-list { max-height: 300px; overflow-y: auto; padding: 0; }
        .mechanic-list .list-group-item {
            text-align: left; padding: 12px 15px; transition: background 0.3s ease;
            border-radius: 5px; cursor: pointer;
        }
        .mechanic-list .list-group-item:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="#">Company</a>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="admin.php">Dashboard</a>
            <a href="addproduct.php?companyid=<?php echo $companyid; ?>&role=<?php echo $company_role; ?>">Product</a>
            <a href="homemanager.php?companyid=<?php echo $companyid; ?>">Service Executive</a>
            <a href="add_staff.php?companyid=<?php echo $companyid; ?>">Staff</a>
            <a href="revisit.php?companyid=<?php echo $companyid; ?>">Monitoring</a>
            <a href="dashscale.php?companyid=<?php echo $companyid; ?>">Survey</a>
            <a href="sales.php?companyid=<?php echo $companyid; ?>">Sales</a>
            <a href="logout.php">Logout</a>
        </div>
            <div class="container mt-4">
                <h1 class="display-6 text-center">
                    Welcome, <?php echo isset($company_data['companyname']) ? $company_data['companyname'] : 'Valued Partner'; ?>!
                </h1>
                <p class="lead text-center">Manage your business effectively with our dashboard.</p>
                <br>
                <br>
                <br>
                <div class="row text-white mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Primary</h5>
                                <a href="#" class="text-white">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Warning</h5>
                                <a href="#" class="text-dark">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Success</h5>
                                <a href="#" class="text-white">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Danger</h5>
                                <a href="#" class="text-white">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">📊 Area Chart</div>
                            <div class="card-body">
                                <canvas id="areaChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">📊 Bar Chart</div>
                            <div class="card-body">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mechanic-list-container">
            <h5>Mechanic List</h5>
            <ul class="list-group mechanic-list">
                <?php
                if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
                    while ($mechanic = mysqli_fetch_assoc($mechanic_result)) {
                        echo '<li class="list-group-item"><a href="homemechanic.php?mechanic_id=' . $mechanic['mechanic_id'] . '">' . $mechanic['firstname'] . ' ' . $mechanic['lastname'] . '</a></li>';
                    }
                } else {
                    echo '<li class="list-group-item">No mechanics found</li>';
                }
                ?>
            </ul>
        </div>

        <script>
            var ctx1 = document.getElementById('areaChart').getContext('2d');
            var areaChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Mar 1', 'Mar 3', 'Mar 5', 'Mar 7', 'Mar 9', 'Mar 11', 'Mar 13'],
                    datasets: [{
                        label: 'Sales',
                        data: [10000, 30000, 25000, 20000, 28000, 32000, 40000],
                        borderColor: 'blue',
                        fill: true,
                        backgroundColor: 'rgba(0, 123, 255, 0.2)'
                    }]
                },
                options: { responsive: true }
            });

            var ctx2 = document.getElementById('barChart').getContext('2d');
            var barChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                    datasets: [{
                        label: 'Revenue',
                        data: [3000, 4000, 5000, 7000, 9000, 15000],
                        backgroundColor: 'blue'
                    }]
                },
                options: { responsive: true }
            });
        </script>
</body>
</html>
