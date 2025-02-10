<?php
session_start();
include 'config.php';

if (!isset($_SESSION['companyid'])) {
    header('location:index.php');
    exit();
}

$companyid = $_SESSION['companyid'];

// Query to get company data
$query = "SELECT * FROM autoshop WHERE companyid = '$companyid'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $company_data = mysqli_fetch_assoc($result);
    $company_role = $company_data['role'];
} else {
    echo "<script>alert('Company data not found!');</script>";
    exit();
}

// Fetch all mechanics for the company
$mechanic_query = "SELECT * FROM mechanic WHERE companyid = '$companyid'";
$mechanic_result = mysqli_query($conn, $mechanic_query);

// Updated metrics queries for dashboard
$done_repair_count_query = "
    SELECT COUNT(DISTINCT a.user_id, a.car_id) AS done_count 
    FROM accomplishtask a
    INNER JOIN service s ON a.user_id = s.user_id AND a.car_id = s.car_id
    WHERE a.progress_percentage = 90 
    AND s.companyid = '$companyid'
    AND NOT EXISTS (
        SELECT 1 FROM service s2 
        WHERE s2.user_id = a.user_id 
        AND s2.car_id = a.car_id 
        AND s2.date_created > a.progress_date
    )
";

$progressing_count_query = "
    SELECT COUNT(DISTINCT a.user_id, a.car_id) AS progressing_count 
    FROM accomplishtask a
    INNER JOIN service s ON a.user_id = s.user_id AND a.car_id = s.car_id
    WHERE a.progress_percentage < 90 
    AND s.companyid = '$companyid'
";

$revisit_count_query = "
    SELECT COUNT(DISTINCT s.user_id, s.car_id) as revisit_count
    FROM service s
    INNER JOIN accomplishtask a ON s.user_id = a.user_id 
        AND s.car_id = a.car_id
    WHERE s.companyid = '$companyid'
        AND a.progress_percentage = 90
        AND s.date_created > a.progress_date
";

$done_repair_count = mysqli_fetch_assoc(mysqli_query($conn, $done_repair_count_query))['done_count'] ?? 0;
$progressing_count = mysqli_fetch_assoc(mysqli_query($conn, $progressing_count_query))['progressing_count'] ?? 0;
$revisit_count = mysqli_fetch_assoc(mysqli_query($conn, $revisit_count_query))['revisit_count'] ?? 0;

// Updated query for completed tasks chart (last 7 days)
$completed_tasks_query = "
    SELECT 
        DATE(a.progress_date) as date,
        COUNT(DISTINCT a.user_id, a.car_id) as count
    FROM accomplishtask a
    INNER JOIN service s ON a.user_id = s.user_id AND a.car_id = s.car_id
    WHERE a.progress_percentage = 90 
    AND s.companyid = '$companyid'
    AND a.progress_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    AND NOT EXISTS (
        SELECT 1 FROM service s2 
        WHERE s2.user_id = a.user_id 
        AND s2.car_id = a.car_id 
        AND s2.date_created > a.progress_date
    )
    GROUP BY DATE(a.progress_date)
    ORDER BY date ASC
";

// Updated query for revisits chart (last 6 months)
$revisit_query = "
    SELECT 
        MONTH(s.date_created) as month,
        COUNT(DISTINCT s.user_id, s.car_id) as revisit_count
    FROM service s
    INNER JOIN accomplishtask a ON s.user_id = a.user_id 
        AND s.car_id = a.car_id
    WHERE s.companyid = '$companyid'
        AND a.progress_percentage = 90
        AND s.date_created > a.progress_date
        AND s.date_created >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY MONTH(s.date_created)
    ORDER BY month ASC
";

$completed_result = mysqli_query($conn, $completed_tasks_query);
$revisit_result = mysqli_query($conn, $revisit_query);

// Initialize arrays for the last 7 days (completed tasks)
$last_7_days = array();
$completed_counts = array();
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $last_7_days[date('M d', strtotime($date))] = 0;
}

// Fill in the actual completed tasks data
while ($row = mysqli_fetch_assoc($completed_result)) {
    $date_key = date('M d', strtotime($row['date']));
    $last_7_days[$date_key] = (int)$row['count'];
}

