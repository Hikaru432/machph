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
            <table class="table">
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
                        <tr>
                            <td><?php echo isset($row['username']) ? $row['username'] : ''; ?></td>
                            <td><?php echo isset($row['manufacturer']) ? $row['manufacturer'] : ''; ?></td>
                            <td><?php echo isset($row['plateno']) ? $row['plateno'] : ''; ?></td>
                            <td>
                                <?php
                                if ($row['validation_status'] === 'invalid') {
                                    echo '<a href="#" class="invalid-comment-link" data-comment="' . (isset($row['comment']) ? $row['comment'] : '') . '" data-toggle="modal" data-target="#commentModal">Invalid</a>';
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

        <!-- Modal -->
        <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commentModalLabel">Invalid Comment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="commentText"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>


        <script>
            $(document).ready(function(){
                $('.invalid-comment-link').click(function(){
                    var comment = $(this).data('comment');
                    $('#commentText').text(comment);
                });
            });
        </script>