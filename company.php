<?php
session_start();
include 'config.php'; // Adjust this based on your database connection file

// Initialize the search term variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the query to filter results based on companyid or companyname
$query = "SELECT * FROM autoshop WHERE companyid LIKE ? OR companyname LIKE ?";
$stmt = $conn->prepare($query);
$searchTerm = "%" . $search . "%"; // Adding wildcards to the search term for partial matching
$stmt->bind_param("ss", $searchTerm, $searchTerm); // Bind both parameters to the query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #b30036;">
    <div class="container">
        <a class="navbar-brand text-white" href="home.php">Home</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link text-white" href="shop.php">Shop</a></li>
                <li class="nav-item active"><a class="nav-link text-white" href="company.php">Company</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#">Contact</a></li>
            </ul>

            <!-- Search Bar -->
            <form class="form-inline ml-auto" id="searchForm" method="GET" action="company.php">
                <input class="form-control mr-sm-2" type="search" placeholder="Search by Company ID or Name" aria-label="Search" name="search" id="searchInput" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>

<!-- Company Cards -->
<div class="container mt-4">
    <h3 class="text-center mb-4">All Companies</h3>
    <div class="row" id="companyCards">
        <?php 
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { 
        ?>
            <div class="col-md-4 mb-4 company-card">
                <a href="productshop.php?companyid=<?php echo $row['companyid']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="card shadow-lg">
                        <!-- Fetch Image from image.php -->
                        <img src="images.php?companyid=<?php echo $row['companyid']; ?>" class="card-img-top" alt="Company Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title font-weight-bold"><?php echo $row['companyname']; ?></h5>
                            <p class="card-text text-muted"><?php echo $row['city'] . ', ' . $row['country']; ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php 
            } 
        } else {
            echo "<p class='text-center w-100'>No companies found based on your search.</p>";
        }
        ?>
    </div>
</div>

<!-- JavaScript for live search -->
<script>
$(document).ready(function() {
    // Trigger search on input change
    $('#searchInput').on('keyup', function() {
        var search = $(this).val(); // Get the search value

        $.ajax({
            url: 'company.php', // Send request to the same page
            method: 'GET',
            data: { search: search }, // Send the search query as data
            success: function(response) {
                // Update the company cards div with the new results
                $('#companyCards').html($(response).find('#companyCards').html());
            }
        });
    });
});
</script>

</body>
</html>
