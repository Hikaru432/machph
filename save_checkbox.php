<?php
session_start();
include 'config.php';
require 'calculate_estimated_price.php';

// Check if companyid is set
if (!isset($_SESSION['companyid'])) {
    echo '<p>You must be logged in to view this page.</p>';
    return; // Stop execution if not logged in
}

// Ensure mechanic_id is set
if (!isset($_SESSION['mechanic_id'])) {
    echo '<p>You must be logged in as a mechanic to view this page.</p>';
    return; // Stop execution if mechanic is not logged in
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Check if selected checkboxes are present in the POST request
    if (isset($_POST['repairrecord']) && is_array($_POST['repairrecord'])) {
        // Retrieve user_id and car_id from POST data
        $user_id = $_POST['user_id'];
        $car_id = $_POST['car_id'];

        // Call the function to insert data into the database
        saveSelectedCheckboxes($_POST['repairrecord'], $_POST['quantity'], $conn, $user_id, $car_id);
        
        // Retrieve mechanic_id from session
        $mechanic_id = $_SESSION['mechanic_id'];

        // Redirect back to the form page after saving
        header('Location: homemechanic.php?mechanic_id=' . $mechanic_id);
        exit();
    }
}

// Function to insert selected checkboxes into the database
function saveSelectedCheckboxes($selected_checkboxes, $quantity, $conn, $user_id, $car_id) {
    // Retrieve mechanic_id from session
    $mechanic_id = $_SESSION['mechanic_id'];

    // Prepare and bind the INSERT statement
    $stmt = $conn->prepare("INSERT INTO repairrecord (category, checkbox_value, quantity, user_id, car_id, mechanic_id, other_product) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisss", $category, $checkbox_value, $quantity_value, $user_id, $car_id, $mechanic_id, $other_product);

    // Loop through each selected checkbox
    foreach ($selected_checkboxes as $category => $checkboxes) {
        // Set the category for all checkboxes in the current category
        foreach ($checkboxes as $checkbox_value) {
            // Get quantity for the current checkbox
            $quantity_value = $quantity[$checkbox_value];

            // Prepare the other_product field if necessary
            $other_product = isset($_POST['other_product'][$category][str_replace(' ', '_', $checkbox_value)]) ? $_POST['other_product'][$category][str_replace(' ', '_', $checkbox_value)] : '';

            // Set parameter values and execute the statement
            $category = $conn->real_escape_string($category); // Sanitize input
            $checkbox_value = $conn->real_escape_string($checkbox_value); // Sanitize input
            $quantity_value = intval($quantity_value); // Convert to integer
            $stmt->execute();
        }
    }

    // Close statement
    $stmt->close();
}
?>
