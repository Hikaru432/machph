<?php
// Assuming PDO connection $pdo
$data = json_decode(file_get_contents('php://input'), true);

$query = "INSERT INTO chatmechanic (car_id, mechanic_id, message, sender, timestamp) 
          VALUES (:car_id, :mechanic_id, :message, :sender, NOW())";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':car_id', $data['car_id']);
$stmt->bindParam(':mechanic_id', $data['mechanic_id']);
$stmt->bindParam(':message', $data['message']);
$stmt->bindParam(':sender', $data['sender']);
$stmt->execute();

echo json_encode(['success' => true]);
?>
