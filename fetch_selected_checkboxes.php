<?php
// Start the session
session_start();

// Include database connection or any necessary files
include 'config.php';

// Check if car ID is provided via POST
if(isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    
    // Fetch checkboxes associated with the selected car ID and user ID
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

    // Prepare and execute query to fetch checkboxes
    $query = "SELECT * FROM repairrecord WHERE car_id = $car_id AND user_id = $user_id";
    $result = mysqli_query($conn, $query);

    if($result) {
        // If checkboxes are found, display them
        if(mysqli_num_rows($result) > 0) {
            echo '<form id="checkboxForm">';
            while($row = mysqli_fetch_assoc($result)) {
                // Display checkbox information, initially unchecked
                echo '<div>';
                echo '<input type="checkbox" class="checkbox" id="checkbox_' . $row['id'] . '">';
                echo '<label for="checkbox_' . $row['id'] . '">' . $row['checkbox_value'] . ' - ' . $row['quantity'] . '</label>';
                echo '</div>';
            }
            echo '</form>';
        } else {
            // If no checkboxes are found, display a message
            echo '<p>No checkboxes found for the selected car.</p>';
        }
    } else {
        // If there's an error with the query, display an error message
        echo 'Error: ' . mysqli_error($conn);
    }
} else {
    // If car ID is not provided, display an error message
    echo 'Error: Car ID not provided.';
}
?>

<script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const form = document.getElementById('checkboxForm');
            form.addEventListener('change', (event) => {
                if (event.target.classList.contains('checkbox') && event.target.checked) {
                    event.target.dataset.checked = true;
                } else {
                    event.target.checked = event.target.dataset.checked === 'true';
                }
            });
        });
    </script>
