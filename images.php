<?php
include 'config.php';

if (isset($_GET['companyid'])) {
    $companyid = $_GET['companyid'];
    
    $query = "SELECT companyimage FROM autoshop WHERE companyid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $companyid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($companyimage);
    $stmt->fetch();

    // Debugging
    if (!$companyimage) {
        die("No image found for companyid: " . htmlspecialchars($companyid));
    }

    header("Content-Type: image/jpeg"); // Change to "image/png" if needed
    echo $companyimage;

    $stmt->close();
    exit();
}
?>
