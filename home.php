<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="css/home-second.css">
</head>

<body>
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
                    <a href="carusers.php" class="sidebar-link">
                    <span style="margin-left: 13px;">Car user</span>
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
                            <a href="carregistration.php" class="sidebar-link">Car register</a>
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
            <a href="login.php" target="_blanck" class="sidebar-link">
                <i class="lni lni-exit"></i>
                <span>Logout</span>
            </a>
            </div>
        </aside>
        <div class="main p-3">
            <div class="text-center bg-secondary">
                <li></li>
            </div>
        </div>
    </div>
    
      <!-- Upper nav -->

    <div class="nav-container">
        <div class="upper-nav-container">
                <div class="img-icon-upper-nav">
                    <li><img class="upper-nav-icon-three" src="img/facebook.png" alt="facebook"></li>
                    <li><img class="upper-nav-icon-three" src="img/instagram.png" alt="instagram"></li>
                    <li><img class="upper-nav-icon-three-chrome"src="img/chrome.png" alt="chrome"></li>
                </div>
        
            <div class="upper-nav-icons-info">
                <li><img src="img/gmail.png" alt=""><span>hikaru@gmail.com</span></li>
                <li><img src="img/call.png" alt=""><span>0948-501-1228</span></li> 
            </div>

            <div class="upper-nav-button">
                <a href="carprofile.php">Appointment</a>
            </div>
        </div>
    </div>

    <!-- Sectioning -->
    <section class="home-container">
        
    <div class="h1-home-title-container">
              <h1> NEAR SHOP </h1>
         </div>
       <section class="swiper-extension">
                <div class="swiper mySwiper container">
                    <div class="swiper-wrapper content">

                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m1.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Hikaru Auto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m2.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Slime auto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m3.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Snow suto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m4.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Benemaru auto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m5.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Choe auto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        <div class="swiper-slide card">
                        <div class="card-content">
                            <div class="image">
                            <img src="img/m6.png" alt="">
                            </div>

                            <div class="media-icons">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-twitter"></i>
                            <i class="fab fa-github"></i>
                            </div>

                            <div class="name-profession">
                            <span class="name">Kizaru auto shop</span>
                            <span class="profession">Mechanic shop</span>
                            </div>

                            <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            </div>

                            <div class="button">
                            <button class="aboutMe">About us</button>
                            <button class="hireMe">Message</button>
                            </div>
                        </div>
                        </div>
                        
                  

                    </div>
                    </div>

                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
            </section>
    </section>



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