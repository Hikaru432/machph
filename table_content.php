<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['companyid']) || empty($_SESSION['companyid'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Check if mechanic_id is passed in the query
if (!isset($_GET['mechanic_id']) || empty($_GET['mechanic_id'])) {
    echo "<script>alert('Mechanic ID is missing!'); window.location.href='homemechanic.php';</script>";
    exit();
}

$mechanic_id = intval($_GET['mechanic_id']); // Ensure it's an integer

// Retrieve car data assigned to the mechanic
$query = "SELECT car.*, user.name AS username, manufacturer.name AS manufacturer_name, mechanic.jobrole, CONCAT(mechanic.firstname, ' - ', mechanic.jobrole) AS assigned_mechanic
          FROM car 
          JOIN user ON car.user_id = user.id
          LEFT JOIN assignments ON car.car_id = assignments.car_id
          LEFT JOIN mechanic ON assignments.mechanic_id = mechanic.mechanic_id
          LEFT JOIN autoshop ON mechanic.companyid = autoshop.companyid
          LEFT JOIN manufacturer ON car.manufacturer_id = manufacturer.id
          WHERE mechanic.mechanic_id = $mechanic_id";

$car_select = mysqli_query($conn, $query);

if (!$car_select) {
    die('Error in car query: ' . mysqli_error($conn));
}
?>

<div class="container-fluid mt-4">
    <div class="card shadow-lg">
        <div class="card-header">
            <h4 class="mb-0 fw-bold">Assigned Vehicles Overview</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="carTable" class="table table-hover custom-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Car Name</th>
                            <th>Plate No</th>
                            <th>Assigned Mechanic</th>
                            <th>Parts</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($car_select)) : ?>
                        <?php
                        // Fetch additional user details using user_id
                        $user_id = $row['user_id'];
                        $user_query = "SELECT * FROM user WHERE id = $user_id";
                        $user_result = mysqli_query($conn, $user_query);
                        $user = mysqli_fetch_assoc($user_result);
                        ?>
                        <tr data-user-id="<?php echo (int)$user_id; ?>" data-car-id="<?php echo (int)$row['car_id']; ?>">
                            <td class="username-cell"><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?></td>
                            <td><?php echo isset($row['manufacturer_name']) ? htmlspecialchars($row['manufacturer_name']) : ''; ?></td>
                            <td><?php echo isset($row['plateno']) ? htmlspecialchars($row['plateno']) : ''; ?></td>
                            <td><?php echo isset($row['assigned_mechanic']) ? htmlspecialchars($row['assigned_mechanic']) : '<span class="badge bg-warning text-dark">Not Assigned</span>'; ?></td>
                            <td>
                                <a href="machvalidate.php?mechanic_id=<?php echo $mechanic_id; ?>&car_id=<?php echo $row['car_id']; ?>&user_id=<?php echo $user_id; ?>" 
                                   class="btn btn-info btn-sm">
                                   <i class="fas fa-tools me-1"></i>Parts
                                </a>
                            </td>
                            <td>
                                <a href="machidentify.php?mechanic_id=<?php echo (int)$mechanic_id; ?>&car_id=<?php echo (int)$row['car_id']; ?>&user_id=<?php echo $user_id; ?>" 
                                   class="btn btn-primary btn-sm">
                                   <i class="fas fa-user me-1"></i>View Profile
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Updated CDN links -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --border-color: #e0e0e0;
        --shadow-color: rgba(0, 0, 0, 0.05);
        --hover-bg: #f9f9f9;
        --text-primary: #2c2c2c;
        --text-secondary: #666;
    }

    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 24px var(--shadow-color);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem 1.5rem;
    }

    .card-header h4 {
        color: var(--text-primary);
        font-size: 1.25rem;
        letter-spacing: -0.5px;
    }

    .card-body {
        padding: 1.5rem;
    }

    .custom-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 0;
    }

    .custom-table thead th {
        background: transparent;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .custom-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .custom-table tbody tr:last-child {
        border-bottom: none;
    }

    .custom-table tbody tr:hover {
        background-color: var(--hover-bg);
        transform: translateY(-1px);
    }

    .custom-table td {
        padding: 1rem;
        vertical-align: middle;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.625rem 1.25rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.1);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .btn:hover::after {
        opacity: 1;
    }

    .btn-info {
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        color: var(--text-primary);
    }

    .btn-info:hover {
        background-color: #eeeeee;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn-primary {
        background-color: #2c2c2c;
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background-color: #404040;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 1em;
        border-radius: 6px;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        background-color: #f5f5f5;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }

    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f5f5f5;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #ccc;
    }

    @media (max-width: 768px) {
        .card {
            border-radius: 12px;
            margin: 0.5rem;
        }

        .card-header {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .custom-table td, 
        .custom-table th {
            padding: 0.75rem;
        }

        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.7rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.4em 0.8em;
        }
    }

    /* Animation for table rows */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .custom-table tbody tr {
        animation: fadeIn 0.3s ease forwards;
    }

    /* Hover effect for the entire card */
    .card {
        transform: perspective(1px) translateZ(0);
        backface-visibility: hidden;
    }

    /* Custom focus styles */
    .btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }
</style>

