<?php
// getSenders.php
include 'config.php';

$request_id = $_GET['request_id'];
$company_id = $_GET['company_id'];

// Query to fetch unique senders based on the request
$query = "
    SELECT DISTINCT cm.sender_id, a.companyname AS company_name
    FROM chat_messages cm
    JOIN autoshop a ON cm.sender_id = a.companyid
    WHERE cm.request_id = '$request_id' AND cm.company_id = '$company_id'";

$result = mysqli_query($conn, $query);

$senders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $senders[] = $row;
}

echo json_encode($senders);
?>
