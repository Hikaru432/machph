<?php
session_start();

// Redirect to login.php if user is not logged in
if(!isset($_SESSION['user_id'])){
   header('location:index.php');
   exit();
}

// Redirect to login.php after logout
if(isset($_GET['logout'])){
   unset($_SESSION['user_id']);
   session_destroy();
   header('location:index.php');
   exit();
}
include 'config.php';

$user_id = $_SESSION['user_id'];

// Prepared statement for security
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
   $fetch = $result->fetch_assoc();
} else {
   die('No user found');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   <title>Profile</title>
   <link rel="stylesheet" href="css/nav.css">
   <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <!-- Upper nav -->
    <nav class="fixed w-full h-20 bg-black flex justify-between items-center px-4 text-gray-100 font-medium">
        <ul>
           <li></li>
           <li></li>
        </ul>
    </nav>

   <div class="container-navbar-home">
      <div class="wrapper">
      <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
               <ion-icon style="color:white; font-size: 35px; margin-left: -10px;" name="grid-outline"></ion-icon>
                </button>
                <div class="sidebar-logo">
                    <a href="home.php">MachPH</a>
                </div>
            </div>
            <ul class="sidebar-nav">
            <li class="sidebar-item">
              <!--  <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                    <a href="home.php" class="sidebar-link">
                    <span class="active" style="margin-left: 13px;">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
               <!-- <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                    <a href="profile.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Profile</span>
                    </a>
                </li>
                <li class="sidebar-item">
               <!-- <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
               <a href="vehicleuser.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="sidebar-link">
                    <span style="margin-left: 13px;">Vehicle user</span>
                </a>

                </li>
                <li class="sidebar-item">
                <!-- <ion-icon style="color:white; font-size: 25px; position: absolute; top: 6px; left: 8px;" name="person-circle"></ion-icon>-->
                <a hrefv="ehicleuser.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="sidebar-link">
                        <span style="margin-left: 13px;">Condition</span>
                    </a>
                <li class="sidebar-item">
                    <a href="shop.php" class="sidebar-link">
                    <span style="margin-left: 13px;">shop</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="carregistration.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Vehicle register</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="date_profile.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Update profile</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="register.php" class="sidebar-link">
                    <span style="margin-left: 13px;">User register</span>
                    </a>
                </li>
                
                <!-- <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-protection"></i>
                        <span>Auth</span>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="register.php" class="sidebar-link">User register</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="carregistration.php" class="sidebar-link">Vehicle register</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="update_profile.php" class="sidebar-link">Update profile</a>
                        </li>
                    </ul>
                </li> -->
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Appointent</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                                <li class="sidebar-item">
                                    <a href="identify.php" class="sidebar-link">Identifying</a>
                                </li>

                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Notification</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Setting</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
            <a href="index.php" class="sidebar-link">
                <i class="lni lni-exit"></i>
                <span>Logout</span>
            </a>
            </div>
        </aside>
         
         <div class="main p-5">
            <section class="profile-section bg-white shadow-lg rounded-lg p-5">
               <div class="profile-header text-center mb-4">
                  <?php
                  // Check if the image is stored in the database (as BLOB)
                  if(empty($fetch['image'])){
                     // If no image is found, display a default avatar
                     echo '<img src="images/default-avatar.png" alt="Default Avatar" class="rounded-circle border border-3" style="width: 150px; height: 150px;">';
                  } else {
                     // Display the BLOB image using image.php script
                     echo '<img src="image.php?id=' . $user_id . '" alt="User Image" class="rounded-circle border border-3" style="width: 150px; height: 150px;">';
                  }
                  ?>

                  <span style="position:;">
                     <h3 class="mt-3 fw-bold"><?php echo $fetch['name']; ?></h3>
                  </span>
               
               </div>

               <!-- Personal Information Section -->
               <div class="profile-info mt-4">
                  <h4 class="text-center fw-bold mb-4">Personal Information</h4>
                  <div class="row">
                     <!-- First Name -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>First Name:</strong>
                           <p class="mb-0"><?php echo $fetch['firstname']; ?></p>
                        </div>
                     </div>
                     <!-- Middle Name -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Middle Name:</strong>
                           <p class="mb-0"><?php echo $fetch['middlename']; ?></p>
                        </div>
                     </div>
                     <!-- Last Name -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Last Name:</strong>
                           <p class="mb-0"><?php echo $fetch['lastname']; ?></p>
                        </div>
                     </div>
                     <!-- Address -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Address:</strong>
                           <p class="mb-0"><?php echo $fetch['homeaddress']; ?></p>
                        </div>
                     </div>
                     <!-- Barangay -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Barangay:</strong>
                           <p class="mb-0"><?php echo $fetch['barangay']; ?></p>
                        </div>
                     </div>
                     <!-- City -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>City:</strong>
                           <p class="mb-0"><?php echo $fetch['municipality']; ?></p>
                        </div>
                     </div>
                     <!-- Province -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Province:</strong>
                           <p class="mb-0"><?php echo $fetch['province']; ?></p>
                        </div>
                     </div>
                     <!-- Zip Code -->
                     <div class="col-md-4 mb-3"> <!-- Added margin bottom for spacing -->
                        <div class="card border border-secondary p-3 text-center">
                           <strong>Zip Code:</strong>
                           <p class="mb-0"><?php echo $fetch['zipcode']; ?></p>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
   <a href="update_profile.php" class="btn btn-primary mt-2 px-4 py-2 shadow-sm">Update Profile</a>
</div>

        

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="nav.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
