<?php
session_start();
include 'config.php'; // Assuming this file contains your database connection code

if(!isset($_SESSION['companyid'])){
   header('location:clogin.php');
   exit();
} 

$companyid = $_SESSION['companyid'];
echo "Company ID: " . $companyid; 

// Assuming your database connection is established and stored in $conn
$query = "SELECT * FROM autoshop WHERE companyid = $companyid";
$result = mysqli_query($conn, $query);

if($result && mysqli_num_rows($result) > 0) {
    $company_data = mysqli_fetch_assoc($result);
} else {
    // Redirect to some error page if company data is not found
}

// Fetch mechanics data for the current company
$mechanics_query = "SELECT u.id, u.firstname, u.lastname, u.email, m.employment, m.jobrole FROM mechanic m
                    INNER JOIN user u ON m.user_id = u.id
                    WHERE m.companyid = $companyid";
$mechanics_result = mysqli_query($conn, $mechanics_query);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="#">Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="homemechanic.php">User</a>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php">Sales</a> 
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="addproduct.php">Products</a>
                </li>
                <li class="nav-item"> <a class="nav-link text-white active" aria-current="page" href="repair_table_content.php">Customer</a> 
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-white" href="#">Sales</a></li>
                        <li><a class="dropdown-item text-white" href="#">Products</a></li>
                        <li><a class="dropdown-item text-white" href="#">Customer</a></li>
                        <li><a class="dropdown-item text-white" href="#">Sales Report</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white" href="#">Something else here</a></li>
                    </ul>
                </li>
                <li class="nav-item"> 
                    <a class="nav-link text-white active" aria-current="page" href="clogin.php">Logout</a> 
                </li>
            </ul>
        </div>
    </div>
</nav>

<h1>Welcome <?php echo isset($company_data['companyname']) ? $company_data['companyname'] : ''; ?></h1>
<a href="homemanager.php?companyid=<?php echo $companyid; ?>" class="btn btn-primary">Go to Home Manager</a>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mechanicModal">
  View Registered Mechanics
</button>

<!-- Mechanic Modal -->
<div class="modal fade" id="mechanicModal" tabindex="-1" aria-labelledby="mechanicModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mechanicModalLabel">Registered Mechanics</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if(mysqli_num_rows($mechanics_result) > 0): ?>
        <table class="table">
          <thead>
            <tr>
              <th>Firstname</th>
              <th>Lastname</th>
              <th>Email</th>
              <th>Employment</th>
              <th>Job Role</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while($mechanic_data = mysqli_fetch_assoc($mechanics_result)): ?>
            <tr>
              <td><?php echo $mechanic_data['firstname']; ?></td>
              <td><?php echo $mechanic_data['lastname']; ?></td>
              <td><?php echo $mechanic_data['email']; ?></td>
              <td><?php echo $mechanic_data['employment']; ?></td>
              <td><?php echo $mechanic_data['jobrole']; ?></td>
              <td>
                <form action="approve_decline_mechanic.php" method="POST">
                  <input type="hidden" name="mechanic_id" value="<?php echo $mechanic_data['id']; ?>">
                  <button type="submit" name="approve" class="btn btn-success">Approve</button>
                  <button type="submit" name="decline" class="btn btn-danger">Decline</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p>No registered mechanics found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</body>
</html>