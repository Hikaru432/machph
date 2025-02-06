<?php
include 'config.php';

$branchId = $_GET['branchid'];  // The branch ID passed in the URL

// Query to fetch stock details for the selected branch
$products_query = "
    SELECT p.item_name, p.quantity, p.product_image, p.category, p.companyid
    FROM products p
    JOIN branch b ON p.companyid = b.companyid
    WHERE b.branchid = $branchId";

$products_result = mysqli_query($conn, $products_query);

// Prepare the response array
$response = [];
$response['branch_name'] = "";  // Will hold the branch name
$response['stock'] = [];         // Will hold product stock information
$response['companyid'] = "";    // Will hold the company id related to the branch

// Fetch the branch name and companyid from branch table
$branch_query = "SELECT branchname, companyid FROM branch WHERE branchid = $branchId";
$branch_result = mysqli_query($conn, $branch_query);

if ($branch_data = mysqli_fetch_assoc($branch_result)) {
    $response['branch_name'] = $branch_data['branchname'];
    $response['companyid'] = $branch_data['companyid']; // Store the companyid for the branch
}

// Fetch the products and categorize their stock status
while ($product = mysqli_fetch_assoc($products_result)) {
    // Determine the stock status
    if ($product['quantity'] == 0) {
        $status = 'No Stock';
    } elseif ($product['quantity'] >= 1 && $product['quantity'] <= 6) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }

    $response['stock'][] = [
        'item_name' => $product['item_name'],
        'category' => $product['category'],
        'quantity' => $product['quantity'],
        'status' => $status,
        'product_image' => $product['product_image']
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Debugging: log the response to make sure it's returning correctly
// Uncomment the line below for testing
// error_log(print_r($response, true));
?>
