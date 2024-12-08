<?php
session_start();
include 'config.php';

$sql = "SELECT * FROM service";
$result = $conn->query($sql);

$user_id = $_SESSION['user_id'];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];

        // Fetch user details
        $userSql = "SELECT name FROM user WHERE id = $userId";
        $userResult = $conn->query($userSql);
        $userName = ($userResult->num_rows > 0) ? $userResult->fetch_assoc()['name'] : 'Unknown';

        // Fetch current user's details
        $select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($select) > 0) {
            $fetch = mysqli_fetch_assoc($select);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/identify.css">
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
                    <span style="margin-left: 13px;">Dashboard</span>
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
                                    <a href="#" class="sidebar-link">Identifying</a>
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
                <a href="">Appointment</a>
            </div>
        </div>
    </div>

    <!-- First section -->

    <section class="identify-first-section">
    <div class="your-container">
        <div class="user-info">
            <div class="user-details">
                <p> <img src="<?php echo isset($fetch['image']) && $fetch['image'] != '' ? 'uploaded_img/'.$fetch['image'] : 'images/default-avatar.png'; ?>" class="user-img"><span class='user'><?php echo $fetch['name']; ?></span></p>
            </div>
        </div>

        <div class="item-box">
            <h1>Major</h1>
            <ul>
                <li class='engine-overhaul'><span class='label'>Engine overhaul:</span> <span class='value'><?php echo $row['eo']; ?></span></li>
                <li class='engine-overhaul'><span class='label'>Engine low power:</span> <span class='value'><?php echo $row['elp']; ?></span></li>
                <li class='engine-overhaul'><span class='label'>Electrical problem:</span> <span class='value'><?php echo $row['ep']; ?></span></li>
            </ul>
        </div>

      
    <div class="item-box">
            <h1>Maintenance</h1>
         <div class="first-maintenance-container">
            <ul>
                <li class='battery'><span class='label'>Battery:</span> <span class='value'><?php echo $row['battery']; ?></span></li>
                <li class='battery'><span class='label'>Lights:</span> <span class='value'><?php echo $row['light']; ?></span></li>
                <li class='battery'><span class='label'>Oil:</span> <span class='value'><?php echo $row['oil']; ?></span></li>
                <li class='battery'><span class='label'>Water:</span> <span class='value'><?php echo $row['water']; ?></span></li>
            </ul>   
          </div> 
        <div class="second-maintenance-container">
            <ul>
                <li class='battery'><span class='label'>Brake:</span> <span class='value'><?php echo $row['brake']; ?></span></li>
                <li class='battery'><span class='label'>Air:</span> <span class='value'><?php echo $row['air']; ?></span></li>
                <li class='battery'><span class='label'>Gas:</span> <span class='value'><?php echo $row['gas']; ?></span></li>
                <li class='battery'><span class='label'>Tire:</span> <span class='value'><?php echo $row['tire']; ?></span></li>
            </ul>
        </div>
    </div>
</div>

    </section>

   <!-- Second section -->
<section class="identify-second-section">
    <form id="repairForm">
        <div class="identify-modal-major">
            <!-- Major options -->
            <div>
                <label for="engineOverhaul">Engine Overhaul:</label>
                <select id="engineOverhaul" name="engineOverhaul">
                    <option value="good">Good</option>
                    <option value="repair">Repair</option>
                </select>
            </div>

            <div>
                <label for="engineLowPower">Engine Low Power:</label>
                <select id="engineLowPower" name="engineLowPower">
                    <option value="good">Good</option>
                    <option value="repair">Repair</option>
                </select>
            </div>

            <div>
                <label for="electricalProblem">Electrical Problem:</label>
                <select id="electricalProblem" name="electricalProblem">
                    <option value="good">Good</option>
                    <option value="repair">Repair</option>
                </select>
            </div>
        </div>

        <!-- Maintenance options -->
        <div class="identify-modal-maintenance">
        <div class="first-column-maintenance">
           <div>
                <label for="battery">Battery:</label>
                <select id="battery" name="battery">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="lights">Light:</label>
                <select id="lights" name="lights">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="oil">Oil:</label>
                <select id="oil" name="oil">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="water">Water:</label>
                <select id="water" name="water">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>
            </div>
            <div class="second-column-maintenance">
            <div>
                <label for="brake">Brake:</label>
                <select id="brake" name="brake">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="air">Air:</label>
                <select id="air" name="air">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="gas">Gas:</label>
                <select id="gas" name="gas">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>

            <div>
                <label for="tire">Tire:</label>
                <select id="tire" name="tire">
                    <option value="good">Good</option>
                    <option value="maintain">Maintain</option>
                </select>
            </div>
           </div>
        </div>

        <button type="button" onclick="showResults()">Show Results</button>
    </form>

    <!-- Results Modal -->
    <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultsModalLabel">Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="engineOverhaulResult"></p>
                    <p id="engineLowPowerResult"></p>
                    <p id="electricalProblemResult"></p>
                    <p id="maintenanceResult"></p>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Display Results -->
<?php
// Code to display results here, based on user choices in the forms.
// You can retrieve results from the database or session and display them.
?>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="nav.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- Modal -->
    <script>
        function showResults() {
    // Get selected values
            var engineOverhaul = document.getElementById("engineOverhaul").value;
            var engineLowPower = document.getElementById("engineLowPower").value;
            var electricalProblem = document.getElementById("electricalProblem").value;
            var battery = document.getElementById("battery").value;
            var lights = document.getElementById("lights").value;
            var oil = document.getElementById("oil").value;
            var water = document.getElementById("water").value;
            var brake = document.getElementById("brake").value;
            var air = document.getElementById("air").value;
            var gas = document.getElementById("gas").value;
            var tire = document.getElementById("tire").value;

            // Display selected values in the modal
            // Major
            document.getElementById("engineOverhaulResult").innerText = "Engine Overhaul: " + engineOverhaul;
            document.getElementById("engineLowPowerResult").innerText = "Engine Low Power: " + engineLowPower;
            document.getElementById("electricalProblemResult").innerText = "Electrical Problem: " + electricalProblem;

            // Maintenance
            document.getElementById("maintenanceResult").innerText += "Battery: " + battery + "\n";
            document.getElementById("maintenanceResult").innerText += "Light: " + lights + "\n";
            document.getElementById("maintenanceResult").innerText += "Oil: " + oil + "\n";
            document.getElementById("maintenanceResult").innerText += "Water: " + water + "\n";
            document.getElementById("maintenanceResult").innerText += "Brake: " + brake + "\n";
            document.getElementById("maintenanceResult").innerText += "Air: " + air + "\n";
            document.getElementById("maintenanceResult").innerText += "Gas: " + gas + "\n";
            document.getElementById("maintenanceResult").innerText += "Tire: " + tire;

    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('resultsModal'));
    myModal.show();
}

    </script>


</body>

</html>

<?php
    }
} else {
        
    header('Location: carprofile.php');
    exit;
  }
$conn->close();
?>

