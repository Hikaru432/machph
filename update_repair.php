<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $approval_status = $_POST['approval_status'];
    $not_approve_reason = isset($_POST['not_approve_reason']) ? $_POST['not_approve_reason'] : null;
    
    // Array to store Engine Overhaul problems
    $engine_overhaul_problems = isset($_POST['engine_overhaul_problems']) ? $_POST['engine_overhaul_problems'] : array();

    // Process Engine Overhaul problems
    foreach ($engine_overhaul_problems as $engine_overhaul_problem) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Engine Overhaul', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $engine_overhaul_problem);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Array to store Engine Low Power problems
    $engine_low_power_problems = isset($_POST['engine_low_power_problems']) ? $_POST['engine_low_power_problems'] : array();

    // Process Engine Low Power problems
    foreach ($engine_low_power_problems as $engine_low_power_problem) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Engine Low Power', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $engine_low_power_problem);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Array to store Electrical problems
    $electrical_problems = isset($_POST['electrical_problems']) ? $_POST['electrical_problems'] : array();

    // Process Electrical problems
    foreach ($electrical_problems as $electrical_problem) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Electrical Problem', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $electrical_problem);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Maintenance

     // Process Battery
     $battery_problems = isset($_POST['battery_problems']) ? $_POST['battery_problems'] : array();

     foreach ($battery_problems as $battery_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Battery', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $battery_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Process Light
    $light_problems = isset($_POST['light_problems']) ? $_POST['light_problems'] : array();

    foreach ($light_problems as $light_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Light', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $light_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Process Oil
    $oil_problems = isset($_POST['oil_problems']) ? $_POST['oil_problems'] : array();

    foreach ($oil_problems as $oil_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Oil', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $oil_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Process Water
    $water_problems = isset($_POST['water_problems']) ? $_POST['water_problems'] : array();
    
    foreach ($water_problems as $water_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Water', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $water_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Process Brake
    $brake_problems = isset($_POST['brake_problems']) ? $_POST['brake_problems'] : array();

    foreach ($brake_problems as $brake_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Brake', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $brake_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    
    // Process Air
    $air_problems = isset($_POST['air_problems']) ? $_POST['air_problems'] : array();

    foreach ($air_problems as $air_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Air', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $air_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Process Gas
    $gas_problems = isset($_POST['gas_problems']) ? $_POST['gas_problems'] : array();

    foreach ($gas_problems as $gas_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Gas', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $gas_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    
    // Process Tire
    $tire_problems = isset($_POST['tire_problems']) ? $_POST['tire_problems'] : array();

    foreach ($tire_problems as $tire_problems) {
        // Insert into 'repair' table
        $insert_into_repair_query = "INSERT INTO repair 
            (user_id, plateno, problem, diagnosis) 
            VALUES 
            (?, ?, 'Tire', ?)";
        
        $stmt = mysqli_prepare($conn, $insert_into_repair_query);
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $car_id, $tire_problems);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // For Approvals

    
        // Check if an approval status is provided
        if (isset($_POST['approval_status'])) {
            $approval_status = $_POST['approval_status'];
            $not_approve_reason = isset($_POST['not_approve_reason']) ? $_POST['not_approve_reason'] : null;
    
            // Update 'approvals' table with the approval status and reason
            $update_approvals_query = "INSERT INTO approvals (user_id, car_id, status, reason) VALUES ('$user_id', '$car_id', '$approval_status', ?) ON DUPLICATE KEY UPDATE status = '$approval_status', reason = ?";
            $stmt = mysqli_prepare($conn, $update_approvals_query);
            mysqli_stmt_bind_param($stmt, 'ss', $not_approve_reason, $not_approve_reason);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
    
            echo "Approval status and reason updated successfully!";
    
            // Display JavaScript alert and redirect
            echo '<script>';
            echo 'alert("Data successfully inserted into repair table! Approval status and reason updated successfully!");';
            echo 'window.location.href = "homemechanic.php";';
            echo '</script>';
        }
    
}
?>