<?php
// Include the database connection
include_once 'config.php';

// Function to sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags($data));
}

// Handle delete operation

if (isset($_GET['delete_id'])) {
    $delete_id = sanitize($_GET['delete_id']);

    // Check for foreign key relationships
    $check_fk_query = "SELECT * FROM service WHERE user_id = $delete_id OR plateno IN (SELECT plateno FROM car WHERE user_id = $delete_id)";
    $check_fk_result = mysqli_query($conn, $check_fk_query);

    if (mysqli_num_rows($check_fk_result) > 0) {
        echo "Cannot delete user due to existing foreign key relationships in the service or car table.";
    } else {
        // No foreign key relationships, proceed with deletion
        $delete_query_user = "DELETE FROM user WHERE id = $delete_id";
        mysqli_query($conn, $delete_query_user);
        echo "User deleted successfully.";
    }
}


// Handle update operation
if (isset($_POST['update'])) {
    $update_id = sanitize($_POST['update_id']);
    $new_name = sanitize($_POST['new_name']);
    $new_firstname = sanitize($_POST['new_firstname']);
    $new_middlename = sanitize($_POST['new_middlename']);
    $new_lastname = sanitize($_POST['new_lastname']);
    $new_homeaddress = sanitize($_POST['new_homeaddress']);
    $new_email = sanitize($_POST['new_email']);
    $new_password = sanitize($_POST['new_password']);
    $new_barangay = sanitize($_POST['new_barangay']);
    $new_province = sanitize($_POST['new_province']);
    $new_municipality = sanitize($_POST['new_municipality']);
    $new_zipcode = sanitize($_POST['new_zipcode']);
    $new_role = sanitize($_POST['new_role']);

    $update_query = "UPDATE user SET
        name = '$new_name',
        firstname = '$new_firstname',
        middlename = '$new_middlename',
        lastname = '$new_lastname',
        homeaddress = '$new_homeaddress',
        email = '$new_email',
        password = '$new_password',
        barangay = '$new_barangay',
        province = '$new_province',
        municipality = '$new_municipality',
        zipcode = '$new_zipcode',
        role = '$new_role'
        WHERE id = $update_id";

    mysqli_query($conn, $update_query);
}

// Retrieve users from the database
$select_query = "SELECT * FROM user";
$result = mysqli_query($conn, $select_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

<section class="nav-bar-section">
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Link</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dropdown
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" aria-disabled="true">Disabled</a>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
</section>

    <div class="container mt-4">
        <h2>User Table</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Home Address</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Barangay</th>
                    <th>Province</th>
                    <th>Municipality</th>
                    <th>Zip Code</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['firstname']}</td>
                            <td>{$row['middlename']}</td>
                            <td>{$row['lastname']}</td>
                            <td>{$row['homeaddress']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['password']}</td>
                            <td>{$row['barangay']}</td>
                            <td>{$row['province']}</td>
                            <td>{$row['municipality']}</td>
                            <td>{$row['zipcode']}</td>
                            <td>{$row['role']}</td>
                            <td>
                                <a href='admin.php?delete_id={$row['id']}' class='btn btn-danger'>Delete</a>
                                <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#updateModal{$row['id']}'>Update</button>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Update Modal -->
    <?php
    $result = mysqli_query($conn, $select_query); // Fetch data again for the modal
    while ($row = mysqli_fetch_assoc($result)) {
        echo "
        <div class='modal fade' id='updateModal{$row['id']}' tabindex='-1' aria-labelledby='updateModalLabel{$row['id']}' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='updateModalLabel{$row['id']}'>Update User</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <form method='post' action='admin.php'>
                            <input type='hidden' name='update_id' value='{$row['id']}'>
                            <!-- Input fields for all columns -->
                            <div class='mb-3'>
                                <label for='new_name' class='form-label'>Name</label>
                                <input type='text' name='new_name' value='{$row['name']}' class='form-control'>
                            </div>
                            <div class='mb-3'>
                                <label for='new_firstname' class='form-label'>First Name</label>
                                <input type='text' name='new_firstname' value='{$row['firstname']}' class='form-control'>
                            </div>
                            <!-- Add input fields for other columns -->
                            <label for='new_middlename' class='form-label'>Middle Name</label>
                            <input type='text' name='new_middlename' value='{$row['middlename']}' class='form-control'>
                            
                            <label for='new_lastname' class='form-label'>Last Name</label>
                            <input type='text' name='new_lastname' value='{$row['lastname']}' class='form-control'>
                            
                            <label for='new_homeaddress' class='form-label'>Home Address</label>
                            <input type='text' name='new_homeaddress' value='{$row['homeaddress']}' class='form-control'>
                            
                            <label for='new_email' class='form-label'>Email</label>
                            <input type='email' name='new_email' value='{$row['email']}' class='form-control'>
                            
                            <label for='new_password' class='form-label'>Password</label>
                            <input type='password' name='new_password' value='{$row['password']}' class='form-control'>
                            
                            <label for='new_barangay' class='form-label'>Barangay</label>
                            <input type='text' name='new_barangay' value='{$row['barangay']}' class='form-control'>
                            
                            <label for='new_province' class='form-label'>Province</label>
                            <input type='text' name='new_province' value='{$row['province']}' class='form-control'>
                            
                            <label for='new_municipality' class='form-label'>Municipality</label>
                            <input type='text' name='new_municipality' value='{$row['municipality']}' class='form-control'>
                            
                            <label for='new_zipcode' class='form-label'>Zip Code</label>
                            <input type='text' name='new_zipcode' value='{$row['zipcode']}' class='form-control'>
                            
                            <label for='new_role' class='form-label'>Role</label>
                            <input type='text' name='new_role' value='{$row['role']}' class='form-control'>
                            <!-- Add input fields for other columns -->
                            

                            <button type='submit' name='update' class='btn btn-primary mt-2'>Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>";
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
