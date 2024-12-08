<?php
include 'config.php'; // Include your DB connection file

// Get the companyid from the POST request
$companyid = $_POST['companyid'] ?? null;

if ($companyid) {
    // Query to fetch products and their quantities for the selected company
    $query = "SELECT id, item_name, quantity FROM products WHERE companyid = '$companyid'";
    $result = mysqli_query($conn, $query);

    // Initialize an array to store the products
    $products = [];

    // Fetch products and add them to the products array
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    // Return the products as JSON
    echo json_encode(['products' => $products]);
} else {
    // If no companyid is provided, return an empty response
    echo json_encode(['products' => []]);
}
?>
