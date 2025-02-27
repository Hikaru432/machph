    <?php
    session_start();

    // Redirect to login.php if the user is not logged in
    if (!isset($_SESSION['user_id'])) {
        header('location:login.php');
        exit();
    }

    // Redirect to login.php after logout
    if (isset($_GET['logout'])) {
        unset($_SESSION['user_id']);
        session_destroy();
        header('location:login.php');
        exit();
    }

    include 'config.php';

    $user_id = $_SESSION['user_id'];
    $companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : null;

    // Perform the modified query to include the manufacturer's name
    $car_select = mysqli_query($conn, "SELECT car.*, manufacturer.name AS manuname FROM car LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id WHERE car.user_id = '$user_id'");

    // Check if the query was successful
    if (!$car_select) {
        die('Error in car query: ' . mysqli_error($conn));
    }

    // Retrieve the companyname parameter
    if(isset($_GET['companyname'])){
        $companyname = $_GET['companyname'];
    }else{
        // Handle the case where companyname is not provided
        // For example, redirect to a different page or show an error message
    }

    // Perform the query to fetch additional information about the selected card based on companyname
    $company_select = mysqli_query($conn, "SELECT * FROM autoshop WHERE companyname = '$companyname'");

    // Check if the query was successful
    if (!$company_select) {
        die('Error in company query: ' . mysqli_error($conn));
    }

    // Fetch the additional information about the selected card
    $company_info = mysqli_fetch_assoc($company_select);

    // Add this after the car_select query
    $notification_query = "SELECT a.*, r.user_id, r.companyid, au.companyname 
                          FROM action a 
                          JOIN requestproduct r ON a.request_id = r.id 
                          JOIN autoshop au ON r.companyid = au.companyid 
                          WHERE r.user_id = '$user_id' 
                          ORDER BY a.created_at DESC";
    $notification_result = mysqli_query($conn, $notification_query);

    // Get unread notification count
    $unread_count = mysqli_num_rows($notification_result);

    // Fetch the company information based on the companyname
    $companyname = isset($_SESSION['companyname']) ? $_SESSION['companyname'] : null;

    if ($companyname) {
        $company_query = "SELECT companyid FROM autoshop WHERE companyname = ?";
        $company_stmt = mysqli_prepare($conn, $company_query);
        mysqli_stmt_bind_param($company_stmt, "s", $companyname);
        mysqli_stmt_execute($company_stmt);
        $company_result = mysqli_stmt_get_result($company_stmt);
        
        if ($company_row = mysqli_fetch_assoc($company_result)) {
            $session_companyid = $company_row['companyid']; // Now we have the companyid
        } else {
            // Handle the case where the company is not found
            $session_companyid = null; // or set a default value
        }
    } else {
        // Handle the case where companyname is not set
        $session_companyid = null; // or set a default value
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MachPH Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #b30036;
                --primary-hover: #8b002a;
                --secondary-color: #f8f9fa;
                --text-dark: #2c3e50;
                --text-light: #ffffff;
                --border-color: #e9ecef;
                --card-shadow: 0 2px 10px rgba(179, 0, 54, 0.1);
                --hover-shadow: 0 4px 15px rgba(179, 0, 54, 0.15);
            }

            body {
                background-color: var(--secondary-color);
                color: var(--text-dark);
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                line-height: 1.6;
            }

            /* Enhanced Navbar */
            .navbar {
                background: linear-gradient(135deg, #b30036 0%, #8b002a 100%) !important;
                box-shadow: var(--card-shadow);
                padding: 1rem 0;
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .navbar-brand {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--text-light) !important;
                letter-spacing: 0.5px;
            }

            .nav-link {
                color: var(--text-light) !important;
                font-weight: 500;
                padding: 0.5rem 1.2rem;
                border-radius: 25px;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.1);
                transform: translateY(-1px);
            }

            /* Refined Welcome Section Style */
            .welcome-section {
                background: linear-gradient(180deg, #000000 0%, #f8f9fa 100%);
                padding: 3.5rem 0;
                margin-bottom: 3rem;
                /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
                position: relative;
                overflow: hidden;
            }

            .welcome-section h1 {
                font-weight: 800;
                margin-bottom: 1.5rem;
                font-size: 2.75rem;
                color: #ffffff;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            }

            .welcome-section .btn-custom {
                background: #000000;
                color: #ffffff;
                border: none;
                padding: 0.8rem 2rem;
                border-radius: 0;
                font-weight: 600;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 0.9rem;
            }

            .welcome-section .btn-custom:hover {
                background: #333333;
                transform: translateY(-2px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }

            /* Enhanced Card Design */
            .card {
                border: none;
                border-radius: 15px;
                box-shadow: var(--card-shadow);
                transition: all 0.3s ease;
                background: var(--text-light);
                overflow: hidden;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: var(--hover-shadow);
            }

            .card-header {
                background-color: var(--text-light);
                border-bottom: 2px solid var(--border-color);
                padding: 1.5rem;
            }

            .card h2 {
                color: var(--primary-color);
                font-weight: 700;
                font-size: 1.75rem;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            /* Enhanced Table Design */
            .table-responsive {
                border-radius: 0 0 15px 15px;
                overflow: hidden;
            }

            .table {
                margin: 0;
                border-collapse: separate;
                border-spacing: 0;
            }

            .table th {
                background: linear-gradient(135deg, #b30036 0%, #8b002a 100%) !important;
                color: var(--text-light);
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.9rem;
                letter-spacing: 0.5px;
                padding: 1.2rem 1rem;
                border: none;
            }

            .table td {
                padding: 1.2rem 1rem;
                vertical-align: middle;
                border-color: var(--border-color);
                font-size: 0.95rem;
                transition: all 0.3s ease;
            }

            .table tbody tr {
                transition: all 0.3s ease;
            }

            .table tbody tr:hover {
                background-color: rgba(179, 0, 54, 0.05);
            }

            /* Enhanced Button Styles */
            .btn-custom {
                background: linear-gradient(135deg, #b30036 0%, #8b002a 100%);
                color: var(--text-light);
                padding: 0.8rem 2rem;
                border-radius: 25px;
                font-weight: 600;
                border: none;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 0.9rem;
            }

            .btn-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(179, 0, 54, 0.2);
                background: linear-gradient(135deg, #8b002a 0%, #b30036 100%);
                color: var(--text-light);
            }

            .btn-sm.btn-custom {
                padding: 0.5rem 1.5rem;
                font-size: 0.875rem;
            }

            /* Empty State Enhancement */
            .empty-state {
                padding: 4rem 2rem;
                text-align: center;
                color: var(--text-dark);
            }

            .empty-state i {
                font-size: 3.5rem;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
                opacity: 0.8;
            }

            /* Animation Refinements */
            .fade-in {
                animation: fadeIn 0.6s ease-out;
            }

            @keyframes fadeIn {
                from { 
                    opacity: 0; 
                    transform: translateY(20px); 
                }
                to { 
                    opacity: 1; 
                    transform: translateY(0); 
                }
            }

            /* Responsive Improvements */
            @media (max-width: 768px) {
                .welcome-section {
                    padding: 2.5rem 0;
                }

                .welcome-section h1 {
                    font-size: 2rem;
                }

                .card {
                    margin: 1rem;
                }

                .table-responsive {
                    margin: 0 -1rem;
                }

                .btn-custom {
                    padding: 0.6rem 1.5rem;
                }
            }

            /* Additional Enhancements */
            .container {
                max-width: 1200px;
                padding: 0 1rem;
            }

            .table td:first-child,
            .table th:first-child {
                padding-left: 1.5rem;
            }

            .table td:last-child,
            .table th:last-child {
                padding-right: 1.5rem;
            }
        </style>
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="home.php">MachPH</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="home.php">Dashboard</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                                Notification
                                <?php if($unread_count > 0) {
                                    echo " <span class='badge bg-danger'>$unread_count</span>";
                                }
                                ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <?php while($notification = mysqli_fetch_assoc($notification_result)) : ?>
                                <li>
                                    <a class="dropdown-item" href="notification_details.php?action_id=<?php echo $notification['id']; ?>">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo $notification['companyname']; ?></strong>
                                            <small class="text-muted"><?php echo date('M d', strtotime($notification['created_at'])); ?></small>
                                        </div>
                                        <div><?php echo $notification['action_type']; ?></div>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                                <?php if($unread_count == 0) : ?>
                                <li><div class="dropdown-item text-muted">No notifications</div></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>  
                    </ul>
                </div>
            </div>
        </nav>

        <section class="welcome-section">
            <div class="container text-center">
                <h1 class="fade-in">Welcome to <?php echo isset($company_info['companyname']) ? $company_info['companyname'] : ''; ?></h1>
                <a href="productshop.php?companyid=<?php echo isset($session_companyid) ? $session_companyid : ''; ?>" class="btn btn-lg btn-custom">Visit Shop</a>
                
                <!-- Message Button -->
                <button type="button" class="btn btn-primary btn-sm" style="position: absolute; top: 20px; right: 20px;" data-bs-toggle="modal" data-bs-target="#chatModal">
                    Messages
                </button>
            </div>
        </section>
 
          <!-- Chat Modal -->
                <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="chatModalLabel">Chat</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <div id="chatbox" style="max-height: 300px; overflow-y: auto;">
                                <!-- Display messages here -->
                                <?php
                                // Assuming you have already started the session and have the user_id in the session
                                $session_user_id = $_SESSION['user_id']; // Get the user_id from the session

                                // Fetch messages for the specific user_id and companyid
                                $message_query = "SELECT * FROM cashier WHERE companyid = ? AND user_id = ? ORDER BY date ASC";
                                $message_stmt = mysqli_prepare($conn, $message_query);
                                mysqli_stmt_bind_param($message_stmt, "ii", $session_companyid, $session_user_id); // Use session variables
                                mysqli_stmt_execute($message_stmt);
                                $message_result = mysqli_stmt_get_result($message_stmt);

                                while ($mess    age = mysqli_fetch_assoc($message_result)) {
                                    // Determine the alignment based on the role 
                                    $alignment = htmlspecialchars($message['role']) === 'cashier' ? 'text-left' : 'text-right';
                                    echo '<div class="' . $alignment . '"><strong>' . htmlspecialchars($message['role']) . ':</strong> ' . htmlspecialchars($message['message']) . '</div>';
                                }
                                ?>
                            </div>
                                <div class="input-group mt-2">
                                    <input type="text" class="form-control" name="message" id="messageInput" placeholder="Type your message here...">
                                    <button class="btn btn-primary" type="button" id="sendMessage">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <div class="container">
            <div class="card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Your Vehicles</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Manufacturer</th>   
                                <th>Model</th>
                                <th>Plate No</th>
                                <th>Color</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($car_select)) : ?>
                            <tr>
                                <td><?php echo $row['manuname'] ?? ''; ?></td>
                                <td><?php echo $row['carmodel'] ?? ''; ?></td>
                                <td><?php echo $row['plateno'] ?? ''; ?></td>
                                <td><?php echo $row['color'] ?? ''; ?></td>
                                <td>
                                    <a href="carprofile.php?car_id=<?php echo $row['car_id']; ?>&companyname=<?php echo $company_info['companyname']; ?>" 
                                    class="btn btn-sm btn-custom">View Profile</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle sending messages
                document.getElementById('sendMessage').addEventListener('click', function() {
                    const message = document.getElementById('messageInput').value;
                    const role = 'user'; // Set the role to 'user' for the current user

                    // Send the message using fetch
                    fetch('save_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message: message, requestId: <?php echo $requestId; ?>, role: role })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('chatbox').innerHTML += `<div class="text-right"><strong>${role}:</strong> ${message}</div>`;
                            document.getElementById('messageInput').value = ''; // Clear input
                        } else {
                            alert('Error: ' + data.error);
                        }
                    });
                });
            });
        </script>
    </body>

    </html>
