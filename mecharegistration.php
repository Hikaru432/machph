<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstname = mysqli_real_escape_string($conn, $_POST["firstname"]);
    $middlename = mysqli_real_escape_string($conn, $_POST["middlename"]);
    $lastname = mysqli_real_escape_string($conn, $_POST["lastname"]);
    $employment = mysqli_real_escape_string($conn, $_POST["employment"]);
    $jobrole = implode(", ", $_POST["jobrole"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]); // Added line for email

    // Insert data into the mechadata table
    $sql = "INSERT INTO mechadata (firstname, middlename, lastname, employment, jobrole) 
            VALUES ('$firstname', '$middlename', '$lastname', '$employment', '$jobrole')";

    if ($conn->query($sql) === TRUE) {
        // Retrieve the user ID of the newly inserted record
        $userId = $conn->insert_id;

        // Update the user table to set the role as 'mechanic' and set the email
        $updateUser = "UPDATE user SET role = 'mechanic', email = '$email' WHERE id = $userId";
        if ($conn->query($updateUser) === TRUE) {
            // Redirect to login.php
            header("Location: login.php");
            exit(); // Ensure that no further code is executed after the redirect
        } else {
            echo "Error updating user role and email: " . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!-- ... (remaining HTML code) -->




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mechanic Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/mecharegistration.css">

</head>
<body>
<div class="container mx-auto p-4">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="max-w-md mx-auto bg-custom-color p-6 rounded-md shadow-md mt-10">>
    

        <label for="employment" class="block text-sm font-medium text-gray-600 mt-4">Employment:</label>
        <select name="employment" required class="mt-1 p-2 border border-gray-300 rounded-md w-full">
            <option value="full time">Full Time</option>
            <option value="part time">Part Time</option>
            <option value="intern/temporary">Intern/Temporary</option>
        </select>

        <label for="jobrole[]" class="block text-sm font-medium text-gray-600 mt-4">Job Role(s):</label>
        <select name="jobrole[]" multiple required class="mt-1 p-2 border border-gray-300 rounded-md w-full">
            <option value="General automotive mechanic">General Automotive Mechanic</option>
            <option value="Brake and transmission technicians">Brake and Transmission Technicians</option>
            <option value="Small engine mechanic">Small Engine Mechanic</option>
            <option value="Tire mechanics">Tire Mechanics</option>
        </select>

        <div class="flex justify-between items-center mt-6">
            <input type="submit" value="Register" class="p-2 bg-blue-500 text-white rounded-md cursor-pointer">
            <a href="login.php" class="text-sm text-gray-600 hover:underline cursor-pointer">Back</a>
           
       </div>
    </form>

</div>
</body>
</html>