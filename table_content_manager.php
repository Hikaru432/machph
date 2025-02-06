<?php
include 'config.php';
session_start();

// Fetch companyid from session
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : null;

// Check if companyid is set
if (!$companyid) {
    die('Company ID is missing.');
}

// Fetch data from the user, car, and approvals tables only if companyid is set
$query = "SELECT user.id as user_id, user.name, car.carmodel, car.plateno, car.car_id, car.color, manufacturer.name AS manuname, approvals.status, approvals.reason, autoshop.companyname
          FROM user
          JOIN car ON user.id = car.user_id
          LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
          LEFT JOIN approvals ON user.id = approvals.user_id AND car.car_id = approvals.car_id
          JOIN autoshop ON autoshop.companyid = car.companyid
          WHERE EXISTS (
              SELECT 1 FROM service
              WHERE service.user_id = user.id AND service.companyid = '$companyid'
          )";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Error fetching data: ' . mysqli_error($conn));
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Service Executive</h2>
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="name, manufacturer, or car model">
            </div>
        </div>
    </div>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Manufacturer</th>
                <th>Car Model</th>
                <th>Plate No.</th>
                <th>Color</th>
                <th>Mechanic Approval</th>
                <th>Assign Mechanic</th>
                <th>Survey</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($companyid) {
                $query = "SELECT user.id as user_id, user.name, car.carmodel, car.plateno, car.car_id, car.color, 
                                manufacturer.name AS manuname, approvals.status, approvals.reason, autoshop.companyname
                        FROM user
                        JOIN car ON user.id = car.user_id
                        LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
                        LEFT JOIN approvals ON user.id = approvals.user_id AND car.car_id = approvals.car_id
                        JOIN autoshop ON autoshop.companyid = car.companyid
                        WHERE car.companyid = '$companyid'";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td>
                                <a href="view_user_car_details.php?user_id=<?php echo $row['user_id']; ?>&car_id=<?php echo $row['car_id']; ?>" 
                                class="text-decoration-none fw-bold text-primary">
                                    <?php echo $row['name']; ?>
                                </a>
                            </td>
                            <td><?php echo $row['manuname']; ?></td>
                            <td><?php echo $row['carmodel']; ?></td>
                            <td><?php echo $row['plateno']; ?></td>
                            <td><?php echo $row['color']; ?></td>
                            <td>
                                <?php
                                if ($row['status'] === '1') {
                                    echo '<span class="badge bg-success">Approved</span>';
                                } elseif ($row['status'] === '0') {
                                    echo '<span class="badge bg-danger">Not Approved</span>';
                                    if (!empty($row['reason'])) {
                                        echo '<small class="text-muted"> - ' . $row['reason'] . '</small>';
                                    }
                                } else {
                                    echo '<span class="badge bg-secondary">Not Set</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $detailsapprove_query = "SELECT verify FROM detailsapprove WHERE user_id = '{$row['user_id']}' 
                                                        AND car_id = '{$row['car_id']}' AND companyid = '$companyid'";
                                $detailsapprove_result = mysqli_query($conn, $detailsapprove_query);
                                
                                $verify_status = 'Need verify';

                                if ($detailsapprove_result && mysqli_num_rows($detailsapprove_result) > 0) {
                                    $detailsapprove_row = mysqli_fetch_assoc($detailsapprove_result);
                                    if ($detailsapprove_row['verify'] === 'Approved') {
                                        $verify_status = 'Approved';
                                    }
                                }

                                $assignment_query = "SELECT mechanic.jobrole, mechanic.firstname, mechanic.lastname 
                                                    FROM assignments
                                                    JOIN mechanic ON assignments.mechanic_id = mechanic.mechanic_id
                                                    WHERE assignments.user_id = '{$row['user_id']}' 
                                                    AND assignments.car_id = '{$row['car_id']}'
                                                    AND mechanic.companyid = '$companyid'";
                                $assignment_result = mysqli_query($conn, $assignment_query);

                                if ($verify_status === 'Approved' && $assignment_result && mysqli_num_rows($assignment_result) > 0) {
                                    $assigned_mechanic = mysqli_fetch_assoc($assignment_result);
                                    echo "{$assigned_mechanic['jobrole']} - {$assigned_mechanic['firstname']} {$assigned_mechanic['lastname']}";
                                } elseif ($verify_status === 'Approved') { ?>
                                    <select name="mechanic_id" class="mechanic-select">
                                        <option value="">Select mechanic</option>
                                        <?php
                                        $mechanic_query = "SELECT mechanic_id, CONCAT(firstname, ' ', lastname) AS name, jobrole
                                                            FROM mechanic 
                                                            WHERE companyid = '$companyid'";
                                        $mechanic_result = mysqli_query($conn, $mechanic_query);
                                        if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
                                            while ($mechanic_row = mysqli_fetch_assoc($mechanic_result)) {
                                                echo "<option value=\"{$mechanic_row['mechanic_id']}\">{$mechanic_row['jobrole']} - {$mechanic_row['name']}</option>";
                                            }
                                        } else {
                                            echo "<option value=\"\">No mechanics available</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="btn-assign-mechanic" 
                                            data-user-id="<?php echo $row['user_id']; ?>" 
                                            data-car-id="<?php echo $row['car_id']; ?>">Assign</button>
                                <?php } else {
                                    echo '<span>Need verify</span>';
                                }
                                ?>
                            </td>

                            <!-- Survey Column -->
                            <td>
                                <?php
                                $survey_query = "SELECT surveystatus FROM scale 
                                                WHERE user_id = '{$row['user_id']}' AND car_id = '{$row['car_id']}' 
                                                LIMIT 1";
                                $survey_result = mysqli_query($conn, $survey_query);

                                if ($survey_result && mysqli_num_rows($survey_result) > 0) {
                                    $survey_row = mysqli_fetch_assoc($survey_result);
                                    echo ($survey_row['surveystatus'] === 'Done') ? 
                                        '<span class="badge bg-success">Done</span>' : 
                                        '<span class="badge bg-warning">Not yet</span>';
                                } else {
                                    echo '<span class="badge bg-warning">Not yet</span>';
                                }
                                ?>
                            </td>

                            <td>
                                <?php if ($row['status'] === '0') { ?>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                            data-service-id="<?php echo $row['car_id']; ?>" 
                                            aria-label="Delete service">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php } else { ?>
                                    <span>-</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No records found for the company.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>Company ID not found in session.</td></tr>";
            }
            ?>
        </tbody>
    </table>
  
    </div>
</div>
    <script>
            // JavaScript for Live Search
            document.getElementById('searchInput').addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('tbody tr');  // Update this line

                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    row.style.display = rowText.includes(searchTerm) ? '' : 'none';
                });
            });
        </script>



    <!-- Delete -->

    <script>
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-service-id');
                if (confirm('Are you sure you want to delete this service?')) {
                    fetch('delete_service.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `service_id=${serviceId}`
                    }).then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Service deleted successfully.');
                            location.reload(); // Refresh the page to update the table
                        } else {
                            alert('Failed to delete service: ' + data.message);
                        }
                    });
                }
            });
        });
        </script>



  
        <!-- Script for assigning mechanics -->
        <script>
            $(document).ready(function() {
                function loadRepairTable() {
                    $.get('repair_table_content.php', function(data) {
                        $('#repair-table-content').html(data);
                    });
                }

                $(document).off('click', '.btn-assign-mechanic').on('click', '.btn-assign-mechanic', function() {
                    var userId = $(this).data('user-id');
                    var carId = $(this).data('car-id');
                    var mechanicId = $(this).closest('tr').find('.mechanic-select').val();

                    var confirmation = confirm("Are you sure you want to assign this mechanic?");

                    if (confirmation) {
                        $.post('assign_mechanic.php', {
                            userId: userId,
                            carId: carId,
                            mechanicId: mechanicId
                        }, function(response) {
                            if (response.success) {
                                alert('Mechanic assigned successfully!');
                                loadRepairTable();  // Reload only the relevant part of the table
                            } else {
                                alert('Error assigning mechanic: ' + response.message);
                            }
                        }, 'json');
                    } else {
                        alert('Mechanic assignment was cancelled.');
                    }
                });
            });
        </script>
