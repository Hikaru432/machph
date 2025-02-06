<?php
session_start();

// Include the database configuration file
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:index.php');
    exit();
}

// Get the car_id from the URL
if (!isset($_GET['car_id'])) {
    echo "No car selected.";
    exit();
}

$car_id = intval($_GET['car_id']);

// Perform the query to fetch the progress details for the selected car
$query = "SELECT accomplishtask.nameprogress, accomplishtask.progressing, accomplishtask.progressingpercentage 
          FROM accomplishtask 
          WHERE car_id = $car_id";
$result = mysqli_query($conn, $query);

// Perform the query to fetch image, category, and comment from the pictureprogress table
$queryPicture = "SELECT category, comment, picture, created_at 
                 FROM pictureprogress 
                 WHERE car_id = $car_id";
$pictureResult = mysqli_query($conn, $queryPicture);

// Check if queries were successful
if (!$result) {
    die('Error fetching progress details: ' . mysqli_error($conn));
}
if (!$pictureResult) {
    die('Error fetching picture details: ' . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Progress Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
          nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color:rgb(0, 0, 0);
            }
            .navbar-brand {
                color: white;
            }
            .navbar-brand:hover {
                color: #007BFF;
            }

           /* Chat */

           .chat-button {
                position: absolute;
                left: 1400px;
                top: 100px;
                background: linear-gradient(135deg, #007BFF, #0056b3);
                border: none;
                color: white;
                padding: 14px; /* Slightly increased padding */
                border-radius: 50%; /* Ensures a circular shape */
                box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease-in-out;
                width: 60px;  /* Increased size */
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                font-size: 28px; /* Adjust icon size */
            }

            .chat-button:hover {    
                background: linear-gradient(135deg, #0056b3, #00408d);
                transform: scale(1.1); /* Slightly bigger on hover */
                box-shadow: 6px 6px 15px rgba(0, 0, 0, 0.3);
            }



            /* Responsive Adjustments */
            @media (max-width: 1400px) {
                .chat-button {
                    left: 90%;
                    transform: translateX(-90%);
                }
            }

            @media (max-width: 768px) {
                .chat-button {
                    left: 80%;
                    top: 80px;
                    transform: translateX(-80%);
                    font-size: 14px;
                    padding: 10px 16px;
                    width: 160px;
                    height: 45px;
                }
            }

            @media (max-width: 576px) {
                .chat-button {
                    left: 70%;
                    top: 70px;
                    transform: translateX(-70%);
                    font-size: 12px;
                    padding: 8px 14px;
                    width: 140px;
                    height: 40px;
                }
            }

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="vehicleuser.php">Vehicle</a>
        </div>
    </nav>

    <div class="container position-relative">
        <a href="chatuser.php?car_id=<?= $car_id; ?>&user_id=<?= $_SESSION['user_id']; ?>" 
        class="btn chat-button d-flex align-items-center justify-content-center">
            <i class="bi bi-chat-dots fs-2"></i> <!-- Increased size with 'fs-2' -->
        </a>
    </div>




<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Car Progress Details</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Progress Name</th>
                        <th>Status</th>
                        <th>Progress Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['nameprogress']}</td>";
                            echo "<td>{$row['progressing']}</td>";
                            echo "<td>{$row['progressingpercentage']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No progress details found for this car.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <br>
    <br>
    <br>

    <div class="card shadow-lg border-0 mt-4 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient bg-success text-white text-center py-3">
            <h3 class="mb-0 fw-bold">📸 Additional Progress Details</h3>
        </div>
        <div class="card-body p-4">
            <?php
            $categories = [];
            while ($row = mysqli_fetch_assoc($pictureResult)) {
                $categories[$row['category']][] = $row;
            }
            
            if (!empty($categories)) {
                foreach ($categories as $category => $pictures) {
                    echo '<div class="card shadow-sm border-0 mt-4 rounded-3 overflow-hidden">';
                    echo '<div class="card-header bg-gradient bg-primary text-white text-center py-2">';
                    echo '<h4 class="mb-0 fw-semibold">📂 Category: ' . htmlspecialchars($category) . '</h4>';
                    echo '</div>';
                    echo '<div class="card-body p-4">';
                    echo '<div id="carousel-' . md5($category) . '" class="carousel slide" data-bs-ride="carousel">';
                    echo '<div class="carousel-inner rounded">';

                    $active = true;
                    foreach ($pictures as $picture) {
                        echo '<div class="carousel-item ' . ($active ? 'active' : '') . '">';
                        echo '<div class="text-center">';
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($picture['picture']) . '" 
                                alt="Progress Image" class="img-fluid rounded shadow-lg border" 
                                style="max-height: 320px; object-fit: cover;">';
                        echo '</div>';
                        echo '<p class="text-center mt-3 fs-5 text-secondary"><strong>📝 Comment:</strong> ' . htmlspecialchars($picture['comment']) . '</p>';
                        echo '<p class="text-muted text-end small"><i class="bi bi-calendar-event"></i> Uploaded on: ' . $picture['created_at'] . '</p>';
                        echo '</div>';
                        
                        $active = false;
                    }

                    echo '</div>'; // Close carousel-inner
                    
                    // Carousel controls
                    echo '<button class="carousel-control-prev" type="button" data-bs-target="#carousel-' . md5($category) . '" data-bs-slide="prev">';
                    echo '<span class="carousel-control-prev-icon shadow-sm bg-dark rounded-circle p-3" aria-hidden="true"></span>';
                    echo '<span class="visually-hidden">Previous</span>';
                    echo '</button>';
                    
                    echo '<button class="carousel-control-next" type="button" data-bs-target="#carousel-' . md5($category) . '" data-bs-slide="next">';
                    echo '<span class="carousel-control-next-icon shadow-sm bg-dark rounded-circle p-3" aria-hidden="true"></span>';
                    echo '<span class="visually-hidden">Next</span>';
                    echo '</button>';
                    
                    echo '</div>'; // Close carousel
                    echo '</div>'; // Close card-body
                    echo '</div>'; // Close card
                }
            } else {
                echo '<p class="text-center text-muted fs-5"><i class="bi bi-exclamation-circle"></i> No additional progress details found for this car.</p>';
            }
            ?>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="javascript:history.back();" class="btn btn-secondary">&larr; Back</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
