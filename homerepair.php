<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Fetch data from the database (Assuming $result contains the user and car information)
$result = mysqli_query($conn, "SELECT user.id as user_id, user.name, car.carmodel, car.car_id FROM user
                    JOIN car ON user.id = car.user_id");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <h2>Job</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Car Model</th>
                <th>Assign Mechanic</th>
                <th>Checking</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['carmodel']; ?></td>
                    <td>
                        <?php
                        // Assuming you have the mechanic_id stored somewhere
                        $mechanic_id = 1; // Change this to the specific mechanic_id
                        echo '<a href="machvalidate.php?user_id=' . $row['user_id'] . '&car_id=' . $row['car_id'] . '&mechanic_id=' . $mechanic_id . '" class="btn btn-primary">View Profile</a>';
                        ?>
                    </td>
                    <td>
                        <?php
                        // Button to direct to machrepair.php
                        echo '<a href="machrepair.php?user_id=' . $row['user_id'] . '&car_id=' . $row['car_id'] . '" class="btn btn-secondary">Check</a>';
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
