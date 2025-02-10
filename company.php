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
    <title>Companies | Auto Shop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .company-card .card {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .company-card .card:hover {
            transform: translateY(-5px);
        }
        .company-card .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .company-card .card:hover .card-img-top {
            transform: scale(1.05);
        }
        .company-card .card-body {
            padding: 1.5rem;
        }
        .search-bar input {
            border-radius: 20px;
            padding-left: 20px;
            border: none;
        }
        .search-bar button {
            border-radius: 20px;
            padding: 0.375rem 1.5rem;
        }
        .page-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 2rem;
            position: relative;
        }
        .page-title:after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #b30036;
            margin: 10px auto;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="background-color: #b30036;">
    <div class="container">
        <a class="navbar-brand text-white font-weight-bold" href="home.php">Auto Shop</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link text-white" href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="shop.php">Shop</a></li>
                <li class="nav-item active"><a class="nav-link text-white font-weight-bold" href="company.php">Company</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#">Contact</a></li>
            </ul>

            <!-- Search Bar -->
            <form class="form-inline search-bar" id="searchForm" method="GET" action="company.php">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Search companies..." aria-label="Search" 
                           name="search" id="searchInput" value="<?php echo htmlspecialchars($search); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>

<!-- Company Cards -->
<div class="container py-5">
    <h3 class="text-center page-title">Explore Our Partner Companies</h3>
    <div class="row" id="companyCards">
        <?php 
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { 
        ?>
            <div class="col-md-4 mb-4 company-card">
                <a href="productshop.php?companyid=<?php echo htmlspecialchars($row['companyid']); ?>" 
                   class="text-decoration-none">
                    <div class="card shadow-lg h-100">
                        <img src="images.php?companyid=<?php echo htmlspecialchars($row['companyid']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($row['companyname']); ?>">
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold mb-3"><?php echo htmlspecialchars($row['companyname']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($row['city'] . ', ' . $row['country']); ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php 
            } 
        } else {
            echo '<div class="col-12 text-center">
                    <div class="alert alert-info">
                        No companies found matching your search criteria.
                    </div>
                  </div>';
        }
        ?>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- JavaScript for live search -->
<script>
$(document).ready(function() {
    let searchTimer;
    
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        const search = $(this).val();
        
        // Add debouncing to prevent too many requests
        searchTimer = setTimeout(function() {
            $.ajax({
                url: 'company.php',
                method: 'GET',
                data: { search: search },
                beforeSend: function() {
                    $('#companyCards').addClass('opacity-50');
                },
                success: function(response) {
                    $('#companyCards').html($(response).find('#companyCards').html());
                },
                complete: function() {
                    $('#companyCards').removeClass('opacity-50');
                }
            });
        }, 300);
    });
});
</script>

</body>
</html>
