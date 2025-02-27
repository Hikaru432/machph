<?php
session_start();
include 'config.php';

if(!isset($_SESSION['companyid'])){
    header('location:clogin.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Handle form submissions
if(isset($_POST['confirm_action'])) {
    $request_id = $_POST['request_id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    
    // Handle image upload
    $image = null;
    if(isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }
    
    $comments = $_POST['comments'];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO action (request_id, action_type, product_name, quantity, image, comments) VALUES (?, 'confirm', ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isiss", $request_id, $product_name, $quantity, $image, $comments);
    mysqli_stmt_execute($stmt);
    
    // Update request status
    mysqli_query($conn, "UPDATE requestproduct SET request_type = 'Confirmed' WHERE id = $request_id");
}

if(isset($_POST['pending_action'])) {
    $request_id = $_POST['request_id'];
    $message = isset($_POST['message']) ? $_POST['message'] : ''; // Ensure message is set
    $companyid = $_POST['companyid']; // Get the company ID from the hidden input
    $user_id = $_POST['user_id']; // Get the user ID from the hidden input

    // Insert the message into the cashier table
    $role = 'cashier'; // Set the role in your application code
    $stmt = mysqli_prepare($conn, "INSERT INTO cashier (role, message, companyid, user_id, done_action) VALUES (?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "ssii", $role, $message, $companyid, $user_id);
    
    // Check if message is not empty before executing
    if (!empty($message)) {
        mysqli_stmt_execute($stmt); // This will be an array
    } else {
        // // Handle the case where the message is empty (optional)
        // echo "Message cannot be empty.";
    }

    // Update done_action to 'done' when proceeding
    mysqli_query($conn, "UPDATE cashier SET done_action = 'done' WHERE user_id = $user_id AND companyid = $companyid AND done_action = 'pending'");

    // Update the action table based on the done_action status
    $action_status = 'Pending'; // Default status
    $result = mysqli_query($conn, "SELECT done_action FROM cashier WHERE user_id = $user_id AND companyid = $companyid ORDER BY date DESC LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['done_action'] === 'done') {
            $action_status = 'Done'; // Change status to Done if done_action is done
        }
    }

    // Update the action table with the new status
    mysqli_query($conn, "UPDATE action SET action_type = '$action_status' WHERE request_id = $request_id");
}

// Get all unique request types for the filter
$request_types_query = "SELECT DISTINCT request_type FROM requestproduct WHERE companyid = ?";
$stmt = mysqli_prepare($conn, $request_types_query);
mysqli_stmt_bind_param($stmt, "i", $companyid);
mysqli_stmt_execute($stmt);
$request_types_result = mysqli_stmt_get_result($stmt);
$request_types = [];
while($row = mysqli_fetch_assoc($request_types_result)) {
    if($row['request_type']) {
        $request_types[] = $row['request_type'];
    }
}

if(isset($_POST['delete_action'])) {
    $request_id = $_POST['request_id'];
    $delete_query = "DELETE FROM requestproduct WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $request_id);
    mysqli_stmt_execute($delete_stmt);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        .table {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .btn-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-status:hover {
            transform: translateY(-1px);
        }

        .request-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-denied {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .modal-body {
            padding: 20px;
        }
        .reason-list {
            list-style: none;
            padding: 0;
        }
        .reason-list li {
            margin-bottom: 10px;
        }
        .action-item {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .action-item:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .action-details {
            font-size: 0.95rem;
        }

        .action-details-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .action-details-placeholder {
            padding: 3rem 2rem;
            text-align: center;
            color: #6c757d;
        }

        .action-details-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .action-details-content {
            padding: 0;
        }

        .action-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .action-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .action-status.confirm {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .action-status.pending {
            background: #fff8e1;
            color: #f57f17;
        }

        .action-status.denied {
            background: #ffebee;
            color: #c62828;
        }

        .action-body {
            padding: 1.5rem;
        }

        .detail-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #eee;
        }

        .detail-section h4 {
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 500;
            color: #2c3e50;
        }

        .action-image {
            width: 100%;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .action-footer {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .timestamp {
            color: #6c757d;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .image-wrapper {
            position: relative;
            width: 100%;
            height: 150px; /* Reduced height */
            overflow: hidden;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .action-image {
            width: 100%;
            height: 100%;
            object-fit: contain; /* This will maintain aspect ratio */
            object-position: center;
            transition: transform 0.3s ease;
        }

        /* Optional: Add hover zoom effect */
        .image-wrapper:hover .action-image {
            transform: scale(1.05);
        }

        /* Add these new styles */
        .company-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .company-info i {
            color: #0d6efd;
            font-size: 1.2rem;
        }

        .company-name {
            font-weight: 500;
            color: #2c3e50;
        }

        .comments-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .comment-item {
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .company-comment-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #0d6efd;
        }

        .company-comment-name i {
            font-size: 0.9rem;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .comment-content {
            color: #2c3e50;
            line-height: 1.5;
        }

        .btn-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #000;
            cursor: pointer;
        }

        .btn-close:hover {
            color: #ff0000;
        }

        .message {
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 5px;
            max-width: 70%;
            clear: both;
            position: relative;
        }

        .cashier-message {
            background-color: #007bff; /* Blue for cashier */
            color: white;
            float: right; /* Align to the right */
        }

        .user-message {
            background-color: #e9ecef; /* Light gray for user */
            color: black;
            float: left; /* Align to the left */
        }

        .message {
                padding: 8px 12px;
                border-radius: 10px;
                margin-bottom: 5px;
                max-width: 70%;
                clear: both;
                position: relative;
            }

            .cashier-message {
                background-color: #007bff; /* Blue for cashier */
                color: white;
                float: right; /* Align to the right */
            }

            .user-message {
                background-color: #e9ecef; /* Light gray for user */
                color: black;
                float: left; /* Align to the left */
            }

            .input-group {
                display: flex;
                align-items: center;
            }

            .input-group .form-control {
                flex: 1;
            }

            .input-group .btn {
                margin-left: 5px;
            }

        .chat-box {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            height: 200px; /* Increased height for better visibility */
            overflow-y: auto;
            background-color: #f8f9fa; /* Light background for the chat box */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        .message-container {
            max-height: 150px; /* Adjusted height for better message display */
            overflow-y: auto;
            display: flex;
            flex-direction: column; /* Stack messages vertically */
            gap: 5px; /* Space between messages */
        }

        .message {
            padding: 10px 15px;
            border-radius: 15px; /* More rounded corners */
            margin-bottom: 5px;
            max-width: 80%; /* Increased max-width for better message visibility */
            clear: both;
            position: relative;
            font-size: 0.9rem; /* Slightly smaller font size */
            line-height: 1.4; /* Improved line height for readability */
        }

        .cashier-message {
            background-color: #007bff; /* Blue for cashier */
            color: white;
            align-self: flex-end; /* Align to the right */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); /* Shadow for depth */
        }

        .user-message {
            background-color: #e9ecef; /* Light gray for user */
            color: black;
            align-self: flex-start; /* Align to the left */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); /* Shadow for depth */
        }

        .form-control {
            border-radius: 20px; /* Rounded input field */
            border: 1px solid #ced4da; /* Border color */
            transition: border-color 0.3s; /* Smooth transition for focus */
        }

        .form-control:focus {
            border-color: #007bff; /* Change border color on focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add shadow on focus */
        }

        /* Add modern styles for the table */
        .table {
            border-collapse: collapse; /* Ensure borders are collapsed */
            width: 100%; /* Full width */
            margin-bottom: 1rem; /* Space below the table */
            color: #212529; /* Text color */
            background-color: #fff; /* Background color */
            border-radius: 0.5rem; /* Rounded corners */
            overflow: hidden; /* Prevent overflow */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .table th, .table td {
            padding: 1rem; /* Padding for cells */
            text-align: left; /* Align text to the left */
            border-bottom: 1px solid #dee2e6; /* Bottom border */
        }

        .table th {
            background-color: #f8f9fa; /* Header background color */
            font-weight: bold; /* Bold text */
            color: #495057; /* Header text color */
        }

        .table tr:hover {
            background-color: #f1f1f1; /* Row hover effect */
        }

        .table td {
            transition: background-color 0.3s; /* Smooth transition for background color */
        }

        .table td:last-child {
            border: none; /* Remove border for last cell */
        }

        .btn {
            transition: background-color 0.3s, color 0.3s; /* Smooth transition for buttons */
        }

        .btn:hover {
            background-color: #0056b3; /* Darker blue on hover */
            color: white; /* White text on hover */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-black">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">Admin</a>
            <!-- Add your existing navigation items here -->
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Left Column - Request Table -->
            <div class="col-lg-8">
                <h2 class="mb-4">Requests List</h2>
                
                <!-- Filter dropdown
                <div class="mb-3">
                    <select class="form-select w-auto" id="requestTypeFilter">
                        <option value="">All Requests</option>
                        <?php foreach($request_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div> -->

                <div class="table-responsive" style="margin-top: 200px;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Image</th>
                                <th>Comments</th>
                                <th>Request Type</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, u.firstname, u.lastname 
                                     FROM requestproduct r 
                                     JOIN user u ON r.user_id = u.id 
                                     WHERE r.companyid = ?
                                     ORDER BY r.timestamp DESC";
                            
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "i", $companyid);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            while($row = mysqli_fetch_assoc($result)) {
                                // Table row
                                echo "<tr class='request-row' data-request-type='{$row['request_type']}' data-request-id='{$row['id']}'>";
                                echo "<td>{$row['firstname']} {$row['lastname']}</td>";
                                echo "<td>{$row['name']}</td>";
                                echo "<td>{$row['quantity']}</td>";
                                echo "<td>";
                                if($row['image']) {
                                    echo "<img src='data:image/jpeg;base64," . base64_encode($row['image']) . "' class='request-image' alt='Request Image'>";
                                } else {
                                    echo "No image";
                                }
                                echo "</td>";
                                echo "<td>{$row['comments']}</td>";
                                echo "<td>{$row['request_type']}</td>";
                                echo "<td>" . date('Y-m-d H:i:s', strtotime($row['timestamp'])) . "</td>";
                                echo "<td class='d-flex gap-2'>";
                                // Fetch the action for this request
                                $action_query = "SELECT action_type FROM action WHERE request_id = ? ORDER BY created_at DESC LIMIT 1";
                                $stmt = mysqli_prepare($conn, $action_query);
                                mysqli_stmt_bind_param($stmt, "i", $row['id']);
                                mysqli_stmt_execute($stmt);
                                $action_result = mysqli_stmt_get_result($stmt);
                                $action = mysqli_fetch_assoc($action_result);

                                // Get the user_id from the current row
                                $user_id = $row['user_id']; // Retrieve user_id from the requestproduct table

                                // Check if there are any messages in the cashier table
                                $cashier_query = "SELECT done_action FROM cashier WHERE user_id = ? AND companyid = ? ORDER BY date DESC LIMIT 1";
                                $cashier_stmt = mysqli_prepare($conn, $cashier_query);
                                mysqli_stmt_bind_param($cashier_stmt, "ii", $user_id, $companyid);
                                mysqli_stmt_execute($cashier_stmt);
                                $cashier_result = mysqli_stmt_get_result($cashier_stmt);
                                $cashier_action = mysqli_fetch_assoc($cashier_result);

                                // Show the Pending button if there are any messages in the cashier table
                                if ($cashier_action) {
                                    // If done_action is 'done', show Done button
                                    if ($cashier_action['done_action'] === 'done') {
                                        echo "<button class='btn btn-success'>Done</button>";
                                    } else {
                                        // If done_action is 'pending' or any other value, show Pending button
                                        echo "<button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#pendingModal{$row['id']}'>Pending</button>";
                                    }
                                } else {
                                    // If there are no messages in the cashier table, show Pending button
                                    echo "<button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#pendingModal{$row['id']}'>Pending</button>";
                                }
                                echo "</td>";
                                echo "</tr>";

                                // Generate modals for this row
                                ?>
                                        <!-- Pending Modal -->
                                        <div class="modal fade" id="pendingModal<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Set to Pending</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="color: white; text-size: 20px;">x</button>
                                                    </div>
                                                    <form method="POST" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                            <input type="hidden" name="companyid" value="<?php echo $companyid; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                            <?php
                                                            // Fetch product details based on the name from requestproduct
                                                            $product_query = "SELECT * FROM products WHERE item_name = ?";
                                                            $product_stmt = mysqli_prepare($conn, $product_query);
                                                            mysqli_stmt_bind_param($product_stmt, "s", $row['name']);
                                                            mysqli_stmt_execute($product_stmt);
                                                            $product_result = mysqli_stmt_get_result($product_stmt);
                                                            $product = mysqli_fetch_assoc($product_result); 
                                                            ?>

                                                            <?php if ($product): ?>
                                                                <div class="mb-3 text-center">
                                                                    <img src="data:<?php echo $product['product_image_type']; ?>;base64,<?php echo base64_encode($product['product_image']); ?>" class="img-fluid rounded" alt="Product Image">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Product Name</label>
                                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['item_name']); ?>" readonly>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input type="number" class="form-control" name="quantity" value="<?php echo $product['quantity']; ?>" readonly>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Chat</label>
                                                                    <div class="chat-box" style="border: 1px solid #ced4da; border-radius: 5px; padding: 10px; height: 180px; overflow-y: auto; display: flex; flex-direction: column-reverse; background-color: #f8f9fa;">
                                                                        <div class="message-container" id="messageContainer<?php echo $row['id']; ?>" style="flex-grow: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                                                            <?php
                                                                            // Fetch messages from the cashier table
                                                                            $messages_query = "SELECT * FROM cashier WHERE companyid = ? AND user_id = ? ORDER BY date ASC";
                                                                            $messages_stmt = mysqli_prepare($conn, $messages_query);
                                                                            mysqli_stmt_bind_param($messages_stmt, "ii", $companyid, $row['user_id']);
                                                                            mysqli_stmt_execute($messages_stmt);
                                                                            $messages_result = mysqli_stmt_get_result($messages_stmt);

                                                                            while ($message_row = mysqli_fetch_assoc($messages_result)) {
                                                                                $message_class = $message_row['role'] === 'cashier' ? 'cashier-message' : 'user-message';
                                                                                echo "<div class='message {$message_class}'>{$message_row['message']}</div>";
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="input-group mt-2">
                                                                        <input type="text" class="form-control" name="message" id="chatInput<?php echo $row['id']; ?>" placeholder="Type your message here..." onkeypress="sendMessage(event, <?php echo $row['id']; ?>)">
                                                                        <button class="btn btn-primary" type="button" onclick="sendMessageClick(<?php echo $row['id']; ?>)">Send</button>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="text-danger text-center">Product not found.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="delete_action" class="btn btn-danger">Delete</button>
                                                            <button type="submit" name="pending_action" class="btn btn-primary">Proceed</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>     
                        </div>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailsContainer = document.getElementById('actionDetails');
        const savedRequestId = localStorage.getItem('selectedRequestId');

        if (savedRequestId) {
            showActionDetails(savedRequestId);
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-date') || e.target.closest('.add-date')) {
                const button = e.target.classList.contains('add-date') ? e.target : e.target.closest('.add-date');
                const containerId = button.dataset.container;
                const container = document.getElementById(containerId);

                const dateGroup = document.createElement('div');
                dateGroup.className = 'd-flex gap-2 mb-2';
                dateGroup.innerHTML = `
                    <input type="date" class="form-control" name="estimated_dates[]" required>
                    <button type="button" class="btn btn-danger remove-date">
                        <i class="bi bi-trash"></i>
                    </button>
                `;

                container.appendChild(dateGroup);
            }

            if (e.target.classList.contains('remove-date') || e.target.closest('.remove-date')) {
                const button = e.target.classList.contains('remove-date') ? e.target : e.target.closest('.remove-date');
                button.closest('.d-flex').remove();
            }

            if (e.target.classList.contains('close-details') || e.target.closest('.close-details')) {
                detailsContainer.innerHTML = `
                    <div class="action-details-placeholder">
                        <i class="bi bi-arrow-left-circle"></i>
                        <p>Select a request to view details</p>
                    </div>
                `;
                localStorage.removeItem('selectedRequestId');
            }
        });

        function showActionDetails(requestId) {
            detailsContainer.innerHTML = '<div class="p-4 text-center"><div class="spinner-border text-primary" role="status"></div></div>';

            fetch(`get_action_details.php?request_id=${requestId}`)
                .then(response => response.text())
                .then(html => {
                    detailsContainer.innerHTML = `
                        <button class="btn btn-close close-details" aria-label="Close"></button>
                        ${html}
                    `;
                    localStorage.setItem('selectedRequestId', requestId);

                    document.querySelectorAll('.request-row').forEach(row => {
                        row.classList.remove('selected-row');
                        if (row.dataset.requestId == requestId) {
                            row.classList.add('selected-row');
                        }
                    });
                })
                .catch(error => {
                    detailsContainer.innerHTML = '<div class="p-4 text-center text-danger">Error loading details</div>';
                });
        }

        document.querySelectorAll('.request-row').forEach(row => {
            row.addEventListener('click', function() {
                showActionDetails(this.dataset.requestId);
            });
        });
    });
</script>

<script>
    // Filter functionality
    document.getElementById('requestTypeFilter').addEventListener('change', function() {
        const selectedType = this.value;
        const rows = document.querySelectorAll('.request-row');
        
        rows.forEach(row => {
            const rowType = row.getAttribute('data-request-type');
            if (!selectedType || rowType === selectedType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-date') || e.target.closest('.add-date')) {
            const button = e.target.classList.contains('add-date') ? e.target : e.target.closest('.add-date');
            const containerId = button.dataset.container;
            const container = document.getElementById(containerId);
            
            const dateGroup = document.createElement('div');
            dateGroup.className = 'd-flex gap-2 mb-2';
            dateGroup.innerHTML = `
                <input type="date" class="form-control" name="estimated_dates[]" required>
                <button type="button" class="btn btn-danger remove-date">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            
            container.appendChild(dateGroup);
        }
        
        if (e.target.classList.contains('remove-date') || e.target.closest('.remove-date')) {
            const button = e.target.classList.contains('remove-date') ? e.target : e.target.closest('.remove-date');
            button.closest('.d-flex').remove();
        }
    });

    function showActionDetails(requestId) {
        const detailsContainer = document.getElementById('actionDetails');
        
        // Add loading state
        detailsContainer.innerHTML = '<div class="p-4 text-center"><div class="spinner-border text-primary" role="status"></div></div>';

        // Fetch action details
        fetch(`get_action_details.php?request_id=${requestId}`)
            .then(response => response.text())
            .then(html => {
                detailsContainer.innerHTML = html;
                
                // Highlight selected row
                document.querySelectorAll('.request-row').forEach(row => {
                    row.classList.remove('selected-row');
                    if(row.dataset.requestId == requestId) {
                        row.classList.add('selected-row');
                    }
                });
            })
            .catch(error => {
                detailsContainer.innerHTML = '<div class="p-4 text-center text-danger">Error loading details</div>';
            });
    }

    // Add click handler to table rows
    document.querySelectorAll('.request-row').forEach(row => {
        row.addEventListener('click', function() {
            showActionDetails(this.dataset.requestId);
        });
    });
</script>

<script>
    function sendMessage(event, requestId) {
        if (event.key === 'Enter') {
            const input = document.getElementById(`chatInput${requestId}`);
            const messageContainer = document.getElementById(`messageContainer${requestId}`);
            const message = input.value.trim();

            if (message) {
                // Append the message to the chat box
                const messageElement = document.createElement('div');
                messageElement.textContent = message;

                // Determine the role and style the message accordingly
                const role = 'cashier'; // Replace with actual role logic
                if (role === 'cashier') {
                    messageElement.className = 'message cashier-message';
                } else {
                    messageElement.className = 'message user-message';
                }

                messageContainer.appendChild(messageElement);

                // Clear the input
                input.value = '';

                // Optionally, you can send the message to the server here
                // For example, using fetch or AJAX to save it in the cashier table
                // fetch('save_message.php', { method: 'POST', body: JSON.stringify({ message, requestId }) });
            }
        }
    }
</script>

<script>
    function sendMessage(event, requestId) {
        if (event.key === 'Enter') {
            sendMessageClick(requestId);
        }
    }

    function sendMessageClick(requestId) {
        const input = document.getElementById(`chatInput${requestId}`);
        const messageContainer = document.getElementById(`messageContainer${requestId}`);
        const message = input.value.trim();

        if (message) {
            // Append the message to the chat box
            const messageElement = document.createElement('div');
            messageElement.textContent = message;

            // Determine the role and style the message accordingly
            const role = 'cashier'; // Replace with actual role logic
            if (role === 'cashier') {
                messageElement.className = 'message cashier-message';
            } else {
                messageElement.className = 'message user-message';
            }

            messageContainer.appendChild(messageElement);

            // Clear the input
            input.value = '';

            // Optionally, you can send the message to the server here
            // For example, using fetch or AJAX to save it in the cashier table
            fetch('save_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message, requestId, role })
            });
        }
    }
</script>
</body>
</html>