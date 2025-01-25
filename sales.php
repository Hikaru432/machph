<?php
session_start();
include 'config.php';

// Check if companyid exists in session
if (!isset($_SESSION['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Query for orders from the servicebilling table
$servicebilling_query = "
    SELECT sb.product_id, p.item_name, SUM(sb.quantity) AS total_orders 
    FROM servicebilling sb
    JOIN products p ON sb.product_id = p.id
    WHERE p.companyid = '$companyid'
    GROUP BY sb.product_id
";
$servicebilling_result = mysqli_query($conn, $servicebilling_query);

// Query for sales report from the salesreport table
$salesreport_query = "
    SELECT sr.product_id, p.item_name, SUM(sr.quantity) AS total_sales, SUM(sr.total_price) AS total_revenue
    FROM salesreport sr
    JOIN products p ON sr.product_id = p.id
    WHERE p.companyid = '$companyid'
    GROUP BY sr.product_id
";
$salesreport_result = mysqli_query($conn, $salesreport_query);

// Query for the most sold product based on the total sales
$dashboard_query = "
    SELECT p.item_name, SUM(sr.quantity) AS total_sales
    FROM salesreport sr
    JOIN products p ON sr.product_id = p.id
    WHERE p.companyid = '$companyid'
    GROUP BY sr.product_id
    ORDER BY total_sales DESC
    LIMIT 1
";
$dashboard_result = mysqli_query($conn, $dashboard_query);
$top_product = mysqli_fetch_assoc($dashboard_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Sales Dashboard</h2>

    <!-- Top Most Sold Product -->
    <div class="card mt-4">
        <div class="card-body">
            <h4 class="card-title">Top Most Sold Product</h4>
            <p class="card-text">
                <?php if ($top_product) : ?>
                    <strong>Product:</strong> <?php echo $top_product['item_name']; ?><br>
                    <strong>Total Sales:</strong> <?php echo $top_product['total_sales']; ?> units
                <?php else : ?>
                    No sales data available yet.
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Sales Report Table -->
    <div class="card mt-4">
        <div class="card-body">
            <h4 class="card-title">Sales Report</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Sales</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($salesreport_result)) : ?>
                        <tr>
                            <td><?php echo $row['item_name']; ?></td>
                            <td><?php echo $row['total_sales']; ?> units</td>
                            <td><?php echo number_format($row['total_revenue'], 2); ?> PHP</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders Summary from Service Billing -->
    <div class="card mt-4">
        <div class="card-body">
            <h4 class="card-title">Order Summary</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($servicebilling_result)) : ?>
                        <tr>
                            <td><?php echo $row['item_name']; ?></td>
                            <td><?php echo $row['total_orders']; ?> units</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
