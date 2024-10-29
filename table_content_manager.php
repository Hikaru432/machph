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
    <h2>Service Executive</h2>
    <table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Manufacturer</th>
            <th>Car Model</th>
            <th>Plate no.</th>
            <th>Color</th>
            <th>Mechanic Approval</th>
            <th>Assign Mechanic</th>
        </tr>
    </thead>
    <tbody class="table-body">
        <?php 
        // Ensure companyid is present in the session
        if ($companyid) {
            // Modify your query to ensure data is fetched based on the current companyid
            $query = "SELECT user.id as user_id, user.name, car.carmodel, car.plateno, car.car_id, car.color, manufacturer.name AS manuname, approvals.status, approvals.reason, autoshop.companyname
                      FROM user
                      JOIN car ON user.id = car.user_id
                      LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
                      LEFT JOIN approvals ON user.id = approvals.user_id AND car.car_id = approvals.car_id
                      JOIN autoshop ON autoshop.companyid = car.companyid
                      WHERE car.companyid = '$companyid'";
            $result = mysqli_query($conn, $query);

            // Check if the result is valid
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td><?php echo $row['manuname']; ?></td>
                        <td><?php echo $row['carmodel']; ?></td>
                        <td><?php echo $row['plateno']; ?></td>
                        <td><?php echo $row['color']; ?></td>
                        <td>
                            <?php
                            if ($row['status'] === '1') {
                                echo 'Approve';
                            } elseif ($row['status'] === '0') {
                                echo 'Not Approve';
                                if (!empty($row['reason'])) {
                                    echo ' - ' . $row['reason'];
                                }
                            } else {
                                echo 'Not Set';
                            }
                            ?>
                        </td>
                        <td>
                            <select name="mechanic_id" class="mechanic-select">
                                <option value="">Select mechanic</option>
                                <?php
                                // Fetch mechanics only for the current companyid
                                $mechanic_query = "SELECT mechanic_id, CONCAT(firstname) AS name, jobrole
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
                            <button type="button" class="btn-assign-mechanic" data-user-id="<?php echo $row['user_id']; ?>" data-car-id="<?php echo $row['car_id']; ?>">Assign</button>
                        </td>
                    </tr>
                <?php }
            } else {
                echo "<tr><td colspan='7'>No records found for the company.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Company ID not found in session.</td></tr>";
        }
        ?>
    </tbody>
</table>


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
            });
        });
    </script>
</div>

<br>

<!-- New section for viewing bookings based on companyname -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Bookings</h2>
    <table class="table">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Car Model</th>
                <th>Booking Date</th>
                <th>Company Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Function to determine background color based on booking date
            function getDateColor($bookingDate) {
                $currentDate = date('Y-m-d');
                $dateDifference = (strtotime($currentDate) - strtotime($bookingDate)) / (60 * 60 * 24);

                if ($dateDifference == 0) {
                    return 'style="background-color: green; color: white;"'; // Current date
                } elseif ($dateDifference <= 2) {
                    return 'style="background-color: yellow; color: black;"'; // 1-2 days ago
                } elseif ($dateDifference <= 6) {
                    return ''; // Normal color for more than 2 days ago
                } else {
                    return ''; // Normal color for more than 6 days ago
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
                    $dateColor = getDateColor($booking_row['date']); // Get color for the booking date
                    echo "<tr>
                            <td>{$booking_row['user_name']}</td>
                            <td>{$booking_row['carmodel']}</td>
                            <td $dateColor>{$booking_row['date']}</td>
                            <td>{$booking_row['companyname']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No bookings found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
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

<br>
<br>



