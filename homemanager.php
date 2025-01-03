<?php
session_start();
include 'config.php';

// Check if the user is logged in and companyid is set
if (!isset($_SESSION['companyid'])) {
    header('location:admin.php');
    exit();
}

$companyid = $_SESSION['companyid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-black">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin.php?companyid=<?php echo $companyid; ?>">SE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="admin.php?companyid=<?php echo $companyid; ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" aria-current="page" href="#">Notifications<span id="notification-badge" class="badge bg-danger">0</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-white" data-bs-toggle="modal" data-bs-target="#detailsModal" href="#">Details</a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<div id="table-content-placeholder" class="container mt-5"></div>
<!-- Modal for Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Search Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" id="searchInput" class="form-control" placeholder="Input a Name">
          <button class="btn btn-primary" id="searchButton">Search</button>
        </div>
        <div id="searchResults">
          <!-- Search results will be dynamically displayed here -->
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('searchButton').addEventListener('click', function() {
    const searchQuery = document.getElementById('searchInput').value.trim();
    const companyid = "<?php echo $companyid; ?>";

    if (searchQuery) {
      fetch('search_details.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `search=${encodeURIComponent(searchQuery)}&companyid=${companyid}`
      })
        .then(response => response.text())
        .then(data => {
          document.getElementById('searchResults').innerHTML = data;
        });
    } else {
      alert('Please enter a search query.');
    }
  });

 // Add click event to dynamically load details page
document.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('view-details')) {
    const userId = e.target.getAttribute('data-user-id');
    const carId = e.target.getAttribute('data-car-id');
    const companyid = "<?php echo $companyid; ?>"; 

    window.location.href = `view_user_car_details.php?user_id=${userId}&car_id=${carId}&companyid=${companyid}`;
  }
});
</script>

<script>
    $(document).ready(function() {
        function loadTableContent() {
            $.get('table_content_manager.php', function(data) {
                $('#table-content-placeholder').html(data);
            }).fail(function() {
                console.log('Failed to load table content');
            });
        }

        loadTableContent();

        function reloadTable() {
            $.get('table_content_manager.php', function(data) {
                var oldRowCount = $('#carTable tbody tr').length;
                $('#table-content-placeholder').html(data);
                var newRowCount = $('#carTable tbody tr').length;
                var newRowsCount = newRowCount - oldRowCount;
                if (newRowsCount > 0) {
                    $('#notification-badge').text(newRowsCount);
                    // Highlight new rows
                    var newRows = $('#carTable tbody tr:lt(' + newRowsCount + ')');
                    newRows.addClass('highlighted');
                    setTimeout(function(){
                        newRows.removeClass('highlighted');
                    }, 5000); // Highlight remains for 5 seconds (5000 milliseconds)
                }
            }).fail(function() {
                console.log('Failed to load table content');
            });
        }

        setInterval(reloadTable, 10000);

        // Notification badge click event
        $('#notification-badge').click(function() {
            // Clear notification badge
            $('#notification-badge').text('0');
        });
    });
</script>
<style>
    .highlighted {
        background-color: yellow; 
    }
</style>

</body>
</html>
