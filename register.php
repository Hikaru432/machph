<?php
include 'config.php';

$message = []; // Initialize the message array

if (isset($_POST['submit'])) {
    // User input validation and sanitization
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $homeaddress = mysqli_real_escape_string($conn, $_POST['homeaddress']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
    $zipcode = mysqli_real_escape_string($conn, $_POST['zipcode']);
    $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
    $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
    $role = 'user';
    $companyid = NULL; // Set companyid to NULL if it's not provided

    // Image handling
    $image = $_FILES['image']['tmp_name'];
    $image_data = file_get_contents($image); // Read the binary data of the uploaded file
    $image_size = $_FILES['image']['size'];

    // Check if the user already exists
    $select = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'") or die('Error: ' . mysqli_error($conn));

    if (mysqli_num_rows($select) > 0) {
        $message[] = 'User already exists';
    } else {
        // Additional validation checks
        if ($pass != $cpass) {
            $message[] = 'Confirm password not matched!';
        } elseif ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            // Insert user details into the database with image as binary data
            $stmt = mysqli_prepare($conn, "INSERT INTO user (name, email, password, image, firstname, middlename, lastname, homeaddress, barangay, province, municipality, zipcode, role, companyid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssssssssss", $name, $email, $pass, $image_data, $firstname, $middlename, $lastname, $homeaddress, $barangay, $province, $municipality, $zipcode, $role, $companyid);
            if (mysqli_stmt_execute($stmt)) {
                $message[] = 'Registered successfully!';
                header('location:index.php');
            } else {
                $message[] = 'Registration failed!';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .register-container {
            display: flex;
            width: 900px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .register-image {
            width: 50%;
            background: url('img/shop2.jpg') center/cover;
        }
        .register-form {
            width: 50%;
            padding: 40px;
            background: #fff;
        }
        .form-control {
            border-radius: 20px;
        }
        .btn-custom {
            background-color:  rgb(173, 0, 52);
            border: none;
            border-radius: 20px;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #b3003698;
        }
        .text-custom {
            color:rgba(139, 0, 42, 0.82);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-image"></div>
        <div class="register-form">
            <h3 class="text-center mb-4">Sign Up</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <?php if (isset($message)) { 
                    foreach ($message as $msg) {
                        echo '<div class="alert alert-danger">' . $msg . '</div>';
                    }
                } ?>
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="cpassword" class="form-control" placeholder="Confirm Password" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="middlename" class="form-control" placeholder="Middle Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="homeaddress" class="form-control" placeholder="Home Address" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="barangay" class="form-control" placeholder="Barangay" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="municipality" class="form-control" placeholder="City/Municipality" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="province" class="form-control" placeholder="Province" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="zipcode" class="form-control" placeholder="Zip Code" required>
                </div>
                <div class="mb-3">
                    <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png">
                </div>
                <button type="submit" name="submit" class="btn btn-custom" style="color: white;">Register</button>
                <p class="text-center mt-3">Already have an account? <a href="index.php" class="text-custom">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>