// Initialize arrays for the last 6 months (revisits)
$last_6_months = array();
$revisit_counts = array();
for ($i = 5; $i >= 0; $i--) {
    $month = date('F', strtotime("-$i months"));
    $last_6_months[$month] = 0;
}

// Fill in the actual revisit data
while ($row = mysqli_fetch_assoc($revisit_result)) {
    $month_key = date('F', mktime(0, 0, 0, $row['month'], 1));
    $last_6_months[$month_key] = (int)$row['revisit_count'];
}

// Convert to JSON for JavaScript
$completed_dates_json = json_encode(array_keys($last_7_days));
$completed_counts_json = json_encode(array_values($last_7_days));
$revisit_months_json = json_encode(array_keys($last_6_months));
$revisit_counts_json = json_encode(array_values($last_6_months));

// Add this after your existing metrics queries
$survey_categories_query = "
    SELECT 
        category,
        AVG(rating) as avg_rating,
        COUNT(*) as total_responses
    FROM scale 
    WHERE autoshop_id = '$companyid'
    GROUP BY category
";

$categories_result = mysqli_query($conn, $survey_categories_query);

// Initialize arrays for survey data
$categories = array();
$ratings = array();
$total_rating = 0;
$category_count = 0;

while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
    $ratings[] = round($row['avg_rating'], 2);
    $total_rating += $row['avg_rating'];
    $category_count++;
}

// Calculate overall satisfaction percentage
$overall_satisfaction = $category_count > 0 ? 
    round(($total_rating / $category_count) * 20, 0) : 0; // Converting to percentage (0-5 scale to 0-100%)

