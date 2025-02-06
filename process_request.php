<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $companyid = $_POST['companyid'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $request_type = $_POST['request_type'];
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);
    $quantity = ($request_type === 'request') ? intval($_POST['quantity']) : NULL;

    $image = NULL;
    if ($request_type === 'request' && isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
    }

    $query = "INSERT INTO requestproduct (user_id, companyid, name, quantity, image, comments, request_type) 
              VALUES ('$user_id', '$companyid', '$name', " . ($quantity ? "'$quantity'" : "NULL") . ", " . ($image ? "'$image'" : "NULL") . ", '$comments', '$request_type')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Request submitted successfully'); window.location.href='productshop.php?companyid=$companyid';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
