<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $companyid = mysqli_real_escape_string($conn, $_POST['companyid']);

    $query = "
        SELECT u.id AS user_id, u.firstname, u.middlename, u.lastname, c.car_id, c.carmodel, a.companyname
        FROM user u
        LEFT JOIN car c ON u.id = c.user_id
        LEFT JOIN autoshop a ON u.companyid = a.companyid
        WHERE u.companyid = '$companyid'
          AND (u.firstname LIKE '%$search%' OR u.middlename LIKE '%$search%' 
               OR u.lastname LIKE '%$search%' OR c.carmodel LIKE '%$search%')
    ";

    $result = @mysqli_query($conn, $query);  // Using @ to suppress errors

    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='row'>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "
                <div class='col-md-4 mb-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>{$row['firstname']} {$row['middlename']} {$row['lastname']}</h5>
                            <p class='card-text'><strong>Car Model:</strong> {$row['carmodel']}</p>
                            <button class='btn btn-primary view-details' data-user-id='{$row['user_id']}' data-car-id='{$row['car_id']}'>
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            ";
        }
        echo "</div>";
    } else {
        // Retrieve the company name separately to display it in the message
        $companyQuery = "
            SELECT companyname 
            FROM autoshop 
            WHERE companyid = '$companyid'
        ";
        $companyResult = @mysqli_query($conn, $companyQuery);
        $companyName = '';
        if ($companyResult && mysqli_num_rows($companyResult) > 0) {
            $companyRow = mysqli_fetch_assoc($companyResult);
            $companyName = $companyRow['companyname'];
        }

        echo "
            <div class='alert alert-warning text-center' role='alert'>
                <p>No results found for the given search query.</p>
                <p><strong>Company:</strong> {$companyName}</p>
            </div>
        ";
    }
}
?>


<style>
  .card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    border: none;
    transition: transform 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
  }

  .card-title {
    font-weight: bold;
    font-size: 18px;
  }

  .card-text {
    font-size: 14px;
    color: #555;
  }

  .btn-primary {
    background-color: #007bff;
    border: none;
    transition: background-color 0.3s ease;
  }

  .btn-primary:hover {
    background-color: #0056b3;
  }

  .alert {
    border-radius: 10px;
    font-size: 16px;
  }
</style>

