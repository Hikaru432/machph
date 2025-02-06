<?php
include 'config.php'; // Assuming this file contains your database connection code

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    // Fetch the image and its type from the database
    $query = "SELECT product_image, product_image_type FROM products WHERE id = '$productId'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imageData = $row['product_image'];
        $imageType = $row['product_image_type'] ?? 'image/jpeg'; // Default to JPEG if not set

        header("Content-type: $imageType");
        echo $imageData;
    } else {
        echo "Image not found.";
    }
} else {
    echo "No product ID provided.";
}
?>
