<?php
session_start();
include 'config.php';

if (!isset($_GET['companyid'])) {
    die("Invalid company");
}

$companyid = $_GET['companyid'];

// Fetch company details
$company_query = "SELECT * FROM autoshop WHERE companyid = '$companyid'";
$company_result = mysqli_query($conn, $company_query);

// Check if the company exists
if (mysqli_num_rows($company_result) == 0) {
    die("Company not found");
}

$company = mysqli_fetch_assoc($company_result);

// Fetch products
$product_query = "SELECT * FROM products WHERE companyid = '$companyid'";
$product_result = mysqli_query($conn, $product_query);

// Check if the current company is a branch
$branch_query = "SELECT * FROM branch WHERE companyid = '$companyid'";
$branch_result = mysqli_query($conn, $branch_query);

$branches = [];
$main_company = null;

if (mysqli_num_rows($branch_result) > 0) {
    // If the company is a branch, get the main company and sibling branches
    $branch_data = mysqli_fetch_assoc($branch_result);
    $maincompanyid = $branch_data['maincompanyid'];

    // Fetch the main company details
    $main_query = "SELECT * FROM autoshop WHERE companyid = '$maincompanyid'";
    $main_result = mysqli_query($conn, $main_query);
    $main_company = mysqli_fetch_assoc($main_result);

    // Fetch sibling branches (excluding the current branch)
    $branches_query = "SELECT * FROM branch WHERE maincompanyid = '$maincompanyid' AND companyid != '$companyid'";
    $branches_result = mysqli_query($conn, $branches_query);

    while ($branch = mysqli_fetch_assoc($branches_result)) {
        $branches[] = $branch;
    }
} else {
    // If the company is a main company, fetch all branches under it
    $branches_query = "SELECT * FROM branch WHERE maincompanyid = '$companyid'";
    $branches_result = mysqli_query($conn, $branches_query);

    while ($branch = mysqli_fetch_assoc($branches_result)) {
        $branches[] = $branch;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company['companyname']; ?> - Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
       .sidebar {
                position: fixed;
                top: 56px;
                right: 0;
                width: 260px;
                height: calc(100% - 56px);
                background-color: #f8f9fa;
                padding: 20px;
                overflow-y: auto;
                border-left: 2px solid #ddd;
            }

            .branch-link {
                display: block;
                padding: 10px;
                margin-bottom: 5px;
                background: #ffffff;
                text-decoration: none;
                color: #333;
                border-radius: 5px;
                text-align: center;
                font-weight: bold;
                border: 1px solid #ddd;
                transition: all 0.3s;
            }

            .branch-link:hover, .main-branch {
                background: #b30036;
                color: white;
            }

            .request-form {
                background: white;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            }

            .request-form h5 {
                font-weight: bold;
                color: #333;
            }

            .form-control, .form-control-file {
                border-radius: 5px;
            }

            .btn-primary {
                background: #b30036;
                border: none;
            }

            .btn-primary:hover {
                background: #90002a;
            }
    </style>
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
                <li class="nav-item"><a class="nav-link text-white" href="company.php">Company</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Product Cards -->
<div class="container mt-4" style="margin-right: 270px;"> <!-- Adjust width for sidebar -->
    <h3 class="text-center"><?php echo $company['companyname']; ?> - Products</h3>
    <br>
    <br>
    <br>
    <?php if (mysqli_num_rows($product_result) > 0) { ?>
        <div class="row">
            <?php while ($product = mysqli_fetch_assoc($product_result)) { ?>
                <div class="col-md-3 mb-4">
                    <div class="card d-flex flex-column h-100">
                        <img src="data:image/<?php echo $product['product_image_type']; ?>;base64,<?php echo base64_encode($product['product_image']); ?>" class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $product['item_name']; ?></h5>
                            <p class="card-text"><strong>Price:</strong> ₱<?php echo number_format($product['selling_price'], 2); ?></p>
                            <form action="" method="post" class="mt-auto">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" class="form-control mb-2">
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-block">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p class="text-center">No products available for this company.</p>
    <?php } ?>
</div>

        <!-- Sidebar -->
        <div class="sidebar">
            <h5 class="text-center">Branches</h5>
            <?php if ($main_company): ?>
                <a href="productshop.php?companyid=<?php echo $main_company['companyid']; ?>" class="branch-link main-branch">
                    🏠 <?php echo $main_company['companyname']; ?> (Main)
                </a>
            <?php endif; ?>
            <?php foreach ($branches as $branch): ?>
                <a href="productshop.php?companyid=<?php echo $branch['companyid']; ?>" class="branch-link">
                    🏢 <?php echo $branch['branchname']; ?>
                </a>
            <?php endforeach; ?>

            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>

            <!-- Product Request Form -->
            <div class="request-form mt-4">
                <h5 class="text-center">Request and Report </h5>
                <form action="process_request.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="hidden" name="companyid" value="<?php echo $companyid; ?>">

                    <!-- Request Type Selection -->
                    <div class="form-group">
                        <label for="request_type">Select Type:</label>
                        <select id="request_type" name="request_type" class="form-control" onchange="toggleRequestFields()">
                            <option value="request">Request</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>

                    <!-- Product Name -->
                    <div class="form-group">
                        <label for="name">Product Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <!-- Quantity Field (Visible only for Requests) -->
                    <div id="quantity_field" class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" name="quantity" class="form-control" min="1">
                    </div>

                    <!-- Image Upload (Visible only for Requests) -->
                    <div id="image_field" class="form-group">
                        <label for="image">Upload Image:</label>
                        <input type="file" name="image" class="form-control-file">
                    </div>

                    <!-- Comments -->
                    <div class="form-group">
                        <label for="comments">Comments:</label>
                        <textarea name="comments" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
                </form>
            </div>
        </div>

        <script>
            function toggleRequestFields() {
                let type = document.getElementById('request_type').value;
                document.getElementById('quantity_field').style.display = (type === 'request') ? 'block' : 'none';
                document.getElementById('image_field').style.display = (type === 'request') ? 'block' : 'none';
            }
            toggleRequestFields();
        </script>
</body>
</html>