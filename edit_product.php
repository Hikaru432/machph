<?php
include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
   
    $productId = $_POST['product_id'];
    $editBarcode = $_POST['edit_barcode'];
    $editItemName = $_POST['edit_item_name']; 
    $editCategory = $_POST['edit_category'];
    $editDateArrival = $_POST['edit_date_arrival'];
    $editSellingPrice = $_POST['edit_selling_price']; 
    $editOriginalPrice = $_POST['edit_original_price'];
    $editQuantity = $_POST['edit_quantity']; 

    // Example of updating all fields
    $query = "UPDATE products SET barcode = '$editBarcode', item_name = '$editItemName', category = '$editCategory', date_arrival = '$editDateArrival', selling_price = '$editSellingPrice', original_price = '$editOriginalPrice', quantity = '$editQuantity' WHERE id = $productId";

    $result = mysqli_query($conn, $query);
    
    if($result) {
        // Product updated successfully
        echo "<script>alert('Product updated successfully.');</script>";
        echo "<script>window.location.href = 'addproduct.php';</script>";
        exit();
    } else {
        // Handle update failure
        echo "<script>alert('Failed to update product. Please try again.');</script>";
        echo "<script>window.location.href = 'addproduct.php';</script>";
        exit();
    }
}
?>
