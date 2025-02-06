<?php
include 'config.php';

$maincompanyid = $_GET['companyid']; // The main company ID passed from the URL
// Store the company ID in the session
$_SESSION['company_id'] = $maincompanyid;

// Fetch branches connected to the main company and their company details
$branches_query = "
    SELECT b.branchid, b.companyid AS branch_companyid, a.companyname
    FROM branch b
    INNER JOIN autoshop a ON b.companyid = a.companyid
    WHERE b.maincompanyid = $maincompanyid";
$branches_result = mysqli_query($conn, $branches_query);

// Fetch stock summary
$stock_summary_query = "
    SELECT 
        SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) AS no_stock,
        SUM(CASE WHEN quantity > 0 AND quantity <= 10 THEN 1 ELSE 0 END) AS low_stock,
        SUM(CASE WHEN quantity > 10 THEN 1 ELSE 0 END) AS in_stock
    FROM products
    WHERE companyid IN (SELECT companyid FROM branch WHERE maincompanyid = $maincompanyid)";
$stock_summary_result = mysqli_query($conn, $stock_summary_query);
$stock_summary = mysqli_fetch_assoc($stock_summary_result);

// Fetch stock requests from branches to the main company
$requests_query = "
    SELECT r.request_id, a.companyname AS destination_name, p.item_name, r.custom_item, r.quantity, r.request_date, r.request_type
    FROM request r
    LEFT JOIN autoshop a ON r.destination_companyid = a.companyid
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.branchid IN (SELECT branchid FROM branch WHERE maincompanyid = $maincompanyid)
    OR r.destination_companyid = $maincompanyid"; // This condition ensures requests to the main company are included
