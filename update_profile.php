<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {

    $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $update_email = mysqli_real_escape_string($conn, $_POST['update_email']);
    $update_firstname = mysqli_real_escape_string($conn, $_POST['update_firstname']);
    $update_middlename = mysqli_real_escape_string($conn, $_POST['update_middlename']);
    $update_lastname = mysqli_real_escape_string($conn, $_POST['update_lastname']);
    $update_homeaddress = mysqli_real_escape_string($conn, $_POST['update_homeaddress']);

    mysqli_query($conn, "UPDATE user SET 
        name = '$update_name',
        email = '$update_email',
        firstname = '$update_firstname',
        middlename = '$update_middlename',
        lastname = '$update_lastname',
        homeaddress = '$update_homeaddress'
        WHERE id = '$user_id'") or die('query failed');

    $old_pass = $_POST['old_pass'];
    $update_pass = mysqli_real_escape_string($conn, md5($_POST['update_pass']));
    $new_pass = mysqli_real_escape_string($conn, md5($_POST['new_pass']));
    $confirm_pass = mysqli_real_escape_string($conn, md5($_POST['confirm_pass']));

    if (!empty($update_pass) || !empty($new_pass) || !empty($confirm_pass)) {
        if ($update_pass != $old_pass) {
            $message[] = 'old password not matched!';
        } elseif ($new_pass != $confirm_pass) {
            $message[] = 'confirm password not matched!';
        } else {
            mysqli_query($conn, "UPDATE user SET password = '$confirm_pass' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'password updated successfully!';
        }
    }

    // Handling image upload as BLOB
    if (!empty($_FILES['update_image']['tmp_name'])) {
        $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
        $update_image = addslashes(file_get_contents($update_image_tmp_name)); // Read image as binary data

        $image_update_query = mysqli_query($conn, "UPDATE user SET image = '$update_image' WHERE id = '$user_id'") or die('query failed');
        if ($image_update_query) {
            $message[] = 'image updated successfully!';
        } else {
            $message[] = 'failed to update image.';
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
    <title>Update Profile</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<div class="update-profile">

    <?php
    $select = mysqli_query($conn, "SELECT * FROM user WHERE id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($select) > 0) {
        $fetch = mysqli_fetch_assoc($select);
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <?php
        // Display current image
        if (empty($fetch['image'])) {
            echo '<img src="images/default-avatar.png" alt="Default Avatar">';
        } else {
            echo '<img src="image.php?id=' . $fetch['id'] . '" alt="User Image" class="img-thumbnail" style="width: 150px; height: 150px;">';
        }
        if (isset($message)) {
            foreach ($message as $message) {
                echo '<div class="message">' . $message . '</div>';
            }
        }
        ?>
        <div class="flex">
            <div class="inputBox">
                <span>username :</span>
                <input type="text" name="update_name" value="<?php echo $fetch['name']; ?>" class="box">
                <span>your email :</span>
                <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box">
                <span>full name:</span>
                <input type="text" name="update_firstname" value="<?php echo $fetch['firstname']; ?>" class="box">
                <span>middle name:</span>
                <input type="text" name="update_middlename" value="<?php echo $fetch['middlename']; ?>" class="box">
                <span>last name:</span>
                <input type="text" name="update_lastname" value="<?php echo $fetch['lastname']; ?>" class="box">
                <input type="hidden" name="old_pass" value="<?php echo $fetch['password']; ?>">
                <span>old password :</span>
                <input type="password" name="update_pass" placeholder="enter previous password" class="box">
                <span>update your pic :</span>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
            </div>
            <div class="inputBox">
                <span>new password :</span>
                <input type="password" name="new_pass" placeholder="enter new password" class="box">
                <span>confirm password :</span>
                <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
                <span>home address:</span>
                <input type="text" name="update_homeaddress" value="<?php echo $fetch['homeaddress']; ?>" class="box">
                <span>barangay:</span>
                <input type="text" name="update_barangay" value="<?php echo $fetch['barangay']; ?>" class="box">
                <span>province:</span>
                <input type="text" name="update_province" value="<?php echo $fetch['province']; ?>" class="box">
                <span>municipality/city:</span>
                <input type="text" name="update_city" value="<?php echo $fetch['municipality']; ?>" class="box">
                <span>Zip code:</span>
                <input type="text" name="update_zipcode" value="<?php echo $fetch['zipcode']; ?>" class="box">
            </div>
        </div>
        <input type="submit" value="update profile" name="update_profile" class="btn">
        <a href="home.php" class="delete-btn">go back</a>
    </form>

</div>

</body>
</html>
