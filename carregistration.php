<?php
include 'config.php';

session_start(); // Start the session

if (isset($_POST['submit'])) {
    // Check if the necessary form fields are set
    if (
        isset($_POST['plateno']) && isset($_POST['manufacturer']) &&
        isset($_POST['carmodel']) && isset($_POST['year']) &&
        isset($_POST['bodyno']) && isset($_POST['enginecc']) && isset($_POST['gas'])
    ) {
        // Retrieve car details from the form
        $plateno = mysqli_real_escape_string($conn, $_POST['plateno']);
        $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
        $carmodel = mysqli_real_escape_string($conn, $_POST['carmodel']);
        $year = mysqli_real_escape_string($conn, $_POST['year']);
        $bodyno = mysqli_real_escape_string($conn, $_POST['bodyno']);
        $enginecc = mysqli_real_escape_string($conn, $_POST['enginecc']);
        $gas = mysqli_real_escape_string($conn, $_POST['gas']);

        // Check if 'plateno' already exists in the 'car' table
        $check_plateno_query = mysqli_query($conn, "SELECT * FROM car WHERE plateno = '$plateno'");

        if (mysqli_num_rows($check_plateno_query) > 0) {
            // 'plateno' already exists, display an error message or handle accordingly
            $message[] = 'Car with Plate number ' . $plateno . ' already registered.';
        } else {
            // Retrieve user information from the session
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Proceed with the car registration
                $insert = mysqli_query($conn, "INSERT INTO car (plateno, manufacturer, carmodel, year, bodyno, enginecc, gas, user_id) 
                    VALUES('$plateno', '$manufacturer', '$carmodel', '$year', '$bodyno', '$enginecc', '$gas', '$user_id')") or die(mysqli_error($conn));

                if ($insert) {
                    $message[] = 'Register another car!';
                } else {
                    $message[] = 'Registration failed!';
                }
            } else {
                // Session user_id not set, display an error message
                $message[] = 'User information not available. Cannot register the car.';
            }
        }
    } else {
        $message[] = 'All form fields are required.';
    }
}

// Retrieve user information from the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Perform the query to retrieve all cars associated with the user
    $car_select = mysqli_query($conn, "SELECT * FROM car WHERE user_id = '$user_id'");

    // Check if the query was successful
    if (!$car_select) {
        die('Error in car query: ' . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Car</title>

    <!-- Custom CSS file link  -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Script part -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</head>
<body>
    <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data">
            <h3>Register Car</h3>
            <?php
            if (isset($message)) {
                foreach ($message as $message) {
                    echo '<div class="message">' . $message . '</div>';
                }
            }
            ?>
            <!-- Remove the input field for email -->
            <input type="text" name="plateno" placeholder="Enter Plate number" class="box" required>
               
            <select name="manufacturer" placeholder="Enter manufacturer" id="manufacturer" class="box">
                <option>select manufacturer</option>
                <option value="Toyota">Toyota</option>
                <option value="Honda">Honda</option>
                <option value="Suzuki">Suzuki</option>
            </select>
            <select name="carmodel" placeholder="Enter car model" class="box" id="carmodel">
            <option>select car model</option>
            </select>

            <input type="text" name="year" placeholder="Year" class="box" required>
            <input type="text" name="bodyno" placeholder="Enter body number" class="box" required>
           
            <select name="enginecc" placeholder="Enter Engine cc" class="box" required>
                <option value="1000">1000cc</option>
                <option value="1200">1200cc</option>
                <option value="1499">1499cc</option>
            </select>

            <select name="gas" placeholder="Gas" class="box" required>
                <option value="Regular">Regular</option>
                <option value="Premium">Premium</option>
                <option value="Diesel">Diesel</option>
            </select>

            <input type="submit" name="submit" value="Register Now" class="btn">
            <p> Back to <a href="home.php"> home</a></p>
        </form>
    </div>

    <section class="cars-container">
        <div class="container">
            <h2>Cars Registered by You</h2>
            <?php if (isset($car_select) && mysqli_num_rows($car_select) > 0) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Car Name</th>
                            <th>Plate No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are rows to fetch
                        while ($row = mysqli_fetch_assoc($car_select)) :
                        ?>
                            <tr>
                                <td><?php echo isset($row['manufacturer']) ? $row['manufacturer'] : ''; ?></td>
                                <td><?php echo isset($row['plateno']) ? $row['plateno'] : ''; ?></td>
                                <td><a href="carprofile.php?car_id=<?php echo $row['car_id']; ?>">View Profile</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>


    <!-- ... (your existing script section) ... -->
</body>


    
   <!-- <script> -->
   <script>
        // Function to update car model options based on the selected manufacturer
        function updateCarModels() {
            var manufacturer = $('#manufacturer').val();
            var carModels;

            // Define car models based on the selected manufacturer
            switch (manufacturer) {
                case 'Toyota':
                    carModels = ['Fortuner', 'Yaris', 'Innova'];
                    break;
                case 'Honda':
                    carModels = ['Civic', 'Pilot', 'Brio'];
                    break;
                case 'Suzuki':
                    carModels = ['Jimny', 'Vitara', 'Ignis'];
                    break;
                default:
                    carModels = [];
            }

            // Update car model options
            var carModelSelect = $('#carmodel');
            carModelSelect.empty();
            $.each(carModels, function(index, value) {
                carModelSelect.append('<option value="' + value + '">' + value + '</option>');
            });
        }

        // Attach the function to the change event of the manufacturer dropdown
        $(document).ready(function() {
            $('#manufacturer').change(updateCarModels);
        });
    </script>
</body>
</html>
