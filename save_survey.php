<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session
    $survey_data = [
        'service_quality' => $_POST['service_quality_rating'],
        'vehicle_condition' => $_POST['vehicle_condition_rating'],
        'scope_of_work' => $_POST['scope_of_work_rating'],
        'timeliness' => $_POST['timeliness_rating'],
        'timeline' => $_POST['timeline_rating'],
        'downtime' => $_POST['downtime_rating'],
        'communication' => $_POST['communication_rating'],
        'progress_info' => $_POST['progress_info_rating'],
        'service_team_access' => $_POST['service_team_access_rating']
    ];

    $query = "INSERT INTO survey (user_id, category, rating) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);

    foreach ($survey_data as $category => $rating) {
        $stmt->bind_param('isi', $user_id, $category, $rating);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    header('Location: thank_you.php');
    exit;
}
?>