</div>
<br>

<!-- Modernized Section for Viewing Bookings -->
<div class="container mt-5">
    <h3 class="mb-4 text-center">Booking Overview</h3>
    <div class="table-responsive shadow rounded">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">User Name</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Booking Date</th>
                    <th scope="col">Company Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Function to determine background badge color based on booking date
                function getDateBadge($bookingDate) {
                    $currentDate = date('Y-m-d');
                    $dateDifference = (strtotime($currentDate) - strtotime($bookingDate)) / (60 * 60 * 24);

                    if ($dateDifference == 0) {
                        return '<span class="badge bg-success">Today</span>';
                    } elseif ($dateDifference <= 2) {
                        return '<span class="badge bg-warning text-dark">Ongoing</span>';
                    } elseif ($dateDifference <= 6) {
                        return '<span class="badge bg-info text-dark">This Week</span>';
                    } else {
                        return '<span class="badge bg-secondary">Older</span>';
                    }
                }

                // Query to fetch bookings along with user and car details
                $booking_query = "SELECT b.id, u.name AS user_name, c.carmodel, b.date, a.companyname, 
                                    (SELECT CONCAT(m.firstname) FROM mechanic AS m WHERE m.companyid = a.companyid LIMIT 1) AS mechanic_name
                                FROM bookings AS b
                                JOIN user AS u ON b.user_id = u.id
                                JOIN car AS c ON b.car_id = c.car_id
                                JOIN autoshop AS a ON a.companyid = c.companyid
                                WHERE a.companyid = '$companyid'";
                $booking_result = mysqli_query($conn, $booking_query);

                if ($booking_result && mysqli_num_rows($booking_result) > 0) {
                    while ($booking_row = mysqli_fetch_assoc($booking_result)) {
                        echo "<tr>
                                <td>{$booking_row['user_name']}</td>
                                <td>{$booking_row['carmodel']}</td>
                                <td>{$booking_row['date']} " . getDateBadge($booking_row['date']) . "</td>
                                <td>{$booking_row['companyname']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<br>

<div class="container mt-5">
    <h2 class="text-center mb-4">Mechanic Progress</h2>
    <div class="row">
        <br>
        <?php
        // Query to fetch all mechanics for the current companyid
        $mechanic_query = "SELECT m.mechanic_id, CONCAT(m.firstname) AS name, m.jobrole
                           FROM mechanic AS m
                           WHERE m.companyid = '$companyid'";
        $mechanic_result = mysqli_query($conn, $mechanic_query);

        if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
            while ($mechanic_row = mysqli_fetch_assoc($mechanic_result)) {
                ?>
                <div class="col-md-6 mb-4" style="width: 400px;">
                    <div class="card border-2">
                        <div class="card-body">
                            <h3 class="card-title mb-3"><?php echo $mechanic_row['name']; ?></h3>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo $mechanic_row['jobrole']; ?></h6>
                            <hr>
                            <?php
                            // Query to fetch progress data for the mechanic
                            $progress_query = "SELECT u.name AS user_name, c.carmodel, ROUND(AVG(p.progress_percentage), 2) AS avg_progress
                                               FROM accomplishtask AS p
                                               INNER JOIN user AS u ON p.user_id = u.id
                                               INNER JOIN car AS c ON p.car_id = c.car_id
                                               WHERE p.mechanic_id = '{$mechanic_row['mechanic_id']}'
                                               GROUP BY u.name, c.carmodel";
                            $progress_result = mysqli_query($conn, $progress_query);

                            if ($progress_result && mysqli_num_rows($progress_result) > 0) {
                                while ($progress_row = mysqli_fetch_assoc($progress_result)) {
                                    echo "<p class='card-text'><strong>User:</strong> {$progress_row['user_name']}</p>";
                                    echo "<p class='card-text'><strong>Car Model:</strong> {$progress_row['carmodel']}</p>";
                                    $progress_percentage = $progress_row['avg_progress'];
                                    $status = '';

                                    if ($progress_percentage < 80) {
                                        $status = 'Under Repair';
                                        $color = 'text-danger';
                                    } elseif ($progress_percentage >= 90) {
                                        $status = 'Ready for Assignment';
                                        $color = 'text-success';
                                    } else {
                                        $status = 'In Progress';
                                        $color = 'text-warning';
                                    }

                                    echo "<p class='card-text'><strong>Status:</strong> <span class='$color'>$status</span></p>";
                                    echo "<p class='card-text'><strong>Progress Percentage:</strong> {$progress_percentage}%</p>";
                                }
                            } else {
                                echo "<p class='card-text'><strong>Status:</strong> Available</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='col-12'>No mechanics available</p>";
        }
        ?>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<br>
<br>



