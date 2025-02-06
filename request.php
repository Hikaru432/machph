<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    header('location:clogin.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Get total counts
$totalQuery = "SELECT 
    SUM(request_type = 'Request') AS total_requests, 
    SUM(request_type = 'Unavailable') AS total_unavailable 
    FROM requestproduct WHERE companyid = '$companyid'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalData = mysqli_fetch_assoc($totalResult);

// Get weekly count
$weeklyQuery = "SELECT COUNT(DISTINCT user_id) AS weekly_count 
                FROM requestproduct 
                WHERE companyid = '$companyid' 
                AND WEEK(timestamp) = WEEK(NOW())";
$weeklyResult = mysqli_query($conn, $weeklyQuery);
$weeklyData = mysqli_fetch_assoc($weeklyResult);

// Get monthly count
$monthlyQuery = "SELECT COUNT(DISTINCT user_id) AS monthly_count 
                 FROM requestproduct 
                 WHERE companyid = '$companyid' 
                 AND MONTH(timestamp) = MONTH(NOW())";
$monthlyResult = mysqli_query($conn, $monthlyQuery);
$monthlyData = mysqli_fetch_assoc($monthlyResult);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }
        .dashboard-container {
            padding: 50px 30px;
        }
        .dashboard-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            text-align: center;
            font-weight: bold;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
        }
        .requests-card { background: linear-gradient(135deg, #007bff, #0056b3); }
        .unavailable-card { background: linear-gradient(135deg, #dc3545, #a71d2a); }
        .weekly-card { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .monthly-card { background: linear-gradient(135deg, #ff9f00, #d68100); }
        .table-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .table thead { background: #343a40; color: white; }
        .table th, .table td { vertical-align: middle !important; }
        .img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .text-primary { color: #007bff !important; }
        .text-danger { color: #dc3545 !important; }
        .text-success { color: #28a745 !important; }
        .text-warning { color: #ff9f00 !important; }
        .card-title {
            font-size: 18px;
            font-weight: 600;
        }
        .card-text {
            font-size: 24px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
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

<div class="container dashboard-container">
 <br>
 <br>

    <div class="row text-center">
        <div class="col-md-3 mb-4">
            <div class="dashboard-card requests-card">
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <h5 class="card-title">Total Requests</h5>
                <p class="card-text"><?php echo $totalData['total_requests'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="dashboard-card unavailable-card">
                <i class="fas fa-ban fa-3x mb-3"></i>
                <h5 class="card-title">Total Unavailable</h5>
                <p class="card-text"><?php echo $totalData['total_unavailable'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="dashboard-card weekly-card">
                <i class="fas fa-calendar-week fa-3x mb-3"></i>
                <h5 class="card-title">Users This Week</h5>
                <p class="card-text"><?php echo $weeklyData['weekly_count'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="dashboard-card monthly-card">
                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                <h5 class="card-title">Users This Month</h5>
                <p class="card-text"><?php echo $monthlyData['monthly_count'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>


    <div class="table-container my-4">
        <h4 class="text-center fw-bold text-primary mb-4">All Requests & Unavailable</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Comments</th>
                    <th>Request Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $allQuery = "SELECT user_id, name, image, comments, request_type, timestamp FROM requestproduct WHERE companyid = '$companyid'";
                $allResult = mysqli_query($conn, $allQuery);
                while ($row = mysqli_fetch_assoc($allResult)) { ?>
                    <tr>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td>
                            <?php if (!empty($row['image'])) { ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" class="img-thumbnail">
                            <?php } else { ?>
                                <span class="text-muted">No Image</span>
                            <?php } ?>
                        </td>
                        <td><?php echo $row['comments']; ?></td>
                        <td class="<?php echo $row['request_type'] == 'Request' ? 'text-primary' : 'text-danger'; ?>">
                            <?php echo $row['request_type']; ?>
                        </td>
                        <td><?php echo date('F d, Y', strtotime($row['timestamp'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> MachPH. All rights reserved.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
