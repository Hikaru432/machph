<?php
session_start();
include 'config.php';

// Ensure cart is initialized in the session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty.'); window.location.href = 'shop.php';</script>";
    exit;
}

$company_id = isset($_GET['companyid']) ? intval($_GET['companyid']) : 0;
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Get user information
$user_query = "SELECT * FROM user WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_row = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

// Get autoshop information if company_id is valid
$autoshop = null;
if ($company_id) {
    $autoshop_query = "SELECT * FROM autoshop WHERE companyid = ?";
    $autoshop_stmt = mysqli_prepare($conn, $autoshop_query);
    mysqli_stmt_bind_param($autoshop_stmt, "i", $company_id);
    mysqli_stmt_execute($autoshop_stmt);
    $autoshop_result = mysqli_stmt_get_result($autoshop_stmt);
    if ($autoshop_result && mysqli_num_rows($autoshop_result) > 0) {
        $autoshop = mysqli_fetch_assoc($autoshop_result);
    }
    mysqli_stmt_close($autoshop_stmt);
}

// Fetch all products in the cart
$cart = $_SESSION['cart'];
$product_ids = implode(',', array_map('intval', array_keys($cart)));
$product_query = "SELECT * FROM products WHERE id IN ($product_ids)";
$product_result = mysqli_query($conn, $product_query);
$products = [];
$totalPrice = 0;

while ($product_row = mysqli_fetch_assoc($product_result)) {
    $product_id = $product_row['id'];
    $quantity = $cart[$product_id];
    $product_row['quantity'] = $quantity;
    $totalPrice += ($product_row['selling_price'] * $quantity);
    $products[] = $product_row;
}

