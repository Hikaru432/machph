<?php
session_start();
include 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

// Fetch company data from the autoshop table
$query = "SELECT companyname, companyimage FROM autoshop";
$result = mysqli_query($conn, $query);



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
                </li>
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
            <a href="logout.php" target="_blanck" class="sidebar-link">
                <i class="lni lni-exit"></i>
                <span>Logout</span>
            </a>
            </div>
        </aside>
        <div class="main p-3"  style="background-color: #B30036;">
           
        </div>
    </div>
    

    <!-- For the swiper -->
<section class="absolute left-64 h-screen" style="width: 1100px; top: 100px; ">
    <div class="container" style="overflow: hidden;">
        <div class="row justify-content-center" >
            <?php
            // Count the number of cards
            $card_count = mysqli_num_rows($result);

            // Determine if swiper.js should be used
            $use_swiper = $card_count > 3;

            // Loop through each company data and display it in a card
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4">
                    <div class="card" style="position: relative; background: #fff; border-radius: 20px; height: 400px; margin: 60px 0 0 50px; box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);">
                        <div class="card-content" style="display: flex; flex-direction: column; align-items: center; padding: 30px; position: relative; z-index: 100;">
                            <div class="image" style="height: 110px; width: 140px; border-radius: 50%; padding: 3px; background: #b30036; margin-top: 30px;">
                                <img src="<?php echo $row['companyimage']; ?>" alt="" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%; border: 3px solid #fff;">
                            </div>

                            <div class="media-icons" style="position: absolute; top: 12px; right: 95px; display: flex; flex-direction: row; align-items: center;">
                                <i class="fab fa-facebook" style="color: #b30036; opacity: 0.6; margin-top: 10px; transition: all 0.3s ease; cursor: pointer; margin: 10px;"></i>
                                <i class="fab fa-twitter" style="color: #b30036; opacity: 0.6; margin-top: 10px; transition: all 0.3s ease; cursor: pointer; margin: 10px;"></i>
                                <i class="fab fa-github" style="color: #b30036; opacity: 0.6; margin-top: 10px; transition: all 0.3s ease; cursor: pointer; margin: 10px;"></i>
                            </div>

                            <div class="name-profession" style="display: flex; flex-direction: column; align-items: center; margin-top: 20px; color: black;">
                                <span class="name" style="font-size: 20px; font-weight: 600;"><?php echo $row['companyname']; ?></span>
                                <span class="profession" style="font-size: 15px; font-weight: 500;">Mechanic shop</span>
                            </div>

                            <div class="button" style="width: 100%; display: flex; justify-content: space-around; margin-top: 20px;">
                                <button class="repair" style="background: #4a36ff; outline: none; border: none; color: #fff; padding: 8px 22px; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; cursor: pointer;">
                                    <a href="carusers.php?companyname=<?php echo urlencode($row['companyname']); ?>" style="color: #fff; text-decoration: none;">Repair</a>
                                </button>
                                <button class="hireMe" style="background: #b30036; outline: none; border: none; color: #fff; padding: 8px 22px; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; cursor: pointer;">About us</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
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