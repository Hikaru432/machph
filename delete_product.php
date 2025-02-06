<?php
include 'config.php'; 

if(isset($_GET['id'])) {
    $productId = $_GET['id'];
    
    $query = "DELETE FROM products WHERE id = $productId";
    $result = mysqli_query($conn, $query);
    
    if($result) {
        // Product deleted successfully
        echo "<script>alert('Product deleted successfully.');</script>";
        echo "<script>window.location.href = 'addproduct.php';</script>";
        exit();
    } else {
        // Handle delete failure
        echo "<script>alert('Failed to delete product. Please try again.');</script>";
        echo "<script>window.location.href = 'addproduct.php';</script>";
        exit();
    }
} else {
    // Handle missing product ID
    echo "<script>alert('Product ID not provided.');</script>";
    echo "<script>window.location.href = 'addproduct.php';</script>";
    exit();
}
?>
