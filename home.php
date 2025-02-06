<?php
session_start();
include 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Fetch company data from the autoshop table
$query = "SELECT companyname, companyimage FROM autoshop";
$result = mysqli_query($conn, "SELECT * FROM autoshop WHERE status = 'Approved'");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="./output.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    
</head>

<body style="background-color: white;">
    <!-- Upper nav -->
    <nav class="fixed w-full h-20 bg-black flex justify-between items-center px-4 text-gray-100 font-medium" style="overflow: hidden;">
        <ul>
           <li></li>
           <li></li>
        </ul>
    </nav>

    <div class="wrapper" style="position: fixed;">
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
        <div class="main p-3"  style="background-color: #B30036;">
           
        </div>
    </div>
    

    <!-- For the swiper -->
    <section class="absolute left-64 h-screen" style="width: 1100px; top: 100px; left: 550px; ">
    <div class="container my-5">
    <div class="row g-4">
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            // Convert the BLOB data to base64
            $imageData = base64_encode($row['companyimage']);
            $imageSrc = 'data:image/jpeg;base64,' . $imageData; // Adjust image format if needed (e.g., png, jpg)
        ?>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-transparent p-0 position-relative">
                    <img src="<?php echo $imageSrc; ?>" class="card-img-top" alt="Company Image" style="height: 200px; object-fit: cover;">
                    <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center text-white bg-dark bg-opacity-75 opacity-0 hover-opacity-100">
                        <span class="fw-bold fs-5">Explore More</span>
                    </div>
                </div>
                <div class="card-body text-center">
                    <h5 class="card-title fw-bold mb-2 text-primary"><?php echo $row['companyname']; ?></h5>
                    <p class="text-muted mb-3">Mechanic Shop</p>
                    <p class="small text-muted">Quality services and trusted repair solutions.</p>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <a href="carusers.php?companyname=<?php echo urlencode($row['companyname']); ?>" class="btn btn-primary w-75 fw-bold rounded-pill shadow-sm">
                        Repair
                    </a>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>
</div>

<style>
    .card {
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .overlay {
        transition: opacity 0.3s ease-in-out;
    }

    .card:hover .overlay {
        opacity: 1;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.5);
    }

    .card-title {
        font-size: 1.25rem;
    }

    .card-footer {
        padding-top: 0;
    }
</style>

</section>


<script>
// Initialize Swiper if needed
if (<?php echo $use_swiper ? 'true' : 'false'; ?>) {
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 3,
        spaceBetween: 30,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
}
</script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="nav.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Swiper -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="home.js"></script>

</body>

</html>