<?php
include 'config.php';

$maincompanyid = $_GET['companyid']; // The main company ID passed from the URL

// Fetch branches connected to the main company and their company details
$branches_query = "
    SELECT b.branchid, b.companyid AS branch_companyid, a.companyname
    FROM branch b
    INNER JOIN autoshop a ON b.companyid = a.companyid
    WHERE b.maincompanyid = $maincompanyid";
$branches_result = mysqli_query($conn, $branches_query);

// Fetch stock summary
$stock_summary_query = "
    SELECT 
        SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) AS no_stock,
        SUM(CASE WHEN quantity > 0 AND quantity <= 10 THEN 1 ELSE 0 END) AS low_stock,
        SUM(CASE WHEN quantity > 10 THEN 1 ELSE 0 END) AS in_stock
    FROM products
    WHERE companyid IN (SELECT companyid FROM branch WHERE maincompanyid = $maincompanyid)";
$stock_summary_result = mysqli_query($conn, $stock_summary_query);
$stock_summary = mysqli_fetch_assoc($stock_summary_result);

// Fetch stock requests from branches to the main company
$requests_query = "
    SELECT r.request_id, a.companyname AS destination_name, p.item_name, r.custom_item, r.quantity, r.request_date, r.request_type
    FROM request r
    LEFT JOIN autoshop a ON r.destination_companyid = a.companyid
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.branchid IN (SELECT branchid FROM branch WHERE maincompanyid = $maincompanyid)
    OR r.destination_companyid = $maincompanyid"; // This condition ensures requests to the main company are included
$requests_result = mysqli_query($conn, $requests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Stock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .status-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .status-card .card-body {
            text-align: center;
        }
        .status-no-stock { background-color: #ffcccc; color: #b30000; font-weight: bold; }
        .status-low-stock { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .status-in-stock { background-color: #d4edda; color: #155724; font-weight: bold; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f9f9f9; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .card-header-custom { background-color: #343a40; color: #fff; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php">Admin</a>
    </div>
</nav>
<br>
<div class="container mt-4">
    <h2 class="mb-4">Branch Stock</h2>

    <!-- Dashboard Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card status-card status-no-stock">
                <div class="card-body">
                    <h5 class="card-title">No Stock</h5>
                    <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                    <p class="card-text fs-4"><?php echo $stock_summary['no_stock']; ?> Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card status-card status-low-stock">
                <div class="card-body">
                    <h5 class="card-title">Low Stock</h5>
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                    <p class="card-text fs-4"><?php echo $stock_summary['low_stock']; ?> Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card status-card status-in-stock">
                <div class="card-body">
                    <h5 class="card-title">In Stock</h5>
                    <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                    <p class="card-text fs-4"><?php echo $stock_summary['in_stock']; ?> Items</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Requests Section -->
    <div class="row mb-4">
    <div class="col-12">
        <h4>Stock Requests</h4>
        <table class="table table-bordered table-striped table-hover" id="requestsTable">
            <thead class="table-dark">
                <tr>
                    <th>Sender Company</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Action</th> <!-- Button to open chat modal -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Modified query to get the sender company name, product name, and quantity
                $requests_query = "
                    SELECT r.request_id, a.companyname AS sender_company, p.item_name, r.quantity
                    FROM request r
                    LEFT JOIN autoshop a ON r.sender_companyid = a.companyid
                    LEFT JOIN products p ON r.product_id = p.id
                    WHERE r.branchid IN (SELECT branchid FROM branch WHERE maincompanyid = $maincompanyid)
                    OR r.destination_companyid = $maincompanyid"; 

                $requests_result = mysqli_query($conn, $requests_query);

                if (mysqli_num_rows($requests_result) > 0): 
                    while ($request = mysqli_fetch_assoc($requests_result)): ?>
                        <tr data-request-id="<?php echo $request['request_id']; ?>" class="clickable-row">
                            <td><?php echo $request['sender_company']; ?></td>
                            <td><?php echo $request['item_name']; ?></td>
                            <td><?php echo $request['quantity']; ?></td>
                            <td><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#chatModal" onclick="loadChat(<?php echo $request['request_id']; ?>)">Chat</button></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No Stock Requests Found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


    <!-- Chat Modal -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">Chat with Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" style="height: 300px; overflow-y: auto;">
                        <!-- Chat messages will be dynamically loaded here -->
                    </div>
                    <div class="mt-3">
                        <textarea id="chatMessage" class="form-control" rows="3" placeholder="Type your message..."></textarea>
                        <button class="btn btn-primary mt-2" onclick="sendMessage()">Send Message</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRequestId = null;

        // Load chat for specific request
        function loadChat(requestId) {
            currentRequestId = requestId;

            // Fetch previous messages
            fetch(`fetchChatMessages.php?request_id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    let chatMessages = '';
                    data.forEach(message => {
                        chatMessages += `<div><strong>${message.sender_name}:</strong> ${message.message}</div>`;
                    });
                    document.getElementById('chatMessages').innerHTML = chatMessages;
                })
                .catch(error => console.log('Error fetching chat messages:', error));
        }

        // Send a new message
        function sendMessage() {
            const message = document.getElementById('chatMessage').value;
            if (!message.trim()) return;

            fetch('sendChatMessage.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    request_id: currentRequestId,
                    message: message,
                }),
            })
            .then(response => response.json())
            .then(data => {
                // Add the new message to the chat box
                document.getElementById('chatMessages').innerHTML += `<div><strong>You:</strong> ${message}</div>`;
                document.getElementById('chatMessage').value = ''; // Clear message input
            })
            .catch(error => console.log('Error sending message:', error));
        }
    </script>


    <!-- Branch Cards Section -->
    <div class="row">
        <?php while ($branch = mysqli_fetch_assoc($branches_result)): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0"><?php echo $branch['companyname']; ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Stock Status</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $branch_companyid = $branch['branch_companyid'];

                                // Fetch products specific to the branch
                                $products_query = "
                                    SELECT p.item_name, p.category, p.quantity
                                    FROM products p
                                    WHERE p.companyid = $branch_companyid";
                                $products_result = mysqli_query($conn, $products_query);

                                if (mysqli_num_rows($products_result) > 0):
                                    while ($product = mysqli_fetch_assoc($products_result)):
                                        $status_class = '';
                                        $status_text = '';

                                        // Determine stock status
                                        if ($product['quantity'] == 0) {
                                            $status_class = 'status-no-stock';
                                            $status_text = 'No-Stock';
                                        } elseif ($product['quantity'] > 0 && $product['quantity'] <= 10) {
                                            $status_class = 'status-low-stock';
                                            $status_text = 'Low-Stock';
                                        } else {
                                            $status_class = 'status-in-stock';
                                            $status_text = 'In-Stock';
                                        }
                                ?>
                                    <tr>
                                        <td><?php echo $product['item_name']; ?></td>
                                        <td><?php echo $product['category']; ?></td>
                                        <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                                        <td><?php echo $product['quantity']; ?></td>
                                    </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No Products Found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
