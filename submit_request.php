<?php
session_start(); // Start the session
include 'config.php'; // Include DB connection

// Get the posted data
$destination_companyid = $_POST['destination_companyid'];
$branch_id = $_POST['branch_id'] ?? NULL; // Nullable for Main requests
$product_id = $_POST['product_id'] ?? NULL; // Nullable for custom requests
$custom_item = $_POST['custom_item'] ?? NULL; // Custom request field
$quantity = $_POST['quantity'];

// Get the sender's company ID from session
$sender_companyid = $_SESSION['companyid']; // Assuming session contains companyid

// Determine request type
$request_type = $branch_id ? 'Branch' : 'Main'; // Branch if branch_id is set, otherwise Main

// Insert the request into the database
$insert_query = "INSERT INTO request (branchid, destination_companyid, product_id, custom_item, quantity, sender_companyid, request_type)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_query);

// Dynamically bind parameters
$branch_id = $branch_id ?: NULL; // Convert empty strings to NULL
$product_id = $product_id ?: NULL; // Convert empty strings to NULL
$custom_item = $custom_item ?: NULL; // Convert empty strings to NULL

// Bind the parameters - use 'i' for integer and 's' for string, and allow NULLs for nullable fields
$stmt->bind_param(
    'iiissis', // Types: integer, integer, integer, string, integer, integer, string
    $branch_id, // Nullable integer
    $destination_companyid, // Non-nullable integer
    $product_id, // Nullable integer
    $custom_item, // Nullable string
    $quantity, // Non-nullable integer
    $sender_companyid, // Non-nullable integer
    $request_type // Non-nullable string
);

if ($stmt->execute()) {
    // Successful execution, display confirmation dialog
    echo '<script>
            var proceed = confirm("Request submitted successfully. Do you want to proceed?");
            if (proceed) {
                alert("Request Proceeded Successfully!");
                // Redirect to the specific session page (otherstock.php) after successful submission
                window.location.href = "otherstock.php?companyid=' . $_SESSION['companyid'] . '&role=' . $_SESSION['role'] . '";
            } else {
                // If the user clicks cancel, go back to the previous page
                window.history.back();
            }
          </script>';
} else {
    echo '<script>alert("Error Sending Request! Please try again.");</script>';
}

$stmt->close();
$conn->close();
?>
