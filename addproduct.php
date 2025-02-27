<?php
session_start();
include 'config.php'; // Assuming this file contains your database connection code

if(!isset($_SESSION['companyid'])){
    header('location:index.php');
    exit();
 } 

 $companyid = $_SESSION['companyid'];
//  echo "Company ID: " . $companyid; 


$role = isset($_GET['role']) ? $_GET['role'] : 'undefined';

if ($role === 'Main') {
    // Logic for main companies
} elseif ($role === 'Branch') {
    // Logic for branches
} else {
    echo "Invalid role.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    // Handle form submission
    $barcode = $_POST['barcode'];
    $itemName = $_POST['item_name'];
    $category = $_POST['category'];
    $system = $_POST['system'];
    $dateArrival = date('Y-m-d', strtotime($_POST['date_arrival']));
    $sellingPrice = $_POST['selling_price'];
    $originalPrice = $_POST['original_price'];
    $quantity = $_POST['quantity']; 
    $companyid = $_SESSION['companyid'];

    // Image upload handling
    $image = $_FILES['product_image']['tmp_name'];
    $imageData = addslashes(file_get_contents($image)); // Read the image as binary data

    // Calculate profit
    $profit = $sellingPrice - $originalPrice;

    // Insert into database
    $query = "INSERT INTO products (barcode, item_name, category, date_arrival, selling_price, original_price, profit, product_image, quantity, system, companyid) 
              VALUES ('$barcode', '$itemName', '$category', '$dateArrival', '$sellingPrice', '$originalPrice', '$profit', '$imageData', '$quantity', '$system', '$companyid')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        // Product added successfully
        header('Location: addproduct.php');
        exit();
    } else {
        // Error occurred while adding product
        $error_message = "Failed to add product.";
    }
}

// Function to determine quantity status color
function quantityStatusColor($quantity) {
    if ($quantity <= 0) {
        return 'text-danger'; 
    } elseif ($quantity <= 2) {
        return 'text-warning'; 
    } else {
        return 'text-success';
    }
}

