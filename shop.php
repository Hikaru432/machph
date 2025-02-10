<?php
session_start();
include 'config.php';

// Fetch products from the database
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);

// Check if user is logged in, if not redirect to login page
if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit();
}

$user_id = $_SESSION['user_id'];


// Handle adding items to cart
if(isset($_POST['add_to_cart'])){
    // Retrieve product details
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Add item to cart session variable
    if(!isset($_SESSION['cart'][$product_id]))
        $_SESSION['cart'][$product_id] = 0;
    $_SESSION['cart'][$product_id] += $quantity;
    
    // Redirect back to shop page
    header('Location: shop.php');
    exit();
}

// Handle editing product quantity
if(isset($_POST['update_quantity'])){
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['new_quantity'];
    
    // Update quantity in cart session variable
    $_SESSION['cart'][$product_id] = $new_quantity;
    
    // Redirect back to shop page
    header('Location: shop.php');
    exit();
}

// Handle deleting product from cart
if(isset($_POST['delete_product'])){
    $product_id = $_POST['product_id'];
    
    // Remove product from cart session variable
    unset($_SESSION['cart'][$product_id]);
    
    // Redirect back to shop page
    header('Location: shop.php');
    exit();
}

// Fetch companyid from the session or URL parameter
$companyid = isset($_GET['companyid']) ? $_GET['companyid'] : (isset($_SESSION['companyid']) ? $_SESSION['companyid'] : null);

// Display company ID (you may remove this line if not needed)
// echo "Company ID: " . $companyid; 

// Fetch company information based on companyid
$company_info = null; // Initialize the variable
if (!empty($companyid)) {
    $company_select = mysqli_query($conn, "SELECT companyname FROM autoshop WHERE companyid = '$companyid'");
    if ($company_row = mysqli_fetch_assoc($company_select)) {
        $company_info = $company_row; // Assign company information to $company_info variable
        // echo "Company Name: " . $company_info['companyname'];
    } else {
        echo "Company not found";
    }
}

// Fetch products from the database based on companyid
if (!empty($companyid)) {
    $query = "SELECT * FROM products WHERE companyid = $companyid";
} else {
    // If companyid is not provided, fetch all products
    $query = "SELECT * FROM products";
}


// Fetch products from the database based on search query
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = $_GET['search'];
    $query = "SELECT * FROM products WHERE item_name LIKE '%$search%'";
} else {
    // If no search query, fetch all products
    $query = "SELECT * FROM products";
}
$result = mysqli_query($conn, $query);