// Convert to JSON for JavaScript
$categories_json = json_encode($categories);
$ratings_json = json_encode($ratings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            background-color: #f8f9fa; 
            padding-top: 60px; /* Add padding to prevent content from going under navbar */
        }
        .sidebar {
            width: 250px; 
            height: 100vh; 
            background-color: #343a40;
            padding-top: 20px; 
            position: fixed;
            top: 60px; /* Start below navbar */
            left: 0;
            overflow-y: auto;
        }
        .sidebar a {
            color: white; 
            padding: 10px 20px; 
            display: block; 
            text-decoration: none;
        }
        .sidebar a:hover { 
            background-color: #495057; 
        }
        .content { 
            margin-left: 270px; 
            padding: 20px;
            margin-top: 20px; /* Add some space below navbar */
        }
        .mechanic-list-container {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 280px;
            background: white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            padding: 20px;
            z-index: 1000;
        }
        .mechanic-list-container h5 {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .mechanic-list {
            max-height: calc(100vh - 180px);
            overflow-y: auto;
            padding: 0;
            margin: 0;
        }
        .mechanic-list::-webkit-scrollbar {
            width: 6px;
        }
        .mechanic-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .mechanic-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        .mechanic-list .list-group-item {
            text-align: left;
            padding: 12px 15px;
            margin-bottom: 8px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .mechanic-list .list-group-item:hover {
            background-color: #e9ecef;
            transform: translateX(-5px);
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .mechanic-list .list-group-item a {
            color: #495057;
            text-decoration: none;
            display: block;
            font-weight: 500;
        }
        .mechanic-list .list-group-item:last-child {
            margin-bottom: 0;
        }
        /* Add smooth scrollbar for Firefox */
        .mechanic-list {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
        .card { 
            transition: transform 0.2s; 
        }
        .card:hover { 
            transform: translateY(-5px); 
        }
        .chart-container { 
            position: relative; 
            margin: auto; 
            height: 300px; 
        }
        .navbar {
            background: linear-gradient(to bottom, #343a40, #23272b) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 500;
            color: white !important;
            padding: 0.5rem 0;
        }
        /* Update the container margin to give space for mechanic list */
        .container {
            margin-right: 320px; /* Increased margin to prevent overlap with mechanic list */
            margin-left: 270px; /* Keep existing left margin for sidebar */
            padding: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark px-3 fixed-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Company Dashboard</span>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="admin.php">Dashboard</a>
            <a href="addproduct.php?companyid=<?php echo $companyid; ?>&role=<?php echo $company_role; ?>">Product</a>
            <a href="homemanager.php?companyid=<?php echo $companyid; ?>">Service Executive</a>
            <a href="add_staff.php?companyid=<?php echo $companyid; ?>">Staff</a>
            <a href="revisit.php?companyid=<?php echo $companyid; ?>">Monitoring</a>
            <a href="dashscale.php?companyid=<?php echo $companyid; ?>">Survey</a>
            <a href="sales.php?companyid=<?php echo $companyid; ?>">Sales</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="container mt-4">
            <h1 class="display-6 text-center">
                Welcome, <?php echo isset($company_data['companyname']) ? htmlspecialchars($company_data['companyname']) : 'Valued Partner'; ?>!
            </h1>
            <p class="lead text-center">Manage your business effectively with our dashboard.</p>
            
            <div class="row text-white mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Completed Repairs</h5>
                            <p class="card-text h2"><?php echo $done_repair_count; ?></p>
                            <small>First-time completed repairs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">In Progress</h5>
                            <p class="card-text h2"><?php echo $progressing_count; ?></p>
                            <small>Current ongoing repairs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Total Revisits</h5>
                            <p class="card-text h2"><?php echo $revisit_count; ?></p>
                            <small>Vehicles returned for service</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📊 Completed Tasks Trend</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="areaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📊 Monthly Revisits</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Customer Satisfaction Survey Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container" style="position: relative; height:300px;">
                                        <canvas id="surveyChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Rating</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                for ($i = 0; $i < count($categories); $i++) {
                                                    $percentage = round($ratings[$i] * 20, 0); // Convert to percentage
                                                    echo "<tr>
                                                        <td>{$categories[$i]}</td>
                                                        <td>{$ratings[$i]}</td>
                                                        <td>{$percentage}%</td>
                                                    </tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mechanic-list-container">
            <h5>Mechanic List</h5>
            <ul class="list-group mechanic-list">
                <?php
                if ($mechanic_result && mysqli_num_rows($mechanic_result) > 0) {
                    while ($mechanic = mysqli_fetch_assoc($mechanic_result)) {
                        echo '<li class="list-group-item">
                            <a href="homemechanic.php?mechanic_id=' . htmlspecialchars($mechanic['mechanic_id']) . '" 
                               class="text-decoration-none text-dark">
                                ' . htmlspecialchars($mechanic['firstname'] . ' ' . $mechanic['lastname']) . '
                            </a>
                        </li>';
                    }
                } else {
                    echo '<li class="list-group-item">No mechanics found</li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <script>
        // Area Chart for Completed Tasks
        var ctx1 = document.getElementById('areaChart').getContext('2d');
        var areaChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo $completed_dates_json; ?>,
                datasets: [{
                    label: 'Completed Tasks',
                    data: <?php echo $completed_counts_json; ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Completed Tasks (Last 7 Days)',
                        font: { size: 14 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Bar Chart for Monthly Revisits
        var ctx2 = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo $revisit_months_json; ?>,
                datasets: [{
                    label: 'Revisits',
                    data: <?php echo $revisit_counts_json; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Revisits (Last 6 Months)',
                        font: { size: 14 }
                    },
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Survey Chart
        var ctxSurvey = document.getElementById('surveyChart').getContext('2d');
        var surveyChart = new Chart(ctxSurvey, {
            type: 'doughnut',
            data: {
                labels: <?php echo $categories_json; ?>,
                datasets: [{
                    data: <?php echo $ratings_json; ?>,
                    backgroundColor: [
                        'rgba(153, 102, 255, 0.8)', // Purple for Tangibles
                        'rgba(54, 162, 235, 0.8)',  // Blue for Reliability
                        'rgba(255, 206, 86, 0.8)',  // Yellow for Empathy
                        'rgba(255, 99, 132, 0.8)',  // Pink for Responsiveness
                        'rgba(75, 192, 192, 0.8)'   // Turquoise for Assurance
                    ],
                    borderWidth: 1,
                    borderColor: 'white'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: '<?php echo $overall_satisfaction; ?>%',
                        padding: {
                            top: 10,
                            bottom: 0
                        },
                        font: {
                            size: 24,
                            weight: 'bold'
                        }
                    },
                    subtitle: {
                        display: true,
                        text: 'Overall Satisfaction',
                        padding: {
                            top: 0,
                            bottom: 10
                        },
                        font: {
                            size: 14,
                            weight: 'normal'
                        },
                        color: '#666'
                    }
                }
            }
        });
    </script>

</body>
</html>