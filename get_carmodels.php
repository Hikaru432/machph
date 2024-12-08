<?php
include 'config.php';

// Check if the manufacturer_id is received via POST
if(isset($_POST['manufacturer_id'])) {
    $manufacturer_id = $_POST['manufacturer_id'];

    // Query to fetch the manufacturer name based on the received manufacturer_id
    $manufacturer_query = mysqli_query($conn, "SELECT name FROM manufacturer WHERE id = $manufacturer_id");

    // Check if the query was successful and if the manufacturer name exists
    if($manufacturer_query) {
        $manufacturer_name = mysqli_fetch_assoc($manufacturer_query)['name'];

        // Query to fetch car models based on the selected manufacturer_id
        $query = "SELECT * FROM car_model WHERE manufacturer_id = $manufacturer_id";

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Check if the query was successful
        if($result) {
            // Generate HTML options for car models
            $options = '<option value="">Select car model</option>';
            while($row = mysqli_fetch_assoc($result)) {
                $options .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
            }
            echo $options;
        } else {
            // Error handling if query fails
            echo '<option value="">Error retrieving car models</option>';
        }
    } else {
        // Error handling if manufacturer name not found
        echo '<option value="">Manufacturer not found</option>';
    }
} else {
    // Error handling if manufacturer_id is not received
    echo '<option value="">Manufacturer ID not received</option>';
}
?>