function renderNavLink($role, $companyid) {
    if ($role === 'Branch') {
        return '<li class="nav-item">
                    <a class="nav-link text-white" href="otherstock.php?companyid=' . $companyid . '&role=' . urlencode($role) . '">Other Stock</a>
                </li>';
    } else {
        return '<li class="nav-item">
                    <a class="nav-link text-white" href="branchstock.php?companyid=' . $companyid . '&role=' . urlencode($role) . '">Branch Stock</a>
                </li>';
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <style>
    <style>
        .low-stock {
            color: yellow;
        }
        .no-stock {
            color: red;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-black">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon text-white"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php echo renderNavLink($role, $companyid); ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="report.php?companyid=<?php echo $companyid; ?>">Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="Request.php?companyid=<?php echo $companyid; ?>">Request</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container">

    <br>
    <br>

    <h3 style="font-weight: 800;">Product Inventory</h3>
    <!-- Button trigger modal -->
    <br>
    <br>
    <br>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        Add Product
    </button>

    <!-- Modal -->
    
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Product ID:</label>
                                <input type="text" class="form-control" id="barcode" name="barcode">
                            </div>
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name:</label>
                                <input type="text" class="form-control" id="item_name" name="item_name">
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Description:</label>
                                <input type="text" class="form-control" id="category" name="category">
                            </div>
                            <div class="mb-3">
                                <label for="system" class="form-label">System:</label>
                                <select class="form-select" id="system" name="system">
                                    <option value="Engine system">Engine system</option>
                                    <option value="Maintenance system">Maintenance system</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="date_arrival" class="form-label">Date Arrival:</label>
                                <input type="date" class="form-control" id="date_arrival" name="date_arrival">
                            </div>
                            <div class="mb-3">
                                <label for="selling_price" class="form-label">Selling Price:</label>
                                <input type="text" class="form-control" id="selling_price" name="selling_price" oninput="calculateProfit()">
                            </div>
                            <div class="mb-3">
                                <label for="original_price" class="form-label">Original Price:</label>
                                <input type="text" class="form-control" id="original_price" name="original_price" oninput="calculateProfit()">
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1">
                            </div>
                            <div class="mb-3">
                                <label for="product_image" class="form-label">Product Image:</label>
                                <input type="file" class="form-control" id="product_image" name="product_image">
                            </div>
                            <input type="hidden" name="companyid" value="<?php echo $companyid; ?>">
                            <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Display error message if any
        if (isset($error_message)) {
            echo "<div class='alert alert-danger' role='alert'>$error_message</div>";
        }
        ?>

        <br>
        <br>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Product ID</th>
                    <th scope="col">Item Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Category</th>
                    <th scope="col">Date Arrival</th>
                    <th scope="col">Original Price</th>
                    <th scope="col">Selling Price</th>
                    <!-- <th scope="col">Profit</th> -->
                    <th scope="col">Quantity</th>
                    <th scope="col">Status</th>
                    <th scope="col">Image</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch products from the database belonging to the specific company
                $query = "SELECT * FROM products WHERE companyid = ?";
                // Prepare the query
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    // Bind the companyid parameter
                    mysqli_stmt_bind_param($stmt, "i", $companyid);
                    // Execute the query
                    mysqli_stmt_execute($stmt);
                    // Get the result set
                    $result = mysqli_stmt_get_result($stmt);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Display product details in each row
                            echo "<tr>";
                            echo "<td>{$row['barcode']}</td>";
                            echo "<td>{$row['item_name']}</td>";
                            echo "<td>{$row['category']}</td>";
                            echo "<td>{$row['system']}</td>";
                            echo "<td>{$row['date_arrival']}</td>";
                            echo "<td>{$row['original_price']}</td>";
                            echo "<td>{$row['selling_price']}</td>";
                            // echo "<td>{$row['profit']}</td>";
                            echo "<td>{$row['quantity']}</td>";
                            echo "<td class='" . quantityStatusColor($row['quantity']) . "'>";
                            if ($row['quantity'] <= 0) {
                                echo "Out of Stock";
                            } elseif ($row['quantity'] <= 2) {
                                echo "Low Stock";
                            } else {
                                echo "In Stock";
                            }
                            echo "</td>";
                            echo "<td><img src='image_product.php?id={$row['id']}' width='50' height='50'></td>";
                            echo "<td>";
                            echo "<button type='button' class='btn btn-primary edit-button' data-product-id='{$row['id']}'>Edit</button>";  
                            echo "<button type='button' class='btn btn-danger' onclick='confirmDelete({$row['id']})'>Delete</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No products found.</td></tr>";
                    }
                    // Close the statement
                    mysqli_stmt_close($stmt);
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
                ?>
            </tbody>
        </table>
    

<?php
// Edit Modals for each product
if ($result && mysqli_num_rows($result) > 0) {
    mysqli_data_seek($result, 0); // Reset result pointer
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='modal fade' id='editModal{$row['id']}' tabindex='-1' aria-labelledby='editModalLabel{$row['id']}' aria-hidden='true'>";
        echo "<div class='modal-dialog'>";
        echo "<div class='modal-content'>";
        echo "<div class='modal-header'>";
        echo "<h5 class='modal-title' id='editModalLabel{$row['id']}'>Edit Product</h5>";
        echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
        echo "</div>";
        echo "<div class='modal-body'>";
        echo "<form method='post' action='edit_product.php'>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_barcode' class='form-label'>Barcode:</label>";
        echo "<input type='text' class='form-control' id='edit_barcode' name='edit_barcode' value='{$row['barcode']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_item_name' class='form-label'>Item Name:</label>";
        echo "<input type='text' class='form-control' id='edit_item_name' name='edit_item_name' value='{$row['item_name']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_category' class='form-label'>Description:</label>";
        echo "<input type='text' class='form-control' id='edit_category' name='edit_category' value='{$row['category']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_system' class='form-label'>Category:</label>";
        echo "<select class='form-select' id='edit_system' name='edit_system'>";
        echo "<option value='Engine system' " . ($row['system'] == 'Engine system' ? 'selected' : '') . ">Engine system</option>";
        echo "<option value='Maintenance system' " . ($row['system'] == 'Maintenance system' ? 'selected' : '') . ">Maintenance system</option>";
        echo "</select>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_date_arrival' class='form-label'>Date Arrival:</label>";
        echo "<input type='date' class='form-control' id='edit_date_arrival' name='edit_date_arrival' value='{$row['date_arrival']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_selling_price' class='form-label'>Selling Price:</label>";
        echo "<input type='text' class='form-control' id='edit_selling_price' name='edit_selling_price' value='{$row['selling_price']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_original_price' class='form-label'>Original Price:</label>";
        echo "<input type='text' class='form-control' id='edit_original_price' name='edit_original_price' value='{$row['original_price']}'>";
        echo "</div>";
        // echo "<div class='mb-3'>";
        // echo "<label for='edit_profit' class='form-label'>Profit:</label>";
        // echo "<input type='text' class='form-control' id='edit_profit' name='edit_date_profit' value='{$row['profit']}'>";
        // echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_quantity' class='form-label'>Quantity:</label>";
        echo "<input type='number' class='form-control' id='edit_quantity' name='edit_quantity' value='{$row['quantity']}'>";
        echo "</div>";
        echo "<div class='mb-3'>";
        echo "<label for='edit_product_image' class='form-label'>Upload New Product Image (optional):</label>";
        echo "<input type='file' class='form-control' id='edit_product_image' name='edit_product_image'>";
        echo "</div>";
        echo "<input type='hidden' name='edit_product' value='1'>";
        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
        echo "<button type='submit' class='btn btn-primary'>Save Changes</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}
?>
</div>
<script>
    function confirmDelete(productId) {
        if (confirm("Are you sure you want to delete this product?")) {
            // Redirect to delete_product.php with the product ID as a parameter
            window.location.href = "delete_product.php?id=" + productId;
        }
    }

    $(document).ready(function() {
        // Ensure document is ready before attaching click event listeners
        $('.edit-button').click(function() {
            var productId = $(this).data('product-id');
            openEditModal(productId);
        });
    });

    function openEditModal(productId) {
        // Trigger the modal with the specified product ID
        var modalId = "#editModal" + productId;
        $(modalId).modal('show');
    }
</script>

<script>
    function calculateProfit() {
        var sellingPrice = parseFloat(document.getElementById('selling_price').value);
        var originalPrice = parseFloat(document.getElementById('original_price').value);
        var profit = sellingPrice - originalPrice;
        document.getElementById('profit').value = profit.toFixed(2);
    }
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
