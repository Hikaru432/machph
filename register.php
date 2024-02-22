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
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;
    $role = 'user';

    // Check if the mechanic checkbox is checked
    if (isset($_POST['isMechanic'])) {
        $employment = mysqli_real_escape_string($conn, $_POST['employment']);
        $jobrole = mysqli_real_escape_string($conn, $_POST['jobrole']);
        $role = 'mechanic'; // Set role to 'mechanic'
    
        // Insert user details into the user table
        $insert_user = mysqli_query($conn, "INSERT INTO user (name, email, password, image, firstname, middlename, lastname, homeaddress, barangay, province, municipality, zipcode, role) VALUES ('$name', '$email', '$pass', '$image', '$firstname', '$middlename', '$lastname', '$homeaddress', '$barangay', '$province', '$municipality', '$zipcode', '$role')") or die('user query failed');
    
        // For user_id
        $user_id = mysqli_insert_id($conn);
        $insert_mechanic = mysqli_query($conn, "INSERT INTO mechanic (user_id, employment, jobrole) VALUES ('$user_id', '$employment', '$jobrole')") or die('mechanic query failed');
    
        // For mechanic_id specific update to table
        $mechanic_id = mysqli_insert_id($conn);
        $update_user = mysqli_query($conn, "UPDATE user SET mechanic_id = '$mechanic_id' WHERE id = '$user_id'") or die('update user query failed');
    }
    
    // Manager
    if (isset($_POST['isManager'])) {
        $managerRole = mysqli_real_escape_string($conn, $_POST['managerRole']);
        $role = 'manager'; // Set role to 'manager'
    }

    // Check if the user already exists
    $select = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'") or die('query failed');

    if (mysqli_num_rows($select) > 0) {
        $message[] = 'User already exists';
    } else {
        // Additional validation checks
        if ($pass != $cpass) {
            $message[] = 'Confirm password not matched!';
        } elseif ($image_size > 2000000) {
            $message[] = 'Image size is too large!';
        } else {
            // Insert user details into the database
            $insert = mysqli_query($conn, "INSERT INTO user (name, email, password, image, firstname, middlename, lastname, homeaddress, barangay, province, municipality, zipcode, role, manager_role)
            VALUES ('$name', '$email', '$pass', '$image', '$firstname', '$middlename', '$lastname', '$homeaddress', '$barangay', '$province', '$municipality', '$zipcode', '$role', '$managerRole')") or die('query failed');         
            if ($insert) {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Registered successfully!';
                header('location:login.php');
            } else {
                $message[] = 'Registration failed!';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<div class="form-container">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>register now</h3>
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>
      <div class="form-column form-column-1">
            <input type="text" name="homeaddress" placeholder="Enter home address" class="box" required>
            <input type="text" name="barangay" placeholder="Enter barangay" class="box" required>
            <input type="text" name="municipality" placeholder="Enter city" class="box" required>
            <input type="text" name="province" placeholder="Enter province" class="box" required>
            <input type="text" name="zipcode" placeholder="Enter zip code" class="box" required>
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">

            <input type="checkbox" name="isMechanic" id="isMechanic">
            <label for="isMechanic">Register as Mechanic</label>
            <div id="mechanicFields" style="display:none;">
               <select name="employment" class="box">
                  <option value="full_time">Full Time</option>
                  <option value="part_time">Part Time</option>
                  <option value="intern_temporary">Intern/Temporary</option>
               </select>

               <!-- Modify the input field to a select dropdown for jobrole -->
               <select name="jobrole" class="box" required>
                  <option value="Automotive mechanic">Automotive mechanic</option>
                  <option value="Brake technicians">Brake technicians</option>
                  <option value="Small engine mechanic">Small engine mechanic</option>
                  <option value="Tire mechanics">Tire mechanics</option>
               </select>
         </div>
         <br>
         <input type="checkbox" name="isManager" id="isManager">
                  <label for="isManager">Register as Manager</label>
                  <div id="managerFields" style="display:none;">
                     <select name="managerRole" class="box" required>
                        <option value="vehicle_inspector">Vehicle Inspector</option>
                        <option value="auto_electrician">Auto Electrician</option>
                        <option value="auto_sales_manager">Auto Sales Manager</option>
                     </select>
                  </div>
         </div>
         
         <div class="form-column form-column-2">
            <input type="text" name="name" placeholder="Enter username" class="box" required>
            <input type="email" name="email" placeholder="Enter email" class="box" required>
            <input type="password" name="password" placeholder="Enter password" class="box" required>
            <input type="password" name="cpassword" placeholder="Confirm password" class="box" required>
            <input type="text" name="firstname" placeholder="Enter first name" class="box" required>
            <input type="text" name="middlename" placeholder="Enter middle name" class="box" required>
            <input type="text" name="lastname" placeholder="Enter last name" class="box" required>
            <input type="submit" name="submit" value="register now" class="btn">
            <p>already have an account? <a href="login.php">login now</a></p>
       </div>
   </form>
</div>

<script>
    document.getElementById('isMechanic').addEventListener('change', function () {
        var mechanicFields = document.getElementById('mechanicFields');
        mechanicFields.style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('isManager').addEventListener('change', function () {
    var managerFields = document.getElementById('managerFields');
    managerFields.style.display = this.checked ? 'block' : 'none';
});

</script>

</body>
</html>
