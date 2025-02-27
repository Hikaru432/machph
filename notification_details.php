<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

if (!isset($_GET['action_id'])) {
    header('location:carusers.php');
    exit();
}

$action_id = $_GET['action_id'];
$user_id = $_SESSION['user_id'];

// Fetch action details with company information and user information
$query = "SELECT a.*, r.user_id, r.companyid, au.companyname,
          CONCAT(u.firstname, ' ', u.lastname) as user_full_name
          FROM action a 
          JOIN requestproduct r ON a.request_id = r.id 
          JOIN autoshop au ON r.companyid = au.companyid 
          JOIN user u ON r.user_id = u.id
          WHERE a.id = ? AND r.user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $action_id, $user_id);
mysqli_stmt_execute($stmt); 
$result = mysqli_stmt_get_result($stmt);
$notification = mysqli_fetch_assoc($result);

if (!$notification) {
    header('location:carusers.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #b30036;
            --primary-hover: #8b002a;
            --secondary-color: #f8f9fa;
            --text-dark: #2c3e50;
            --border-color: #e9ecef;
        }

        body {
            background: linear-gradient(180deg, #f3f4f6 0%, #fff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Enhanced Navbar */
        .navbar {
            background: linear-gradient(135deg, #b30036 0%, #8b002a 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            letter-spacing: 0.5px;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Main Content */
        .page-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .notification-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .notification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .status-header {
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .status-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }

        .company-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .content-section {
            padding: 2rem;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .info-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .date-chip {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }

        .product-image {
            width: 100%;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.02);
        }

        .comments-box {
            background: #fff;
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-top: 1.5rem;
            border-radius: 0 10px 10px 0;
        }

        .timestamp {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .action-footer {
            background: #f8f9fa;
            padding: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-back {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(179, 0, 54, 0.2);
            color: white;
        }

        @media (max-width: 768px) {
            .company-name {
                font-size: 1.5rem;
            }
            
            .status-header {
                padding: 1.5rem;
            }

            .content-section {
                padding: 1.5rem;
            }
        }

        .notification-details {
            display: flex;
            max-width: 1000px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.03);
            position: relative;
        }

        .status-line {
            width: 8px;
            background: #ddd;
        }

        .status-line.confirm { background: #28a745; }
        .status-line.pending { background: #ffc107; }
        .status-line.denied { background: #dc3545; }

        .notification-content {
            flex: 1;
            padding: 0;
        }

        .notification-header {
            padding: 2rem;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .company-info h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .company-info i {
            font-size: 1.8rem;
            color: #6c757d;
        }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .status-pill.confirm {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pill.pending {
            background: #fff8e1;
            color: #f57f17;
        }

        .status-pill.denied {
            background: #ffebee;
            color: #c62828;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .detail-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eee;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .detail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-header i {
            font-size: 1.2rem;
            color: #6c757d;
        }

        .card-content {
            padding: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .value {
            font-weight: 500;
            color: #2c3e50;
        }

        .date-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .date-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .image-container {
            position: relative;
            padding-top: 75%;
            overflow: hidden;
            border-radius: 8px;
        }

        .image-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .image-container:hover img {
            transform: scale(1.05);
        }

        .comment-text {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            white-space: pre-line;
        }

        textarea.form-control {
            border: 1px solid #ced4da;
            border-radius: 8px;
            resize: vertical;
        }

        textarea.form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(179, 0, 54, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .timestamp {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-return {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #2c3e50;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-return:hover {
            background: #1a252f;
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .notification-details {
                margin: 1rem;
                border-radius: 12px;
            }

            .details-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .notification-header,
            .notification-footer {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .status-pill {
                align-self: flex-start;
            }
        }

        /* Enhanced Comment Section Styles */
        .detail-card.comments {
            margin-top: 2rem;
            background: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .comment-history {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #2c3e50;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #eee;
            white-space: pre-line;
        }

        .comment-divider {
            position: relative;
            text-align: center;
            margin: 1rem 0;
        }

        .comment-divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }

        .comment-divider span {
            background: #fff;
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.85rem;
            position: relative;
            display: inline-block;
        }

        .comment-form {
            background: transparent;
            padding: 0;
            border: none;
        }

        .comment-input-wrapper {
            position: relative;
            display: flex;
            align-items: flex-end;
            margin-top: 0.5rem;
        }

        .comment-input {
            flex: 1;
            border: 1px solid #e0e0e0;
            border-radius: 20px !important;
            padding: 0.75rem 1rem !important;
            padding-right: 3.5rem !important;
            font-size: 0.9rem;
            resize: none;
            min-height: 60px;
            max-height: 120px;
            line-height: 1.4;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }

        .comment-input:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(179, 0, 54, 0.1);
            outline: none;
        }

        .btn-submit-comment {
            position: absolute;
            right: 8px;
            bottom: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit-comment:hover {
            background-color: var(--primary-hover);
            transform: scale(1.05);
        }

        .btn-submit-comment i {
            font-size: 0.9rem;
            margin-right: -2px;
        }

        .comment-divider {
            margin-bottom: 1rem;
        }

        /* Custom Scrollbar for Comment History */
        .comment-history::-webkit-scrollbar {
            width: 8px;
        }

        .comment-history::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .comment-history::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .comment-history::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Add these styles for comment formatting */
        .comment-history {
            /* ... existing styles ... */
            white-space: pre-line; /* Preserves line breaks */
        }

        .comment-name {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
            margin-right: 0.5rem;
            display: inline-block;
        }

        .comment-text {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        /* Add these new styles */
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .comment-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .comment-name i {
            font-size: 1rem;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .comment-text {
            line-height: 1.6;
            color: #2c3e50;
        }

        .comment-item {
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Chat Styles */
        .chat-content {
            display: flex;
            flex-direction: column;
            height: 400px;
            padding: 0;
        }

        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #f8f9fa;
        }

        .chat-history {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message-wrapper {
            display: flex;
            margin-bottom: 1rem;
        }

        .chat-message {
            max-width: 80%;
            margin-right: auto;
        }

        .message-info {
            margin-bottom: 0.25rem;
        }

        .sender-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .sender-name i {
            color: var(--primary-color);
        }

        .message-bubble {
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            border-top-left-radius: 0.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            line-height: 1.5;
            color: #2d3748;
            position: relative;
        }

        .message-time {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
            margin-left: 0.5rem;
        }

        /* Chat Input Styles */
        .chat-input-container {
            padding: 1rem;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        .chat-form {
            margin: 0;
        }

        .input-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            background: #f8f9fa;
            border-radius: 1.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
        }

        .chat-input {
            flex: 1;
            border: none;
            background: transparent;
            min-height: 40px;
            max-height: 120px;
            padding: 0.5rem 0;
            resize: none;
            font-size: 0.95rem;
            line-height: 1.5;
            color: #2d3748;
        }

        .chat-input:focus {
            outline: none;
        }

        .send-button {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-button:hover {
            background: var(--primary-hover);
            transform: scale(1.05);
        }

        .send-button i {
            font-size: 1rem;
            margin-right: -2px;
        }

        /* Custom Scrollbar */
        .chat-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">MachPH</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="?logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="notification-details">
        <!-- Side decoration line that changes color based on status -->
        <div class="status-line <?php echo $notification['action_type']; ?>"></div>
        
        <div class="notification-content">
            <!-- Header Section -->
            <div class="notification-header">
                <div class="header-content">
                    <div class="company-info">
                        <i class="bi bi-building"></i>
                        <h2><?php echo htmlspecialchars($notification['companyname']); ?></h2>
                    </div>
                    <span class="status-pill <?php echo $notification['action_type']; ?>">
                        <i class="bi <?php 
                            echo match($notification['action_type']) {
                                'confirm' => 'bi-check-circle-fill',
                                'pending' => 'bi-clock-fill',
                                'denied' => 'bi-x-circle-fill'
                            };
                        ?>"></i>
                        <?php echo ucfirst($notification['action_type']); ?>
                    </span>
                </div>
            </div>

            <!-- Main Content -->
            <div class="details-grid">
                <?php if($notification['action_type'] == 'confirm'): ?>
                    <div class="detail-card product-details">
                        <div class="card-header">
                            <i class="bi bi-box-seam"></i>
                            <h3>Product Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="info-item">
                                <span class="label">Product Name</span>
                                <span class="value"><?php echo htmlspecialchars($notification['product_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Quantity</span>
                                <span class="value"><?php echo htmlspecialchars($notification['quantity']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($notification['action_type'] == 'pending'): ?>
                    <div class="detail-card dates">
                        <div class="card-header">
                            <i class="bi bi-calendar-event"></i>
                            <h3>Estimated Arrival</h3>
                        </div>
                        <div class="card-content">
                            <div class="date-list">
                                <?php 
                                $dates = explode(',', $notification['estimated_date']);
                                foreach($dates as $date): ?>
                                    <div class="date-item">
                                        <i class="bi bi-calendar2-check"></i>
                                        <?php echo date('F d, Y', strtotime($date)); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($notification['action_type'] == 'denied'): ?>
                    <div class="detail-card denial">
                        <div class="card-header">
                            <i class="bi bi-x-octagon"></i>
                            <h3>Denial Information</h3>
                        </div>
                        <div class="card-content">
                            <div class="denial-reason">
                                <?php echo htmlspecialchars($notification['reason']); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($notification['image']): ?>
                    <div class="detail-card image-card">
                        <div class="card-header">
                            <i class="bi bi-image"></i>
                            <h3>Product Image</h3>
                        </div>
                        <div class="card-content">
                            <div class="image-container">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($notification['image']); ?>" 
                                     alt="Product Image">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($notification['comments']): ?>
                    <div class="detail-card comments" style="grid-column: 1 / -1;">
                        <div class="card-header">
                            <i class="bi bi-chat-dots-fill"></i>
                            <h3>Comments</h3>
                        </div>
                        <div class="card-content chat-content">
                            <div class="chat-container">
                                <?php if($notification['comments']): ?>
                                    <div class="chat-history">
                                        <div class="message-wrapper">
                                            <div class="chat-message">
                                                <div class="message-info">
                                                    <span class="sender-name">
                                                        <i class="bi bi-building"></i>
                                                        <?php echo htmlspecialchars($notification['companyname']); ?>
                                                    </span>
                                                </div>
                                                <div class="message-bubble">
                                                    <?php echo nl2br(htmlspecialchars($notification['comments'])); ?>
                                                </div>
                                                <div class="message-time">
                                                    <?php echo date('h:i A · M d, Y', strtotime($notification['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Message Input -->
                            <!-- <div class="chat-input-container">
                                <form action="add_comment.php" method="POST" class="chat-form">
                                    <input type="hidden" name="action_id" value="<?php echo $action_id; ?>">
                                    <div class="input-wrapper">
                                        <textarea 
                                            class="chat-input" 
                                            name="new_comment" 
                                            placeholder="Type a message..." 
                                            required
                                        ></textarea>
                                        <button type="submit" class="send-button">
                                            <i class="bi bi-send-fill"></i>
                                        </button>
                                    </div>
                                </form>
                            </div> -->
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="notification-footer">
                <div class="timestamp">
                    <i class="bi bi-clock-history"></i>
                    Received on <?php echo date('F d, Y \a\t h:i A', strtotime($notification['created_at'])); ?>
                </div>
                <a href="javascript:history.back();" class="btn-return">
                    <i class="bi bi-arrow-left"></i> Return
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const commentHistory = document.querySelector('.comment-history');
        if (commentHistory) {
            const content = commentHistory.innerHTML;
            const formattedContent = content.replace(/^(.+?):/gm, '<span class="comment-name">$1</span>');
            commentHistory.innerHTML = formattedContent;
        }
    });
    </script>
</body>
</html> 