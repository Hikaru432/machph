<?php
// Assuming PDO connection $pdo
$carId = $_GET['car_id'];
$mechanicId = $_GET['mechanic_id'];

$query = "SELECT * FROM chatmechanic 
          WHERE car_id = :car_id AND mechanic_id = :mechanic_id
          ORDER BY timestamp ASC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':car_id', $carId);
$stmt->bindParam(':mechanic_id', $mechanicId);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
