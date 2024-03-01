<?php
include 'config.php';

$query = "SELECT user.id as user_id, user.name, car.carmodel, car.car_id, approvals.status
          FROM user
          JOIN car ON user.id = car.user_id
          LEFT JOIN approvals ON user.id = approvals.user_id AND car.car_id = approvals.car_id";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Error fetching data: ' . mysqli_error($conn));
}
?>

<div class="container mt-5">
    <h2>Home Manager</h2>
    <table class="table ">
        <thead>
        <tr>
            <th>Name</th>
            <th>Car Model</th>
            <th>Mechanic Approval</th>
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
                    if (strtolower($row['status']) === '1') {
                        echo 'Approve';
                    } elseif (strtolower($row['status']) === '0') {
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
                    <form action="assign_mechanic.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <input type="hidden" name="car_id" value="<?php echo $row['car_id']; ?>">
                        <select name="mechanic_id" class="form-select">
                            <?php
                            // Fetch available mechanics from the mechanic table with their corresponding names from the user table
                            $mechanic_query = "SELECT mechanic.mechanic_id, mechanic.jobrole, user.name 
                                                FROM mechanic 
                                                JOIN user ON mechanic.user_id = user.id";
                            $mechanic_result = mysqli_query($conn, $mechanic_query);
                            if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
                                while ($mechanic_row = mysqli_fetch_assoc($mechanic_result)) {
                                    echo "<option value=\"{$mechanic_row['mechanic_id']}\">{$mechanic_row['jobrole']} - {$mechanic_row['name']}</option>";
                                }
                            } else {
                                echo "<option value=\"\">No mechanics available</option>";
                            }
                            ?>
                            <button type="submit" class="btn btn-primary">Assign Mechanic</button>
                        </select>


                        <button type="submit" class="btn btn-primary">Assign Mechanic</button>
                    </form>
                </td>
                <td>
                    <a href="machvalidate.php?user_id=<?php echo $row['user_id']; ?>&car_id=<?php echo $row['car_id']; ?>" class="btn btn-primary">Validate</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
