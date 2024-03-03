<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the role of mechanic
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Retrieve mechanic details from the database based on the session user_id
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM mechanic WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$mechanic = mysqli_fetch_assoc($result);

// Proceed only if the user has the role of mechanic
if (!$mechanic) {
    header('Location: login.php'); // Redirect if user is not a mechanic
    exit();
}

// Proceed to fetch repair table content
$query = "SELECT user.id as user_id, user.name, car.carmodel, car.car_id, assignments.mechanic_id, mechanic.jobrole, CONCAT(mechanic_user.name, ' - ', mechanic.jobrole) AS mechanic_name
          FROM user
          JOIN car ON user.id = car.user_id
          LEFT JOIN assignments ON car.car_id = assignments.car_id
          LEFT JOIN mechanic ON assignments.mechanic_id = mechanic.mechanic_id
          LEFT JOIN user AS mechanic_user ON mechanic.user_id = mechanic_user.id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Error fetching data: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="#">Mechanic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger"></span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-white" href="#">Action</a></li>
                        <li><a class="dropdown-item text-white" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<h3 style="margin: 20px;">Job Available</h3>

<!-- The table -->
<div class="row" style="margin-top: 100px; padding: 0 15px 0 15px;">
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['name']; ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo $row['carmodel']; ?></h6>
                    <p class="card-text">
                      <strong> Assigned Mechanic:</strong> <span style="margin-left: 5px;"><?php echo ($row['jobrole'] && $row['mechanic_name']) ? $row['mechanic_name'] : 'Not Assigned'; ?> </span>
                    </p>
                    <a href="machrepair.php?user_id=<?php echo $row['user_id']; ?>&car_id=<?php echo $row['car_id']; ?>" class="btn btn-primary">Repair</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

</body>
</html>