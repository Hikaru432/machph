<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['companyid']) || empty($_SESSION['companyid'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form
    $mechanic_id = $_POST['mechanic_id'];
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $approval_status = $_POST['approval_status'];
    $not_approve_reason = isset($_POST['not_approve_reason']) ? $_POST['not_approve_reason'] : null;

    $all_inserts_successful = true; // Flag to check if all inserts are successful

    // Function to handle inserts
    function insertRepair($conn, $mechanic_id, $user_id, $car_id, $problem, $diagnosis) {
        $insert_query = "INSERT INTO diagnose (mechanic_id, user_id, plateno, problem, diagnosis) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iisss', $mechanic_id, $user_id, $car_id, $problem, $diagnosis);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    // Arrays for problems and their respective titles
    $problem_categories = [
        'engine_overhaul_problems' => 'Engine Overhaul',
        'engine_low_power_problems' => 'Engine Low Power',
        'electrical_problems' => 'Electrical Problem',
        'battery_problems' => 'Battery',
        'light_problems' => 'Light',
        'oil_problems' => 'Oil',
        'water_problems' => 'Water',
        'brake_problems' => 'Brake',
        'air_problems' => 'Air',
        'gas_problems' => 'Gas',
        'tire_problems' => 'Tire',
    ];

    // Insert all problems
    foreach ($problem_categories as $post_key => $problem_title) {
        $problems = isset($_POST[$post_key]) ? $_POST[$post_key] : [];
        foreach ($problems as $problem) {
            if (!insertRepair($conn, $mechanic_id, $user_id, $car_id, $problem_title, $problem)) {
                $all_inserts_successful = false; // Mark as false if any insert fails
            }
        }
    }

    // Approval handling
    if ($approval_status === '1' || $approval_status === '0') {
        $update_approvals_query = "INSERT INTO approvals (mechanic_id, user_id, car_id, status, reason) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), reason = VALUES(reason)";
        $stmt = mysqli_prepare($conn, $update_approvals_query);
        mysqli_stmt_bind_param($stmt, 'iiiss', $mechanic_id, $user_id, $car_id, $approval_status, $not_approve_reason);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error: " . mysqli_error($conn);
            $all_inserts_successful = false; // Mark as false if update fails
        }
        mysqli_stmt_close($stmt);
    }

    // Check if all operations were successful
    if ($all_inserts_successful) {
        // Set session variables
        $_SESSION['mechanic_id'] = $mechanic_id; // Save mechanic ID for the redirect
        $_SESSION['success_message'] = "Data successfully inserted into repair table! Approval status and reason updated successfully!";
        
        // Redirect to homemechanic.php
        header('Location: homemechanic.php?mechanic_id=' . $mechanic_id);
        exit();
    } else {
        echo "There was an error processing your request.";
    }
}
?>
