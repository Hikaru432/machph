<?php
include 'config.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

// Handle approval
if (isset($_POST['approve'])) {
    $companyId = $_POST['companyid'];
    $updateStatus = mysqli_query($conn, "UPDATE autoshop SET status='Approved' WHERE companyid='$companyId'");
}

if (isset($_POST['disapprove'])) {
    $companyId = $_POST['companyid'];
    $updateStatus = mysqli_query($conn, "UPDATE autoshop SET status='Not Approved' WHERE companyid='$companyId'");
}

// Fetch all autoshops
$autoshops = mysqli_query($conn, "SELECT * FROM autoshop");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MachPH Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            cursor: pointer;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }


    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center">MachPH Admin</h4>
        <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <!-- <a href="machphadmin.php" class="active"><i class="bi bi-building"></i> Manage Auto Shops</a>
        <a href="revisit.php"><i class="bi bi-file-earmark-check"></i> Monitoring</a> -->
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Header -->
        <div class="header d-flex justify-content-between align-items-center">
            <h2>Manage Auto Shops</h2>
            <button class="btn btn-primary"><i class="bi bi-bell"></i> Notifications</button>
        </div>

        <!-- Main Content -->
        <div class="container my-4">
        <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Company ID</th>
                            <th>Company Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($autoshops)) { ?>
                            <tr data-bs-toggle="modal" data-bs-target="#detailsModal<?= $row['companyid'] ?>">
                                <td><?= $row['companyid'] ?></td>
                                <td><?= $row['companyname'] ?></td>
                                <td><?= $row['companyemail'] ?></td>
                                <td><?= $row['companyphonenumber'] ?></td>
                                <td><?= $row['city'] ?></td>
                                <td><?= $row['country'] ?></td>
                                <td>
                                    <?php if ($row['status'] == 'Approved') { ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger">Not Approved</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="companyid" value="<?= $row['companyid'] ?>">
                                        <?php if ($row['status'] != 'Approved') { ?>
                                            <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                Approve <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php } ?>
                                        <?php if ($row['status'] == 'Approved') { ?>
                                            <button type="submit" name="disapprove" class="btn btn-danger btn-sm">
                                                Disapprove <i class="bi bi-x-lg"></i>
                                            </button>
                                        <?php } ?>
                                    </form>
                                </td>
                            </tr>

                            <!-- Upgraded Modal -->
                            <div class="modal fade" id="detailsModal<?= $row['companyid'] ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?= $row['companyid'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <!-- Modal Header -->
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="detailsModalLabel<?= $row['companyid'] ?>">Details for <?= $row['companyname'] ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <!-- Modal Body -->
                                        <div class="modal-body">
                                            <div class="row g-4">
                                                <!-- Left Column: Company Image -->
                                                <div class="col-md-4 text-center">
                                                    <div class="card shadow-sm">
                                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['companyimage']) ?>" 
                                                            class="card-img-top rounded" 
                                                            alt="Company Image" 
                                                            style="max-height: 250px; object-fit: cover;">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?= $row['companyname'] ?></h6>
                                                            <p class="card-text text-muted">Company ID: <?= $row['companyid'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Right Column: Company Details -->
                                                <div class="col-md-8">
                                                    <h6 class="fw-bold text-primary">Company Information</h6>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <ul class="list-group">
                                                                <li class="list-group-item"><strong>Email:</strong> <?= $row['companyemail'] ?></li>
                                                                <li class="list-group-item"><strong>Phone Number:</strong> <?= $row['companyphonenumber'] ?></li>
                                                                <li class="list-group-item"><strong>City:</strong> <?= $row['city'] ?></li>
                                                                <li class="list-group-item"><strong>Country:</strong> <?= $row['country'] ?></li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <ul class="list-group">
                                                                <li class="list-group-item"><strong>Street Address:</strong> <?= $row['streetaddress'] ?></li>
                                                                <li class="list-group-item"><strong>Region:</strong> <?= $row['region'] ?></li>
                                                                <li class="list-group-item"><strong>Zip Code:</strong> <?= $row['zipcode'] ?></li>
                                                                <li class="list-group-item"><strong>Status:</strong> 
                                                                    <span class="badge <?= $row['status'] == 'Approved' ? 'bg-success' : 'bg-danger' ?>">
                                                                        <?= $row['status'] ?>
                                                                    </span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal Footer -->
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
