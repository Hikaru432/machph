<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Fetch the image data from the database
    $query = "SELECT image FROM user WHERE id = '$id'";
    $result = mysqli_query($conn, $query) or die('Query failed');
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        header("Content-type: image/jpeg"); // Set the content type (adjust if image type differs)
        echo $row['image']; // Output the binary image data
    } else {
        echo "Image not found.";
    }
} else {
    echo "No ID specified.";
}
?>
