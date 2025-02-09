<?php
session_start();
include 'config.php';

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

if (isset($_POST['user_id']) && isset($_POST['car_id']) && isset($_POST['progress']) && isset($_POST['progressing']) && isset($_POST['mechanic_id']) && isset($_POST['progressingpercentage']) && isset($_POST['nameprogress'])) {
    $userId = $_POST['user_id'];
    $carId = $_POST['car_id'];
    $progress = $_POST['progress'];
    $progressing = $_POST['progressing']; 
    $mechanicId = $_POST['mechanic_id'];
    $progressingPercentage = $_POST['progressingpercentage'];
    $nameProgress = $_POST['nameprogress'];
    
    // Get current Philippine time
    $currentDateTime = date('Y-m-d H:i:s');

    // Check if the record exists
    $checkQuery = "SELECT progressing, progress_percentage FROM accomplishtask 
                  WHERE user_id = ? AND car_id = ? AND mechanic_id = ? AND nameprogress = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'iiis', $userId, $carId, $mechanicId, $nameProgress);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existingProgressing, $existingProgressPercentage);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_fetch($stmt);

        if (strpos($existingProgressing, $progressing) === false) {
            $newProgressing = $existingProgressing . ', ' . $progressing;

            // Update with current Philippine time
            $updateQuery = "UPDATE accomplishtask 
                          SET progress_percentage = ?, 
                              progressing = ?, 
                              progressingpercentage = ?, 
                              progress_date = ?
                          WHERE user_id = ? AND car_id = ? AND mechanic_id = ? AND nameprogress = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'isssiiis', 
                $progress, 
                $newProgressing, 
                $progressingPercentage, 
                $currentDateTime,
                $userId, 
                $carId, 
                $mechanicId, 
                $nameProgress
            );
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);

            echo "Progress updated successfully for category: " . htmlspecialchars($nameProgress);

            if ($progress === 90) {
                // Get the diagnosis from the diagnose table
                $diagnosisQuery = "SELECT diagnosis FROM diagnosetable WHERE user_id = ? AND plateno = ?";
                $diagnosisStmt = mysqli_prepare($conn, $diagnosisQuery);
                mysqli_stmt_bind_param($diagnosisStmt, 'is', $userId, $carId);
                mysqli_stmt_execute($diagnosisStmt);
                mysqli_stmt_bind_result($diagnosisStmt, $diagnosis);
                mysqli_stmt_fetch($diagnosisStmt);
                mysqli_stmt_close($diagnosisStmt);

                // Insert into history table with timestamp
                $historyQuery = "INSERT INTO history (user_id, car_id, mechanic_id, diagnosis, progress_date) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                $historyStmt = mysqli_prepare($conn, $historyQuery);
                mysqli_stmt_bind_param($historyStmt, 'iiis', $userId, $carId, $mechanicId, $diagnosis);
                mysqli_stmt_execute($historyStmt);
                
                if (mysqli_stmt_affected_rows($historyStmt) > 0) {
                    echo "History record created successfully.";
                } else {
                    echo "Error creating history record.";
                }

                mysqli_stmt_close($historyStmt);

                // Delete from service table
                $deleteServiceQuery = "DELETE FROM service WHERE user_id = ? AND car_id = ?";
                $deleteServiceStmt = mysqli_prepare($conn, $deleteServiceQuery);
                mysqli_stmt_bind_param($deleteServiceStmt, 'ii', $userId, $carId);
                mysqli_stmt_execute($deleteServiceStmt);
                
                if (mysqli_stmt_affected_rows($deleteServiceStmt) > 0) {
                    echo "Service record deleted successfully.";
                } else {
                    echo "No service record found to delete.";
                }

                mysqli_stmt_close($deleteServiceStmt);
            }
        } else {
            echo "This progress for " . htmlspecialchars($progressing) . " already exists in category: " . htmlspecialchars($nameProgress);
        }
    } else {
        // Insert new record with current Philippine time
        $insertQuery = "INSERT INTO accomplishtask 
                       (user_id, car_id, progress_percentage, progressing, mechanic_id, 
                        progressingpercentage, nameprogress, progress_date) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, 'iiississ', 
            $userId, 
            $carId, 
            $progress, 
            $progressing, 
            $mechanicId, 
            $progressingPercentage, 
            $nameProgress,
            $currentDateTime
        );
        mysqli_stmt_execute($insertStmt);

        if (mysqli_stmt_affected_rows($insertStmt) > 0) {
            echo "Progress saved successfully for category: " . htmlspecialchars($nameProgress);
        } else {
            echo "Error saving progress for category: " . htmlspecialchars($nameProgress);
        }

        mysqli_stmt_close($insertStmt);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Incomplete data received.";
}

// Ensure all progress percentages are checked to accurately reflect completion
if ($progress === 90) {
    // Update all related records to mark them as completed with new timestamp
    $completeQuery = "UPDATE accomplishtask SET progress_percentage = 90, progress_date = CURRENT_TIMESTAMP WHERE user_id = ? AND car_id = ? AND mechanic_id = ?";
    $completeStmt = mysqli_prepare($conn, $completeQuery);
    mysqli_stmt_bind_param($completeStmt, 'iii', $userId, $carId, $mechanicId);
    mysqli_stmt_execute($completeStmt);
    
    if (mysqli_stmt_affected_rows($completeStmt) > 0) {
        echo "All related tasks marked as completed.";
    } else {
        echo "No related tasks found to mark as completed.";
    }

    mysqli_stmt_close($completeStmt);
}

// Close database connection
mysqli_close($conn);
?>