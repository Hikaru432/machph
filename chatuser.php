<?php
include 'config.php';
session_start();

// Ensure the user is logged in or the mechanic is logged in
if (!isset($_SESSION['user_id']) && !isset($_GET['mechanic_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Get car_id from URL
$car_id = $_GET['car_id'];

// Determine whether it's a user or a mechanic accessing the chat
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $mechanic_id = ''; // Mechanic will be fetched based on car_id
} elseif (isset($_GET['mechanic_id'])) {
    $mechanic_id = $_GET['mechanic_id'];
    $user_id = $_GET['user_id']; // User is passed from the URL
}

// Fetch mechanic_id based on car_id if not set for user
if ($mechanic_id == '' && isset($user_id)) {
    // Get the mechanic assigned to the car
    $query = "SELECT mechanic_id FROM accomplishtask WHERE car_id = $car_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $mechanic_id = $row['mechanic_id'];
    }
}

// Check if the car_id exists in the 'car' table
$carCheckQuery = "SELECT * FROM car WHERE car_id = $car_id";
$carCheckResult = mysqli_query($conn, $carCheckQuery);

if (mysqli_num_rows($carCheckResult) == 0) {
    echo "Error: Invalid car ID!";
    exit;  // Exit if the car_id is not found
}

// Ensure mechanic_id exists before inserting the chat
if ($mechanic_id == '') {
    echo "Error: No mechanic assigned to this car!";
    exit;
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = mysqli_real_escape_string($conn, $_POST['message']); // Prevent SQL injection

    // Identify the sender (user or mechanic)
    $sender = (isset($_SESSION['user_id'])) ? 'user' : 'mechanic';

    // Insert the message into the usermechanicchat table
    $insertChatQuery = "INSERT INTO usermechanicchat (user_id, mechanic_id, car_id, message, sender, timestamp) 
                        VALUES ('$user_id', '$mechanic_id', '$car_id', '$message', '$sender', NOW())";

    if (mysqli_query($conn, $insertChatQuery)) {
        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch the chat history
$fetchChatsQuery = "SELECT * FROM usermechanicchat WHERE car_id = $car_id ORDER BY timestamp ASC";
$chats = mysqli_query($conn, $fetchChatsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Mechanic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sticky top navigation bar */
        nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color:rgb(0, 0, 0);
            padding: 10px 20px;
        }
        .navbar-brand {
            color: white;
            font-size: 1.25rem;
        }
        .navbar-brand:hover {
            color: #0d6efd;
        }

        /* Chat box container */
        .chat-box {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Chat history */
        .chat-history {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #ffffff;
        }

        /* User and mechanic messages */
        .user-message {
            background-color: #d1e7dd;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #b6d1b3;
            text-align: right;
            margin: 5px 0;
        }
        .mechanic-message {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #f5c6cb;
            text-align: left;
            margin: 5px 0;
        }

        /* Chat input and send button */
        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }

        /* Timestamp */
        .timestamp {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .chat-box {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="vehicleuser.php">Vehicle</a>
        </div>
    </nav>

<div class="container chat-box">
    <h4 class="text-center mb-4">Chat with Mechanic</h4>

    <div class="chat-history">
        <?php while ($chat = mysqli_fetch_assoc($chats)): ?>
            <div class="<?php echo ($chat['sender'] == 'user') ? 'user-message' : 'mechanic-message'; ?>">
                <p class="mb-0"> <?php echo htmlspecialchars($chat['message']); ?> </p>
                <small class="timestamp"> <?php echo date('F j, Y, g:i a', strtotime($chat['timestamp'])); ?> </small>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="POST">
        <div class="form-group">
            <textarea name="message" class="form-control" placeholder="Type your message..." rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-3">Send</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
