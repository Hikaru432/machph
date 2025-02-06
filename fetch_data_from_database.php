<?php
// Include your database connection configuration file
include 'config.php';

// Initialize an empty array to store fetched data
$data = array();

// Query to fetch data from the selected_checkboxes table
$query = "SELECT * FROM selected_checkboxes";
$result = mysqli_query($conn, $query);

// Check if there are rows returned
if (mysqli_num_rows($result) > 0) {
    // Loop through each row and add data to the $data array
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

// Close database connection
mysqli_close($conn);

// Encode the $data array to JSON format
echo json_encode($data);
?>
