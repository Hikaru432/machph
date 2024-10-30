<?php
session_start();
include 'config.php';

// Sanitize user ID
$user_id = intval($_SESSION['user_id']);
$user_query = "SELECT * FROM user WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_row = mysqli_fetch_assoc($user_result);

// Fetch all products in the cart at once
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Insert order details
    $insert_order_query = "INSERT INTO salesreport (user_id, payment_method, total_price) VALUES ('$user_id', '$payment_method', '$totalPrice')";
    $insert_order_result = mysqli_query($conn, $insert_order_query);

    if ($insert_order_result) {
        $order_id = mysqli_insert_id($conn);
        $currentDate = date('Y-m-d');

        // Image upload for GCash payment
        if ($payment_method === 'gcash' && isset($_FILES['paymentImage']) && $_FILES['paymentImage']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploaded_img';
            $filename = basename($_FILES['paymentImage']['name']);
            $target_file = $upload_dir . '/' . $filename;
            move_uploaded_file($_FILES['paymentImage']['tmp_name'], $target_file);

            // Update order with the filename
            $update_order_query = "UPDATE salesreport SET paymentimg = '$filename' WHERE id = $order_id";
            mysqli_query($conn, $update_order_query);
        }

        // Insert order items
        $stmt = mysqli_prepare($conn, "INSERT INTO servicebilling (order_id, product_id, quantity, price, order_date) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            mysqli_stmt_bind_param($stmt, "iiids", $order_id, $product['id'], $product['quantity'], $product['selling_price'], $currentDate);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        // Handle optional details
        $details = ($_POST['optionalDetail'] ?? '') ?: ($_POST['cashOptionalDetail'] ?? null);
        $details = empty($details) ? null : $details;
        $update_order_query = "UPDATE salesreport SET detail = ? WHERE id = ?";
        $update_statement = mysqli_prepare($conn, $update_order_query);
        mysqli_stmt_bind_param($update_statement, "si", $details, $order_id);
        mysqli_stmt_execute($update_statement);

        // Clear the cart
        unset($_SESSION['cart']);
        
        // Trigger the modal to display the receipt
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    $('#receiptModal').modal('show');
                });
              </script>";
    } else {
        echo "Failed to place the order. Please try again.";
    }
}


// function displayReceipt($products, $totalPrice) {
//     $receipt_html = '<h2>Receipt</h2><h4>Order Summary</h4><table class="table"><thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead><tbody>';
//     foreach ($products as $product) {
//         $receipt_html .= '<tr><td>' . $product['item_name'] . '</td><td>' . $product['quantity'] . '</td><td>₱' . number_format($product['selling_price'], 2) . '</td><td>₱' . number_format($product['selling_price'] * $product['quantity'], 2) . '</td></tr>';
//     }
//     $receipt_html .= '</tbody><tfoot><tr><th colspan="3" class="text-right">Total:</th><th>₱' . number_format($totalPrice, 2) . '</th></tr></tfoot></table>';
//     echo "<script>
//             var receiptHTML = " . json_encode($receipt_html) . ";
//             document.addEventListener('DOMContentLoaded', function() {
//                 var receiptModalBody = document.getElementById('receiptModalBody');
//                 receiptModalBody.innerHTML = receiptHTML;
//                 $('#receiptModal').modal('show');
//             });
//           </script>";
// }
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
        <a class="navbar-brand text-white" href="home.php">MachPH Store</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="home.php">Home</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link text-white" href="shop.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Contact</a>
                </li>
                <!-- Cart icon -->
                    <ul class="navbar-nav ml-2">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" data-toggle="modal" data-target="#cartModal">
                                <i class="fas fa-shopping-cart"></i> Cart <span class="badge badge-pill badge-light"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                            </a>
                        </li>
                    </ul>
            </ul>
          <!-- Search Bar -->
          <form class="form-inline my-2 my-lg-0" method="GET" action="shop.php">
                <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h2 class="mb-4">Checkout</h2>
    <div class="row">
        <div class="col-md-6">
            <h4>Order Summary</h4>
            <table class="table">
                <thead>
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
                        <td><?php echo $product['item_name']; ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>₱<?php echo number_format($product['selling_price'], 2); ?></td>
                        <td>₱<?php echo number_format($product['selling_price'] * $product['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total:</th>
                        <th>₱<?php echo number_format($totalPrice, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-6">
            <h4>Payment Method</h4>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="paymentMethod">Select Payment Method:</label>
                    <select class="form-control" id="paymentMethod" name="payment_method">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div id="paymentImageDiv" style="display: none;">
                    <div class="form-group">
                        <label for="paymentImage">Upload Payment Screenshot:</label>
                        <input type="file" class="form-control-file" id="paymentImage" name="paymentImage">
                    </div>
                </div>
                <div class="form-group">
                    <label for="optionalDetail">Enter other detail (optional):</label>
                    <input type="text" class="form-control" id="optionalDetail" name="optionalDetail">
                </div>
                <button type="submit" class="btn btn-primary" name="submit_payment">Submit Payment</button>
            </form>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="redirectToShop()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="receiptModalBody">
                <!-- Receipt HTML will be generated and inserted here by PHP -->
                <?php if (isset($products) && !empty($products)): ?>
                    <h2>Receipt</h2>
                    <h4>Order Summary</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
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
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>₱<?= number_format($totalPrice, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
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