// Code for system redirection
if (isset($_GET['system']) && !empty($_GET['system'])) {
    $system = $_GET['system'];
    $query = "SELECT * FROM products WHERE system = '$system'";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        /* Button Styles */
        .btn-primary {
            background-color: #b30036;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #8c002b;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(179, 0, 54, 0.3);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
            background: linear-gradient(45deg, #b30036, #ff1a1a);
            color: white;
        }

        /* Price Tag Style */
        .price-tag {
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
            color: #b30036;
            display: inline-block;
            margin: 10px 0;
        }

        /* Fixed Container Style */
        #fixedContainer {
            transition: all 0.3s ease;
            cursor: pointer;
            background: linear-gradient(45deg, #000000, #333333);
            box-shadow: -2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        #fixedContainer:hover {
            transform: translateX(-5px);
        }

        /* Table Styles */
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-top: none;
        }

        /* Quantity Input Style */
        input[type="number"] {
            border-radius: 20px;
            border: 2px solid #dee2e6;
            padding: 8px 15px;
        }

        /* Updated Navigation Link Hover Effect */
        .nav-link {
            position: relative;
            padding: 8px 15px;
            color: white !important; /* Force white color */
        }

        .nav-link:hover {
            color: rgba(255, 255, 255, 0.9) !important; /* Slightly dimmed white on hover */
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: white;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
<!-- Navigation bar - Updated with modern design -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #b30036; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <div class="container">
        <a class="navbar-brand" href="home.php" style="font-size: 1.5rem; letter-spacing: 1px; color: white;">
            <i class="fas fa-home mr-2"></i>Home
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="#"><i class="fas fa-envelope mr-1"></i>Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="company.php"><i class="fas fa-building mr-1"></i>Company</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#" data-toggle="modal" data-target="#cartModal">
                        <i class="fas fa-shopping-cart mr-1"></i>Cart 
                        <span class="badge badge-pill badge-light ml-1"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                    </a>
                </li>
            </ul>
            <!-- Enhanced Search Bar -->
            <form class="form-inline my-2 my-lg-0" method="GET" action="shop.php">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Search products..." aria-label="Search" name="search" style="border-radius: 20px 0 0 20px;">
                    <div class="input-group-append">
                        <button class="btn btn-light" type="submit" style="border-radius: 0 20px 20px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>

    <!-- <div class="row mb-2">
        <div class="col-md-12 text-center">
            <a href="shop.php?system=Engine" class="btn btn-primary mr-2">Engine System</a>
            <a href="shop.php?system=Maintenance" class="btn btn-primary">Maintenance System</a>
        </div>
    </div> -->

<!-- Main content -->
<div class="container mt-4">
    <div class="row">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <!-- Update the image source to point to the image_product.php -->
                    <img src="image_product.php?id=<?php echo $row['id']; ?>" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['item_name']; ?></h5>
                        <p class="card-text"><strong>Price:</strong> <span style="padding: 4px;">₱</span><?php echo $row['selling_price']; ?></p>
                        <?php
                        // Fetch company name based on companyid
                        $companyid = $row['companyid'];
                        $company_query = mysqli_query($conn, "SELECT companyname FROM autoshop WHERE companyid = '$companyid'");
                        $company_row = mysqli_fetch_assoc($company_query);
                        $companyname = $company_row['companyname'];
                        ?>
                        <p class="card-text"><strong>Company:</strong> <?php echo $companyname; ?></p> <!-- Display company name here -->
                        <a href="#" class="text-primary font-italic" data-toggle="modal" data-target="#productModal<?php echo $row['id']; ?>">Product Details</a>
                        <form action="shop.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control mb-2">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-block">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Product details modals -->
<?php mysqli_data_seek($result, 0); // Reset result pointer to the beginning ?>
<?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="modal fade" id="productModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="productModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel<?php echo $row['id']; ?>">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Product details -->
                    <div class="row">
                        <div class="col-md-6">
                            <img src="image_product.php?id=<?php echo $row['id']; ?>" class="img-fluid" alt="Product Image">
                        </div>
                        <div class="col-md-6">
                            <p><strong>Item Name:</strong> <?php echo $row['item_name']; ?></p>
                            <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
                            <p><strong>Price:</strong> <span style="padding: 4px;">₱</span><?php echo $row['selling_price']; ?></p>
                            <!-- Add more details as needed -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>


<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="cartModalLabel"><i class="fas fa-shopping-cart"></i> Shopping Cart</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Cart content here -->
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPrice = 0;
                                foreach ($_SESSION['cart'] as $product_id => $quantity): 
                                    // Fetch product details from products table
                                    $product_query = "SELECT * FROM products WHERE id = $product_id";
                                    $product_result = mysqli_query($conn, $product_query);
                                    $product_row = mysqli_fetch_assoc($product_result);
                                    $product_name = $product_row['item_name'];
                                    $product_price = $product_row['selling_price'];
                                    $totalProductPrice = $product_price * $quantity;
                                    $totalPrice += $totalProductPrice;
                                ?>
                                    <tr>
                                        <td><?php echo $product_name; ?></td>
                                        <td>₱<?php echo number_format($product_price, 2); ?></td>
                                        <td><?php echo $quantity; ?></td>
                                        <td>₱<?php echo number_format($totalProductPrice, 2); ?></td>
                                        <td class="text-center">
                                            <form action="shop.php" method="post">
                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="font-weight-bold">
                                <tr>
                                    <td colspan="3" class="text-right">Total:</td>
                                    <td colspan="2" class="text-right">₱<?php echo number_format($totalPrice, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">Your cart is empty.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <!-- Proceed to Checkout -->
                <a href="checkout.php?companyid=<?php echo $companyid; ?>" class="btn btn-success btn-lg btn-block">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </a>
                <!-- Close button -->
                <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


<div id="fixedContainer"  data-toggle="modal" data-target="#exampleModal" style="position: fixed; top: 100px; right: -10px; width: 70px; height: 70px; background-color: black; border-radius: 8px; z-index: 999; display: flex; align-items: center; justify-content: center;">
    <h1 style="font-weight: 800; color: white; font-size: 20px; cursor: pointer;">Parts</h1>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Car</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Car Selection Dropdown -->
                <div class="form-group">
                    <label for="carSelect">Select Car:</label>
                    <select class="form-control" id="carSelect" name="carSelect">
                        <option value="">Select Car</option>
                        <?php
                        // Fetch cars associated with the user from the database
                        $user_id = $_SESSION['user_id'];
                        $car_query = "SELECT * FROM car WHERE user_id = $user_id";
                        $car_result = mysqli_query($conn, $car_query);
                        while($car_row = mysqli_fetch_assoc($car_result)): ?>
                            <option value="<?php echo $car_row['car_id']; ?>"><?php echo $car_row['plateno'] . ' - ' . $car_row['carmodel']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Display Selected Checkboxes -->
                <div id="checkboxList">
                    <!-- Selected checkboxes will be displayed here -->
                </div>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Handle car selection change event
    $('#carSelect').change(function() {
        var carId = $(this).val(); // Get the selected car ID
        if (carId !== '') {
            // Make an AJAX request to fetch checkboxes associated with the selected car
            $.ajax({
                url: 'fetch_selected_checkboxes.php', // Replace with the appropriate URL
                method: 'POST',
                data: { car_id: carId },
                success: function(response) {
                    // Display the fetched checkboxes in the modal
                    $('#checkboxList').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    // Optionally handle errors
                }
            });
        } else {
            // Clear the checkbox list if no car is selected
            $('#checkboxList').empty();
        }
    });
});
</script>

<script>
    // Function to fetch and populate products in the modal
    $('#exampleModal').on('show.bs.modal', function(event) {
        var modal = $(this);
        // Fetch data from the server using AJAX
        fetch('fetch_data_from_database.php')
            .then(response => response.json())
            .then(data => {
                // Clear previous product rows
                modal.find('#partsTableBody').empty();
                // Add product rows dynamically
                data.forEach(item => {
                    var totalPrice = item.quantity * item.price;
                    var productHTML = 
                        <tr>
                            <td>${item.checkbox_value}</td>
                            <td>${item.quantity}</td>
                        </tr>;
                    modal.find('#partsTableBody').append(productHTML);
                });
                // Calculate overall total price
                // calculateOverallTotal();
            })
            .catch(error => console.error('Error:', error));
    });

    // Calculate overall total price
    // function calculateOverallTotal() {
    //     var modal = $('#exampleModal');
    //     var overallTotal = 0;
    //     modal.find('#partsTableBody tr').each(function() {
    //         overallTotal += parseInt($(this).find('td:eq(3)').text().replace('₱', ''));
    //     });
    //     $('#overallTotal').text(overallTotal);
    // }

    // Save selected parts
    $('#saveButton').click(function() {
        var modal = $('#exampleModal');
        var selectedParts = [];
        modal.find('#partsTableBody tr').each(function() {
            var quantity = parseInt($(this).find('td:eq(1)').text());
            var price = parseInt($(this).find('td:eq(2)').text().replace('₱', ''));
            selectedParts.push({
                checkboxValue: $(this).find('td:eq(0)').text(),
                quantity: quantity,
                price: price
            });
        });
        // Send selected parts to the server for saving
        $.ajax({
            url: 'save_selected_parts.php',
            type: 'POST',
            data: { selectedParts: selectedParts },
            success: function(response) {
                // Display success message or handle the response
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error(xhr.responseText);
            }
        });
        // Close the modal after saving
        $('#exampleModal').modal('hide');
    });
</script>

</body>
</html>