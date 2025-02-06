<?php
// Assuming you have a PDO connection $pdo
$query = "SELECT c.carmodel, c.bodyno, m.firstname AS mechanic_name, a.car_id, a.mechanic_id 
          FROM accomplishtask a
          JOIN car c ON a.car_id = c.car_id
          JOIN mechanic m ON a.mechanic_id = m.mechanic_id";
$stmt = $pdo->query($query);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($tasks);
?>
