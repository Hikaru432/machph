<?php
include 'config.php';

$car_select = mysqli_query($conn, "SELECT car.*, user.name as username, validation.status as validation_status, validation.comment
                                    FROM car 
                                    JOIN user ON car.user_id = user.id
                                    LEFT JOIN validation ON car.user_id = validation.user_id AND car.car_id = validation.car_id");

if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}

?>

<div class="container">
    <h2>Car user</h2>

    <div class="container">
        <table id="carTable" class="table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Car Name</th>
                    <th>Plate No</th>
                    <th>Manager Validation</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($car_select)) : ?>
                    <tr data-user-id="<?php echo (int)$row['user_id']; ?>" data-car-id="<?php echo (int)$row['car_id']; ?>">
                        <td class="username-cell"><?php echo isset($row['username']) ? $row['username'] : ''; ?></td>
                        <td><?php echo isset($row['manufacturer']) ? $row['manufacturer'] : ''; ?></td>
                        <td><?php echo isset($row['plateno']) ? $row['plateno'] : ''; ?></td>
                        <td>
                            <?php
                            if ($row['validation_status'] === 'invalid') {
                                echo '<span class="invalid-label">Invalid</span> - ' . (isset($row['comment']) ? $row['comment'] : '');
                            } elseif ($row['validation_status'] === 'valid') {
                                echo 'Valid';
                            } else {
                                echo 'Not determined yet';
                            }
                            ?>
                        </td>
                    <td><a href="machidentify.php?user_id=<?php echo (int)$row['user_id']; ?>&car_id=<?php echo (int)$row['car_id']; ?>" class="btn btn-primary">View Profile</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <style>
        .invalid-label {
            color: red;
        }
        .highlighted {
            background-color: #ffff66;
        }
    </style>
</div>