$requests_result = mysqli_query($conn, $requests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Stock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .status-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .status-card .card-body {
            text-align: center;
        }
        .status-no-stock { background-color: #ffcccc; color: #b30000; font-weight: bold; }
        .status-low-stock { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .status-in-stock { background-color: #d4edda; color: #155724; font-weight: bold; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f9f9f9; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .card-header-custom { background-color: #343a40; color: #fff; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php">Admin</a>
    </div>
</nav>
<br>
<div class="container mt-4">
    <h2 class="mb-4">Branch Stock</h2>

  <!-- Dashboard Section -->
        
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card status-card status-no-stock" data-bs-toggle="modal" data-bs-target="#stockModal" data-stock-type="no_stock">
                        <div class="card-body">
                            <h5 class="card-title">No Stock</h5>
                            <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                            <p class="card-text fs-4"><?php echo $stock_summary['no_stock']; ?> Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card status-card status-low-stock" data-bs-toggle="modal" data-bs-target="#stockModal" data-stock-type="low_stock">
                        <div class="card-body">
                            <h5 class="card-title">Low Stock</h5>
                            <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                            <p class="card-text fs-4"><?php echo $stock_summary['low_stock']; ?> Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card status-card status-in-stock" data-bs-toggle="modal" data-bs-target="#stockModal" data-stock-type="in_stock">
                        <div class="card-body">
                            <h5 class="card-title">In Stock</h5>
                            <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                            <p class="card-text fs-4"><?php echo $stock_summary['in_stock']; ?> Items</p>
                        </div>
                    </div>
                </div>
            </div>

               <!-- Modal for Stock Details -->
            <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="stockModalLabel">Stock Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Branch Name -->
                            <h4 id="modalBranchName"></h4>

                            <!-- Stock Status -->
                            <p id="modalStockStatus"></p>

                            <!-- Table to display stock items -->
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Product Image</th>
                                    </tr>
                                </thead>
                                <tbody id="modalStockItems">
                                    <!-- Stock items will be populated here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

   
            <div class="row mb-4">
            <div class="col-12">
                <h4>Stock Requests</h4>
                <table class="table table-bordered table-striped table-hover" id="requestsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Sender Company</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Comment</th> <!-- Custom item column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $requests_query = "
                            SELECT 
                                r.request_id, 
                                a.companyname AS sender_company, 
                                a.companyid AS sender_company_id, 
                                b.branchid AS sender_branch_id, 
                                p.item_name, 
                                r.quantity,
                                r.custom_item
                            FROM request r
                            LEFT JOIN autoshop a ON r.sender_companyid = a.companyid
                            LEFT JOIN branch b ON b.branchid = r.branchid
                            LEFT JOIN products p ON r.product_id = p.id
                            WHERE r.branchid IN (SELECT branchid FROM branch WHERE maincompanyid = $maincompanyid)
                            OR r.destination_companyid = $maincompanyid";

                        $requests_result = mysqli_query($conn, $requests_query);

                        if (mysqli_num_rows($requests_result) > 0): 
                            while ($request = mysqli_fetch_assoc($requests_result)): ?>
                                <tr data-request-id="<?php echo $request['request_id']; ?>" 
                                    data-company-id="<?php echo $request['sender_company_id']; ?>" 
                                    data-branch-id="<?php echo $request['sender_branch_id']; ?>" 
                                    class="clickable-row">
                                    <td><?php echo $request['sender_company']; ?></td>
                                    <td><?php echo $request['item_name']; ?></td>
                                    <td><?php echo $request['quantity']; ?></td>
                                    <td><?php echo $request['custom_item']; ?></td> <!-- Display custom item -->
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No Stock Requests Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Branch Cards Section -->
    <div class="row">
        <?php while ($branch = mysqli_fetch_assoc($branches_result)): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0"><?php echo $branch['companyname']; ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Stock Status</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $branch_companyid = $branch['branch_companyid'];

                                // Fetch products specific to the branch
                                $products_query = "
                                    SELECT p.item_name, p.category, p.quantity
                                    FROM products p
                                    WHERE p.companyid = $branch_companyid";

                                $products_result = mysqli_query($conn, $products_query);
                                if (mysqli_num_rows($products_result) > 0) {
                                    while ($product = mysqli_fetch_assoc($products_result)): ?>
                                        <tr>
                                            <td><?php echo $product['item_name']; ?></td>
                                            <td><?php echo $product['category']; ?></td>
                                            <td>
                                                <?php 
                                                    if ($product['quantity'] == 0) echo "Out of Stock";
                                                    elseif ($product['quantity'] <= 10) echo "Low Stock";
                                                    else echo "In Stock";
                                                ?>
                                            </td>
                                            <td><?php echo $product['quantity']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No Products Available</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

        <script>
           function openStockModal(branchId, stockType) {
                const modal = new bootstrap.Modal(document.getElementById('stockModal'));

                // Make AJAX request to fetch stock details
                fetch(`get_stock_details.php?branchid=${branchId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log(data); // Debugging: Check if data is being fetched correctly
                        
                        if (!data || !data.stock || data.stock.length === 0) {
                            console.error("No stock data available");
                            return;
                        }

                        const branchName = data.branch_name;
                        const stockItems = data.stock;

                        // Set the modal branch name
                        document.getElementById('modalBranchName').innerText = branchName;

                        // Set the stock status message based on the selected card (stockType)
                        let stockMessage = '';
                        if (stockType === 'no_stock') {
                            stockMessage = 'Products that are out of stock:';
                        } else if (stockType === 'low_stock') {
                            stockMessage = 'Products with low stock:';
                        } else if (stockType === 'in_stock') {
                            stockMessage = 'Products that are in stock:';
                        }

                        document.getElementById('modalStockStatus').innerText = stockMessage;

                        // Populate the modal table with stock items
                        const tableBody = document.getElementById('modalStockItems');
                        tableBody.innerHTML = ''; // Clear any previous data

                        stockItems.forEach(item => {
                            // Display only the products that match the selected stock type
                            if ((stockType === 'all') || (item.status.toLowerCase() === stockType.toLowerCase())) {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${item.item_name}</td>
                                    <td>${item.category}</td>
                                    <td>${item.quantity}</td>
                                    <td><img src="images/${item.product_image}" alt="${item.item_name}" width="50" /></td>
                                `;
                                tableBody.appendChild(row);
                            }
                        });

                        // Show the modal
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching stock details:', error);
                    });
            }

        </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
