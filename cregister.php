<?php
session_start();
include 'config.php'; // Assuming this file contains your database connection code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $companyname = $_POST['companyname'];
    $companyemail = $_POST['companyemail'];
    $companyphonenumber = $_POST['companyphonenumber'];
    $streetaddress = $_POST['streetaddress'];
    $city = $_POST['city'];
    $region = $_POST['region'];
    $zipcode = $_POST['zipcode'];
    $country = $_POST['country'];
    $cname = $_POST['cname'];
    $cpassword = $_POST['cpassword'];

    // File upload handling
    $target_directory = "uploaded_img"; // Directory where you want to store uploaded images
    $target_file = $target_directory . basename($_FILES["companyimage"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["companyimage"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["companyimage"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["companyimage"]["tmp_name"], $target_file)) {
            // File uploaded successfully, now insert data into database
            $companyimage = $target_file;

            // Perform insert query
            $query = "INSERT INTO autoshop (companyname, companyemail, companyphonenumber, streetaddress, city, region, zipcode, country, cname, cpassword, companyimage) VALUES ('$companyname', '$companyemail', '$companyphonenumber', '$streetaddress', '$city', '$region', '$zipcode', '$country', '$cname', '$cpassword', '$companyimage')";
            
            if(mysqli_query($conn, $query)) {
                // Registration successful, redirect to login page
                header("location: clogin.php");
                exit();
            } else {
                // Error handling
                echo "Error: " . $query . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: #B30036; color: white;">
                    <h2>Company Register</h2>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="companyname" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="companyname" name="companyname" required>
                        </div>
                        <div class="mb-3">
                            <label for="companyemail" class="form-label">Company Email</label>
                            <input type="email" class="form-control" id="companyemail" name="companyemail" required>
                        </div>
                        <div class="mb-3">
                            <label for="companyphonenumber" class="form-label">Company Phone Number</label>
                            <input type="tel" class="form-control" id="companyphonenumber" name="companyphonenumber" required>
                        </div>
                        <div class="mb-3">
                            <label for="streetaddress" class="form-label">Barangays</label>
                            <input type="text" class="form-control" id="streetaddress" name="streetaddress" required>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="region" class="form-label">Region</label>
                            <input type="text" class="form-control" id="region" name="region" required>
                        </div>
                        <div class="mb-3">
                            <label for="zipcode" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="zipcode" name="zipcode" required>
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="Philippines">Philippines</option>
                                <option value="China">China</option>
                                <option value="Japan">Japan</option>
                                <option value="Korea">Korea</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cname" class="form-label">Username</label>
                            <input type="text" class="form-control" id="cname" name="cname" required>
                        </div>
                        <div class="mb-3">
                            <label for="cpassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="cpassword" name="cpassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="companyimage" class="form-label">Company Image</label>
                            <input type="file" class="form-control" id="companyimage" name="companyimage" accept="image/*" >
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-r7Xgs1AeUp2GpS3r+pQ4kpz/zbGOGlZf6Mfk5Bv9os0/7AjLkZgJq6gxciXiBUpZ" crossorigin="anonymous"></script>

</body>
</html>
