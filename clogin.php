<?php
session_start();
include 'config.php'; // Assuming this file contains your database connection code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $cname = $_POST['cname'];
    $cpassword = $_POST['cpassword'];

    // Perform select query
    $query = "SELECT * FROM autoshop WHERE cname='$cname' AND cpassword='$cpassword'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {
        // Login successful, set session variable and redirect to admin page
        $user_data = mysqli_fetch_assoc($result);
        $_SESSION['companyid'] = $user_data['companyid'];
        header("location: admin.php");
        exit();
    } else {
        // Login failed, display error message
        $login_error = "Invalid credentials. Please try again.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: #B30036; color: white; font-weight: 800;">
                    Login
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="cname" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="cname" name="cname" required>
                        </div>
                        <div class="mb-3">
                            <label for="cpassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="cpassword" name="cpassword" required>
                        </div>
                        <?php if(isset($login_error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a href="cregister.php">Register</a>
                        <span style="margin-left: 320px;"> <a href="login.php">back</a></span>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-r7Xgs1AeUp2GpS3r+pQ4kpz/zbGOGlZf6Mfk5Bv9os0/7AjLkZgJq6gxciXiBUpZ" crossorigin="anonymous"></script>

</body>
</html>
