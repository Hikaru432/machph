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
            <th>Mechanic approval</th>
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
                    <a href="machvalidate.php?user_id=<?php echo $row['user_id']; ?>&car_id=<?php echo $row['car_id']; ?>" class="btn btn-primary">Validate</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
