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
                    <td><?php echo isset($row['username']) ? $row['username'] : ''; ?></td>
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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
    $(document).ready(function(){
        // Function to reload the table dynamically
        function reloadTable() {
            $.ajax({
                url: 'reload_table.php', // Replace 'reload_table.php' with the actual file name that fetches the updated data from the database
                type: 'GET',
                success: function(data) {
                    $('#carTable tbody').html(data);
                }
            });
        }

        // Function to highlight the newly added row
        function highlightRow(userId, carId) {
            var newRow = $('#carTable tbody tr[data-user-id="' + userId + '"][data-car-id="' + carId + '"]');
            newRow.addClass('highlighted');
            setTimeout(function(){
                newRow.removeClass('highlighted');
            }, 5000); // Highlight remains for 5 seconds (5000 milliseconds)
        }

        // Event listener for the "View Profile" button
        $(document).on('click', '.view-profile', function(){
            // Highlight the corresponding row
            var userId = $(this).closest('tr').data('user-id');
            var carId = $(this).closest('tr').data('car-id');
            highlightRow(userId, carId);
        });

        // Automatic table reloading every 10 seconds (10000 milliseconds)
        setInterval(reloadTable, 10000);
    });
</script>

</div>

<style>
    .invalid-label {
        color: red;
    }
</style>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>