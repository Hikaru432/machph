<?php
// Include database connection (adjust as necessary)
include('config.php');

// Check if the mechanic is logged in
session_start();
$mechanic_id = $_SESSION['mechanic_id']; // Assuming mechanic_id is stored in the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $user_id = $_GET['user_id']; // Assuming user_id is passed in the URL or can be obtained dynamically
    $car_id = $_GET['car_id']; // Assuming car_id is passed in the URL or can be obtained dynamically
    $sender = 'mechanic'; // Sender is automatically set to 'mechanic'

    // Insert message into the usermechanicchat table
    $query = "INSERT INTO usermechanicchat (user_id, mechanic_id, car_id, message, sender, timestamp) 
              VALUES ('$user_id', '$mechanic_id', '$car_id', '$message', '$sender', NOW())";
    
    if (mysqli_query($conn, $query)) {
        // Redirect to the same page to avoid re-submitting the form
        header("Location: chatmechanic.php?user_id=$user_id&car_id=$car_id");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch chat history
$user_id = $_GET['user_id']; // Assuming user_id is passed in the URL
$car_id = $_GET['car_id']; // Assuming car_id is passed in the URL
$query = "SELECT * FROM usermechanicchat WHERE user_id = '$user_id' AND car_id = '$car_id' ORDER BY timestamp ASC";
$chats = mysqli_query($conn, $query);
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
            padding: 12px 20px;
        }
        .navbar-brand {
            color: #ffffff;
            font-size: 1.5rem;
        }
        .navbar-brand:hover {
            color: #0d6efd;
        }

        /* Chat box container */
        .chat-box {
            width: 100%;
            max-width: 720px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border: 1px solid #e0e0e0;
        }

        /* Chat history container */
        .chat-history {
            height: 450px;
            overflow-y: auto;
            padding: 15px;
            background-color: #f7f7f7;
            border-radius: 12px;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        /* User and mechanic message bubbles */
        .user-message {
            max-width: 75%;
            background-color: #007bff; /* Blue for user */
            color: white;
            padding: 12px 16px;
            border-radius: 20px;
            margin: 8px 0;
            align-self: flex-start;
            word-wrap: break-word;
        }
        .user-message::after {
            content: '';
            position: absolute;
            top: 50%;
            left: -8px;
            width: 0;
            height: 0;
            border-right: 12px solid #007bff;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            transform: translateY(-50%);
        }

        .mechanic-message {
            max-width: 75%;
            background-color: #28a745; /* Green for mechanic */
            color: white;
            padding: 12px 16px;
            border-radius: 20px;
            margin: 8px 0;
            align-self: flex-end;
            word-wrap: break-word;
        }
        .mechanic-message::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -8px;
            width: 0;
            height: 0;
            border-left: 12px solid #28a745;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            transform: translateY(-50%);
        }

        /* Timestamp */
        .timestamp {
            font-size: 0.75rem;
            color: #aaa;
            margin-top: 6px;
        }

        /* Form and input styling */
        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            border: 1px solid #ddd;
            resize: none;
            font-size: 1rem;
            margin-bottom: 15px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        button:hover {
            background-color: #0056b3;
        }

        /* Back button */
        .back-btn {
            text-align: center;
            margin-top: 20px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .chat-box {
                margin: 10px;
                padding: 15px;
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
    <h4 class="text-center mb-4">Chat to User</h4>

    <div class="chat-history">
        <?php while ($chat = mysqli_fetch_assoc($chats)): ?>
            <div class="<?php echo ($chat['sender'] == 'mechanic') ? 'mechanic-message' : 'user-message'; ?>">
                <p class="mb-0"><?php echo htmlspecialchars($chat['message']); ?></p>
                <small class="timestamp"><?php echo date('F j, Y, g:i a', strtotime($chat['timestamp'])); ?></small>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        // Function to reload the chat every 3 seconds
        setInterval(function() {
            var chatHistory = document.getElementById('chat-history');
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_chats.php', true);  // Make sure this script fetches the updated chat history
            xhr.onload = function() {
                if (xhr.status == 200) {
                    chatHistory.innerHTML = xhr.responseText;  // Update the chat history
                }
            };
            xhr.send();
        }, 3000);  // Reload every 3 seconds
    </script>

    <form method="POST">
        <div class="form-group">
            <textarea name="message" class="form-control" placeholder="Type your message..." rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-3">Send</button>
    </form>
</div>

<div class="back-btn">
    <a href="javascript:history.back();" class="btn btn-secondary">&larr; Back</a>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></l>
</body>
</html>
