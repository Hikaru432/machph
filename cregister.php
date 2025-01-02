<?php
session_start();
include 'config.php'; // Database connection

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
    $cpassword = ($_POST['cpassword']);
    $role = $_POST['role'];
    $maincompanyid = $role === 'Branch' ? $_POST['maincompanyid'] : null;

    // File upload handling
    if ($_FILES["companyimage"]["error"] == 0) {
        // Read image as binary data
        $companyimage = file_get_contents($_FILES["companyimage"]["tmp_name"]);
        
        // Insert into the database
        if ($role === "Main") {
            // Insert into autoshop for Main company
            $query = "INSERT INTO autoshop (companyname, companyemail, companyphonenumber, streetaddress, city, region, zipcode, country, cname, cpassword, companyimage, role)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Main')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssss", $companyname, $companyemail, $companyphonenumber, $streetaddress, $city, $region, $zipcode, $country, $cname, $cpassword, $companyimage);
            $stmt->execute();

        } elseif ($role === "Branch") {
            // Insert into autoshop for Branch company
            $query = "INSERT INTO autoshop (companyname, companyemail, companyphonenumber, streetaddress, city, region, zipcode, country, cname, cpassword, companyimage, role)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Branch')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssss", $companyname, $companyemail, $companyphonenumber, $streetaddress, $city, $region, $zipcode, $country, $cname, $cpassword, $companyimage);
            $stmt->execute();

            // Get the `companyid` of the newly inserted branch
            $branchCompanyId = $stmt->insert_id;

            // Insert into branch table, including the maincompanyid and specific branch `companyid`
            $queryBranch = "INSERT INTO branch (maincompanyid, branchname, companyid) VALUES (?, ?, ?)";
            $stmtBranch = $conn->prepare($queryBranch);
            $stmtBranch->bind_param("isi", $maincompanyid, $companyname, $branchCompanyId);
            $stmtBranch->execute();
        }

        header("location: clogin.php");
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <form action="" method="post" enctype="multipart/form-data">
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
                            <label for="role" class="form-label">Company Type</label>
                            <select class="form-select" id="role" name="role" onchange="toggleMainSelection()" required>
                                <option value="Main">Main</option>
                                <option value="Branch">Branch</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="maincompanydiv">
                            <label for="maincompanyid" class="form-label">Select Main Company</label>
                            <select class="form-select" id="maincompanyid" name="maincompanyid">
                                <?php
                                $query = "SELECT companyid, companyname FROM autoshop WHERE role='Main'";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['companyid'] . "'>" . $row['companyname'] . "</option>";
                                }
                                ?>
                            </select>
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
                            <label for="companyimage" class="form-label">Company Logo</label>
                            <input type="file" class="form-control" id="companyimage" name="companyimage" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #B30036;">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMainSelection() {
        const companyType = document.getElementById('role').value;
        const mainDiv = document.getElementById('maincompanydiv');
        if (companyType === 'Branch') {
            mainDiv.classList.remove('d-none');
        } else {
            mainDiv.classList.add('d-none');
        }
    }
</script>
</body>
</html>
