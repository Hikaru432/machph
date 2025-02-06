<?php
include 'config.php'; 

// Check if companyid and role are set in the GET request
if (isset($_GET['companyid']) && isset($_GET['role'])) {
    $_SESSION['companyid'] = $_GET['companyid'];
    $_SESSION['role'] = $_GET['role'];
}

$branch_companyid = $_GET['companyid'];

// Fetch the main company
$main_query = "SELECT * FROM autoshop WHERE companyid = (SELECT maincompanyid FROM branch WHERE companyid = '$branch_companyid')";
$main_result = mysqli_query($conn, $main_query);
$main = mysqli_fetch_assoc($main_result);

// Fetch branches connected to the main company
$branches_query = "SELECT * FROM branch WHERE maincompanyid = '{$main['companyid']}'";
$branches_result = mysqli_query($conn, $branches_query);

// Fetch products for a specific company
function fetch_products($conn, $companyid)
{
    $query = "SELECT id, item_name, category, quantity FROM products WHERE companyid = '$companyid'";
    return mysqli_query($conn, $query);
}

// Process product request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_companyid = $_POST['destination_companyid'] ?? null;
    $selected_product_id = $_POST['product_id'] ?? null;
    $custom_request_item = $_POST['custom_item'] ?? null;
    $quantity = $_POST['quantity'];

    // Validate if branchid exists in the branch table
    $branch_check_query = "SELECT * FROM branch WHERE branchid = '$branch_companyid'";
    $branch_check_result = mysqli_query($conn, $branch_check_query);

    if (mysqli_num_rows($branch_check_result) == 0) {
        echo '<script>alert("Invalid branch ID! Please check the branch information.");</script>';
        exit; // Stop execution if the branch doesn't exist
    }

    // Validate if destination company ID is valid
    if ($destination_companyid) {
        $destination_check_query = "SELECT * FROM autoshop WHERE companyid = '$destination_companyid'";
        $destination_check_result = mysqli_query($conn, $destination_check_query);

        if (mysqli_num_rows($destination_check_result) == 0) {
            echo '<script>alert("Invalid destination company ID! Please select a valid destination.");</script>';
            exit; // Stop execution if the destination company ID doesn't exist
        }
    }

    // Insert request data into the 'request' table
    $query = "
        INSERT INTO request (branchid, product_id, custom_item, quantity, destination_companyid)
        VALUES ('$branch_companyid', '$selected_product_id', '$custom_request_item', '$quantity', '$destination_companyid')
    ";

    if (mysqli_query($conn, $query)) {
        echo '<script>
                if (confirm("Request submitted successfully. Do you want to proceed?")) {
                    alert("Request Proceeded Successfully!");
                } else {
                    window.history.back();
                }
            </script>';
    } else {
        echo '<script>alert("Error Sending Request! Please try again.");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .main-section, .branch-section {
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .main-section {
            background-color: #e3f2fd;
            border-left: 6px solid #0d6efd;
        }
        .branch-section {
            background-color: #ffffff;
            border-left: 6px solid #6c757d;
        }
        .table th {
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
        }
        .request-form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php">Admin</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Available Products</h2>

            <!-- Main Company Section -->
            <div class="main-section">
                <h3>Main: <?php echo $main['companyname']; ?></h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $main_products = fetch_products($conn, $main['companyid']);
                        while ($product = mysqli_fetch_assoc($main_products)): ?>
                            <tr>
                                <td><?php echo $product['item_name']; ?></td>
                                <td><?php echo $product['category']; ?></td>
                                <td><?php echo $product['quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Branch Sections -->
            <?php while ($branch = mysqli_fetch_assoc($branches_result)): ?>
                <div class="branch-section">
                    <h3>Branch: <?php echo $branch['branchname']; ?></h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $branch_products = fetch_products($conn, $branch['companyid']);
                            while ($product = mysqli_fetch_assoc($branch_products)): ?>
                                <tr>
                                    <td><?php echo $product['item_name']; ?></td>
                                    <td><?php echo $product['category']; ?></td>
                                    <td><?php echo $product['quantity']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="col-lg-4">
            <div class="request-form">
                <h2>Request Products</h2>
                <form method="POST" action="submit_request.php" onsubmit="return confirmRequest()">
                    <div class="mb-3">
                        <label for="destination_companyid" class="form-label">Select Destination</label>
                        <select class="form-select" id="destination_companyid" name="destination_companyid" required>
                            <option value="">Choose Destination</option>
                            <option value="<?php echo $main['companyid']; ?>">Main: <?php echo $main['companyname']; ?></option>
                            <?php
                            $branches_result = mysqli_query($conn, $branches_query);
                            while ($branch = mysqli_fetch_assoc($branches_result)): ?>
                                <option value="<?php echo $branch['companyid']; ?>" data-branch-id="<?php echo $branch['branchid']; ?>">
                                    Branch: <?php echo $branch['branchname']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product" class="form-label">Available Products</label>
                        <select class="form-select" id="product" name="product_id">
                            <option value="">Select a product</option>
                            <!-- Products will be dynamically loaded -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="custom_item" class="form-label">Comment</label>
                        <input type="text" class="form-control" id="custom_item" name="custom_item" placeholder="Enter custom item name">
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>

                    <input type="hidden" id="branch_id" name="branch_id" value="">

                    <button type="submit" class="btn btn-primary w-100">Send Request</button>
                </form>
                <!-- <div class="col-lg-4">
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#chatModal">Message</button>
                </div> -->
            </div>
        </div>
        <!-- Chat Modal -->
            <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="chatModalLabel">Chat with Sender</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Sender List -->
                            <div id="senderList">
                                <h5>Select a sender to view chat</h5>
                                <ul class="list-group" id="senders">
                                    <!-- Senders will be dynamically loaded here -->
                                </ul>
                            </div>

                            <!-- Chat View -->
                            <div id="chatView" class="d-none">
                                <h5>Messages</h5>
                                <div id="chatMessages" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Messages will be loaded here -->
                                </div>
                                <textarea id="chatMessage" class="form-control" placeholder="Type a message..."></textarea>
                                <button type="button" class="btn btn-primary mt-2" id="sendMessageBtn">Send Message</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Trigger when "Message" button is clicked
                document.getElementById('chatModal').addEventListener('show.bs.modal', function () {
                    loadSenders();
                });

                // Load the list of senders
                function loadSenders() {
                    const requestId = <?php echo $_GET['request_id']; ?>; // Assuming request_id is passed in the URL
                    const companyId = <?php echo $main['companyid']; ?>; // Company ID from session or autoshop table

                    fetch(`getSenders.php?request_id=${requestId}&company_id=${companyId}`)
                        .then(response => response.json())
                        .then(data => {
                            const senderList = document.getElementById('senders');
                            senderList.innerHTML = '';
                            data.forEach(sender => {
                                const li = document.createElement('li');
                                li.classList.add('list-group-item');
                                li.textContent = sender.company_name;
                                li.dataset.senderId = sender.sender_id; // Store sender_id for later
                                li.addEventListener('click', function () {
                                    loadChat(sender.sender_id);
                                });
                                senderList.appendChild(li);
                            });
                        })
                        .catch(error => console.log('Error loading senders:', error));
                }

                // Load chat messages when sender is clicked
                function loadChat(senderId) {
                    const requestId = <?php echo $_GET['request_id']; ?>;

                    fetch(`getChatMessages.php?request_id=${requestId}&sender_id=${senderId}`)
                        .then(response => response.json())
                        .then(data => {
                            const chatMessages = document.getElementById('chatMessages');
                            chatMessages.innerHTML = '';
                            data.forEach(msg => {
                                const msgDiv = document.createElement('div');
                                msgDiv.classList.add('message');
                                msgDiv.innerHTML = `<strong>${msg.sender_name}:</strong> ${msg.message} <small>(${msg.sent_at})</small>`;
                                chatMessages.appendChild(msgDiv);
                            });

                            document.getElementById('senderList').classList.add('d-none');
                            document.getElementById('chatView').classList.remove('d-none');
                        })
                        .catch(error => console.log('Error loading chat:', error));
                }

                // Send message to the selected sender
                document.getElementById('sendMessageBtn').addEventListener('click', function () {
                    const message = document.getElementById('chatMessage').value.trim();
                    if (message === '') {
                        alert('Please enter a message!');
                        return;
                    }

                    const senderId = document.querySelector('#senders .active')?.dataset.senderId;
                    const requestId = <?php echo $_GET['request_id']; ?>;
                    const companyId = <?php echo $main['companyid']; ?>;

                    if (!senderId) {
                        alert('No sender selected.');
                        return;
                    }

                    const payload = {
                        request_id: requestId,
                        company_id: companyId,
                        sender_id: companyId, // Assuming you're sending as the main company
                        message: message,
                    };

                    fetch('sendMessage.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadChat(senderId); // Reload the chat to show the new message
                            document.getElementById('chatMessage').value = ''; // Clear input
                        } else {
                            console.error('Error sending message:', data.error);
                        }
                    })
                    .catch(error => console.log('Error sending message:', error));
                });
            </script>

        <script>
            document.getElementById('destination_companyid').addEventListener('change', function() {
                var destinationId = this.value;
                var branchId = this.options[this.selectedIndex].getAttribute('data-branch-id');
                document.getElementById('branch_id').value = branchId || ''; // Set branch_id if a branch is selected

                if (destinationId) {
                    // AJAX request to fetch products
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'fetch_products.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status == 200) {
                            var response = JSON.parse(xhr.responseText);
                            var productSelect = document.getElementById('product');
                            productSelect.innerHTML = '<option value="">Select a product</option>';

                            if (response.products.length > 0) {
                                response.products.forEach(function(product) {
                                    var option = document.createElement('option');
                                    option.value = product.id;
                                    option.textContent = product.item_name + ' - Quantity: ' + product.quantity;
                                    productSelect.appendChild(option);
                                });
                            } else {
                                var option = document.createElement('option');
                                option.value = "";
                                option.textContent = "No products available";
                                productSelect.appendChild(option);
                            }
                        } else {
                            console.error('Failed to fetch products');
                        }
                    };
                    xhr.send('companyid=' + destinationId);
                } else {
                    document.getElementById('product').innerHTML = '<option value="">Select a product</option>';
                }
            });
            </script>
    </div>

    <div class="row mt-5">
            <div class="col-lg-12">
                <h3>Request History</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Destination</th>
                            <th>Request Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query to fetch request history
                        $requests_query = "
                            SELECT 
                                r.request_id, 
                                COALESCE(p.item_name, r.custom_item) AS item_name, 
                                r.quantity, 
                                CASE 
                                    WHEN r.branchid IS NOT NULL THEN b.branchname
                                    ELSE a.companyname
                                END AS destination, 
                                r.request_date 
                            FROM 
                                request r
                            LEFT JOIN products p ON r.product_id = p.id
                            LEFT JOIN branch b ON r.branchid = b.branchid
                            LEFT JOIN autoshop a ON r.destination_companyid = a.companyid
                        ";
                        
                        $requests_result = mysqli_query($conn, $requests_query);
                        if ($requests_result && mysqli_num_rows($requests_result) > 0) {
                            while ($request = mysqli_fetch_assoc($requests_result)): ?>
                                <tr>
                                    <td><?php echo $request['request_id']; ?></td>
                                    <td><?php echo $request['item_name']; ?></td>
                                    <td><?php echo $request['quantity']; ?></td>
                                    <td><?php echo $request['destination']; ?></td>
                                    <td><?php echo $request['request_date']; ?></td>
                                </tr>
                            <?php endwhile;
                        } else { ?>
                            <tr>
                                <td colspan="5">No requests found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <br>
            <br>
            <br>
        </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
