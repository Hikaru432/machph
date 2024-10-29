<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['companyid']) || empty($_SESSION['companyid'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Check if mechanic_id is passed in the query
if (!isset($_GET['mechanic_id']) || empty($_GET['mechanic_id'])) {
    echo "<script>alert('Mechanic ID is missing!'); window.location.href='homemechanic.php';</script>";
    exit();
}

$mechanic_id = intval($_GET['mechanic_id']); // Ensure it's an integer

// Retrieve car data assigned to the mechanic
$query = "SELECT car.*, user.name AS username, manufacturer.name AS manufacturer_name, mechanic.jobrole, CONCAT(mechanic.firstname, ' - ', mechanic.jobrole) AS assigned_mechanic
          FROM car 
          JOIN user ON car.user_id = user.id
          LEFT JOIN assignments ON car.car_id = assignments.car_id
          LEFT JOIN mechanic ON assignments.mechanic_id = mechanic.mechanic_id
          LEFT JOIN autoshop ON mechanic.companyid = autoshop.companyid
          LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
          WHERE mechanic.mechanic_id = $mechanic_id";

$car_select = mysqli_query($conn, $query);

if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}
?>

<div class="container mt-4">
    <h2 class="text-center">Car Users</h2>

    <div class="table-responsive">
        <table id="carTable" class="table table-striped table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>User Name</th>
                    <th>Car Name</th>
                    <th>Plate No</th>
                    <th>Assign Mechanic</th>
                    <th>Parts</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($car_select)) : ?>
                <?php
                // Fetch additional user details using user_id
                $user_id = $row['user_id'];
                $user_query = "SELECT * FROM user WHERE id = $user_id";
                $user_result = mysqli_query($conn, $user_query);
                $user = mysqli_fetch_assoc($user_result);
                ?>

                <tr data-user-id="<?php echo (int)$user_id; ?>" data-car-id="<?php echo (int)$row['car_id']; ?>">
                    <td class="username-cell"><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?></td>
                    <td><?php echo isset($row['manufacturer_name']) ? htmlspecialchars($row['manufacturer_name']) : ''; ?></td>
                    <td><?php echo isset($row['plateno']) ? htmlspecialchars($row['plateno']) : ''; ?></td>
                    <td><?php echo isset($row['assigned_mechanic']) ? htmlspecialchars($row['assigned_mechanic']) : 'Not Assigned'; ?></td>
                    <td><a href="machvalidate.php?mechanic_id=<?php echo $mechanic_id; ?>&car_id=<?php echo $row['car_id']; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-info btn-sm">Parts</a></td>
                    <td><a href="machidentify.php?mechanic_id=<?php echo (int)$mechanic_id; ?>&car_id=<?php echo (int)$row['car_id']; ?>&user_id=<?php echo $user_id; ?>" class="btn btn-primary btn-sm">View Profile</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .invalid-label {
        color: red;
    }

    .highlighted {
        background-color: #ffff66;
    }

    /* Additional styles for table enhancements */
    table {
        width: 100%; /* Ensure full width */
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa; /* Light gray on hover */
    }

    .btn-info {
        background-color: #17a2b8; /* Bootstrap info color */
        border: none;
    }

    .btn-info:hover {
        background-color: #138496; /* Darker shade on hover */
    }
</style>
