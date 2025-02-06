<?php
include 'config.php';

// Get the company ID from the URL parameters
$companyid = $_GET['companyid'] ?? 0;

// Prepare and execute the query to fetch products for the given company ID
$query = "SELECT id, item_name, category, quantity FROM products WHERE companyid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $companyid);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store the product data
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Return the product data as JSON
echo json_encode($products);
?>
