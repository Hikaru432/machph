<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = $_GET['car_id'];

// Fetch mechanic and autoshop details based on car_id
$car_query = "SELECT c.companyid, m.mechanic_id 
              FROM car c 
              JOIN mechanic m ON c.companyid = m.companyid 
              WHERE c.car_id = $car_id LIMIT 1";
$car_result = mysqli_query($conn, $car_query);
if (!$car_result || mysqli_num_rows($car_result) === 0) {
    die('Error: Unable to fetch mechanic and company information.');
}
$car_data = mysqli_fetch_assoc($car_result);
$autoshop_id = $car_data['companyid'];
$mechanic_id = $car_data['mechanic_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responses = $_POST['responses']; // Array of responses for each category

    foreach ($responses as $category => $answers) {
        foreach ($answers as $question => $rating) {
            $query = "INSERT INTO scale (user_id, car_id, mechanic_id, autoshop_id, category, question, rating)
                      VALUES ($user_id, $car_id, $mechanic_id, $autoshop_id, '$category', '$question', $rating)";
            mysqli_query($conn, $query) or die('Error: ' . mysqli_error($conn));
        }
    }

    // Redirect to vehicleuser.php after submission
    header('Location: vehicleuser.php');
    exit();
}

// Survey questions
$categories = [
    'Tangibles' => [
        'Q1' => 'Service staff appearance.',
        'Q2' => 'State of the art equipments in service workshop.',
        'Q3' => 'Overall service workshop appearance.'
    ],
    'Reliability' => [
        'Q1' => 'Ease of arranging appointment schedule.',
        'Q2' => 'Prioritization on appointment customers.',
        'Q3' => 'Addressing customer vehicle concerns and requests.',
        'Q4' => 'Service staff returns personal belongings and other valuable items.'
    ],
    'Responsiveness' => [
        'Q1' => 'Assistant provided by guards or other staffs upon entry.',
        'Q2' => 'Assistant of service executive.',
        'Q3' => 'Service staff courteousness.'
    ],
    'Assurance' => [
        'Q1' => 'Maintenance reminder for customer\'s upcoming service.',
        'Q2' => 'Confirmation and reminder on customer\'s appointment schedule.',
        'Q3' => 'Informing customer when their vehicle is being serviced.',
        'Q4' => 'Returning of used parts to customers.',
        'Q5' => 'All customer concerns and requests were done.'
    ],
    'Empathy' => [
        'Q1' => 'Service staffs understand customer needs.',
        'Q2' => 'Service staffs give individual attention to customers.',
        'Q3' => 'Service staffs apologize when customer requests were not done.',
        'Q4' => 'Service staffs assist all customers in a caring manner.',
        'Q5' => 'Service staffs willingness to help.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Feedback Survey</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #007BFF;
            font-size: 32px;
            margin: 40px 0;
            font-weight: bold;
        }
        h3 {
            color: #343a40;
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        td select {
            padding: 8px;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .container {
            width: 80%;
            max-width: 900px;
            margin: 0 auto;
        }
        nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color:rgb(0, 0, 0);
        }
        .navbar-brand {
            color: white;
        }
        .navbar-brand:hover {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="vehicleuser.php">Vehicle</a>
        </div>
    </nav>

    <div class="container">
        <h2>Service Feedback Survey</h2>
        <form method="POST">
            <input type="hidden" name="mechanic_id" value="<?php echo $mechanic_id; ?>">
            <input type="hidden" name="autoshop_id" value="<?php echo $autoshop_id; ?>">

            <?php foreach ($categories as $category => $questions): ?>
                <h3><?php echo $category; ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Rating (1-5)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $qId => $question): ?>
                            <tr>
                                <td><?php echo $question; ?></td>
                                <td>
                                    <select name="responses[<?php echo $category; ?>][<?php echo $qId; ?>]" required>
                                        <option value="">Select</option>
                                        <option value="1">1 - Extremely Satisfied</option>
                                        <option value="2">2 - Very Satisfied</option>
                                        <option value="3">3 - Satisfied</option>
                                        <option value="4">4 - Not Satisfied</option>
                                        <option value="5">5 - Extremely Not Satisfied</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

            <button type="submit">Submit Survey</button>
        </form>
    </div>
</body>
</html>