// Handle form submission for payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Insert order details into `salesreport`
    $insert_order_query = "INSERT INTO salesreport (user_id, payment_method, total_price) VALUES (?, ?, ?)";
    $insert_order_stmt = mysqli_prepare($conn, $insert_order_query);
    mysqli_stmt_bind_param($insert_order_stmt, "isd", $user_id, $payment_method, $totalPrice);
    $insert_order_result = mysqli_stmt_execute($insert_order_stmt);

    if ($insert_order_result) {
        $order_id = mysqli_insert_id($conn);
        $currentDate = date('Y-m-d');

        // Handle GCash image upload if selected
        if ($payment_method === 'gcash' && isset($_FILES['paymentImage']) && $_FILES['paymentImage']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploaded_img';
            $filename = basename($_FILES['paymentImage']['name']);
            $target_file = $upload_dir . '/' . $filename;
            move_uploaded_file($_FILES['paymentImage']['tmp_name'], $target_file);

            // Update the salesreport with image filename
            $update_order_query = "UPDATE salesreport SET paymentimg = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_order_query);
            mysqli_stmt_bind_param($update_stmt, "si", $filename, $order_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }

        // Insert order items into `servicebilling`
        $stmt = mysqli_prepare($conn, "INSERT INTO servicebilling (order_id, product_id, quantity, price, order_date) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            mysqli_stmt_bind_param($stmt, "iiids", $order_id, $product['id'], $product['quantity'], $product['selling_price'], $currentDate);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        // Optional details
        $details = $_POST['optionalDetail'] ?? null;
        $details = empty($details) ? null : $details;
        $update_order_query = "UPDATE salesreport SET detail = ? WHERE id = ?";
        $update_statement = mysqli_prepare($conn, $update_order_query);
        mysqli_stmt_bind_param($update_statement, "si", $details, $order_id);
        mysqli_stmt_execute($update_statement);

        // Clear cart
        unset($_SESSION['cart']);

        echo "<script>alert('Order successfully placed.'); window.location.href = 'shop.php';</script>";
    } else {
        echo "<script>alert('Failed to place the order. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #b30036;">
    <div class="container">
        <a class="navbar-brand text-white" href="shop.php">Shop</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
     
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4 text-center text-primary">Checkout</h2>
    
    <div class="row">
        <!-- Order Summary Section -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Order Summary</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                        <td>₱<?php echo number_format($product['selling_price'], 2); ?></td>
                                        <td>₱<?php echo number_format($product['selling_price'] * $product['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>₱<?php echo number_format($totalPrice, 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Payment Method</h4>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <!-- Payment Method Dropdown -->
                        <div class="form-group mb-3">
                            <label for="paymentMethod">Select Payment Method:</label>
                            <select class="form-control" id="paymentMethod" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="paypal">PayPal</option>
                            </select>
                            <small class="form-text text-muted">Choose your preferred payment method.</small>
                        </div>

                        <!-- Payment Screenshot Upload (conditional display) -->
                        <div id="paymentImageDiv" class="form-group mb-3" style="display: none;">
                            <label for="paymentImage">Upload Payment Screenshot:</label>
                            <input type="file" class="form-control-file" id="paymentImage" name="paymentImage" accept="image/*">
                            <small class="form-text text-muted">Upload a screenshot of your payment (if applicable).</small>
                        </div>

                        <!-- Optional Detail Input -->
                        <div class="form-group mb-3">
                            <label for="optionalDetail">Enter Other Detail (Optional):</label>
                            <input type="text" class="form-control" id="optionalDetail" name="optionalDetail" placeholder="Enter any additional details here">
                            <small class="form-text text-muted">Any additional info (e.g., transaction reference number).</small>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success btn-lg btn-block" name="submit_payment">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="redirectToShop()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="receiptModalBody">
                <?php if (isset($products) && !empty($products)): ?>
                    <!-- Company and Customer Information -->
                    <div class="receipt-header mb-4">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="text-primary"><?= htmlspecialchars($user_row['name']) ?></h4>
                                <p class="mb-1"><?= htmlspecialchars($user_row['homeaddress']) ?></p>
                                <p class="mb-1"><?= htmlspecialchars($user_row['barangay']) ?>, <?= htmlspecialchars($user_row['municipality']) ?>, <?= htmlspecialchars($user_row['province']) ?>, <?= htmlspecialchars($user_row['zipcode']) ?></p>
                            </div>
                            <div class="col-6 text-right">
                                <?php if (isset($autoshop)): ?>
                                    <h5 class="text-success"><?= htmlspecialchars($autoshop['companyname']) ?></h5>
                                    <p><?= htmlspecialchars($autoshop['streetaddress']) ?>, <?= htmlspecialchars($autoshop['city']) ?>, <?= htmlspecialchars($autoshop['region']) ?>, <?= htmlspecialchars($autoshop['zipcode']) ?></p>
                                    <p>Phone: <?= htmlspecialchars($autoshop['companyphonenumber']) ?></p>
                                <?php else: ?>
                                    <p class="text-danger">Autoshop details not found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="receipt-details mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> <?= date('Y-m-d') ?></p>
                                <p><strong>Order ID:</strong> #<?= htmlspecialchars($order_id ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Itemized Products -->
                    <div class="receipt-items mb-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['item_name']) ?></td>
                                            <td><?= htmlspecialchars($product['quantity']) ?></td>
                                            <td>₱<?= number_format($product['selling_price'], 2) ?></td>
                                            <td>₱<?= number_format($product['selling_price'] * $product['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Amount -->
                    <div class="receipt-total text-right">
                        <h4>Total: <span class="text-success">₱<?= number_format($totalPrice, 2) ?></span></h4>
                    </div>
                <?php else: ?>
                    <p>No items in the order.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="redirectToShop()">Proceed</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
            </div>
        </div>
    </div>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($insert_order_result) && $insert_order_result): ?>
    <!-- Script to trigger the modal to display the receipt -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#receiptModal').modal('show');
        });
    </script>
<?php endif; ?>

<script>
    document.getElementById('paymentMethod').addEventListener('change', function() {
        var paymentMethod = this.value;
        var paymentImageDiv = document.getElementById('paymentImageDiv');
        if (paymentMethod === 'gcash') {
            paymentImageDiv.style.display = 'block';
        } else {
            paymentImageDiv.style.display = 'none';
        }
    });

    function printReceipt() {
        var printContents = document.getElementById('receiptModalBody').innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.href = 'shop.php'; // Redirect to shop.php after printing
    }

    function redirectToShop() {
        window.location.href = 'shop.php';
    }
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>