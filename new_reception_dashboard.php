<?php
// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receptionist Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .dashboard-header {
      background-color: #07AF8B;
      color: white;
      padding: 1rem;
    }
    .dashboard-header img {
      height: 40px;
    }
    .card-module {
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }
    .bottom-scanner {
      bottom: 0;
      left: 0;
      right: 0;
      background: #007570;
      color: white;
      padding: 1rem;
      z-index: 1030;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
<?php include 'db.php'; ?>

<div class="dashboard-header d-flex justify-content-between align-items-center px-4">
  <div class="d-flex align-items-center">
    <img src="assets/logo-green-yellow.png" alt="Logo">
    <h4 class="ms-3 mb-0">Receptionist Dashboard</h4>
  </div>
  <div>
    <img src="assets/profile-icon.png" alt="Profile" height="40">
  </div>
</div>

<div class="container mt-4">
  <div class="d-flex justify-content-between mb-3">
    <a href="register_visitor_form.php" class="btn btn-success">+ New Visitor Request</a>
    <input type="text" class="form-control w-50" id="searchVisitor" placeholder="Search visitor by name">
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card-module">
        <h5>Approved Visitors</h5>
        <div id="approvedVisitorsList">
          <?php include 'approved_visitors.php'; ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card-module">
        <h5>Checked-In Visitors</h5>
        <div id="checkedInVisitorsList">
          <?php include 'checkedin_visitors.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card-module">
    <h5>Take Visitor Photo</h5>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#photoModal">📸 Capture Photo</button>
  </div>
</div>

<!-- Modal for taking visitor photo -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Capture Visitor Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label for="visitorDropdown" class="form-label">Select Checked-In Visitor:</label>
        <select class="form-select" id="visitorDropdown">
          <?php include 'dropdown_checkedin.php'; ?>
        </select>
        <div class="mt-3">
          <button class="btn btn-primary">Start Camera</button>
          <button class="btn btn-success">Capture & Save</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- QR Code Scanner Section -->
<div>
  <a href="scanner_page.php"><button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#qrScannerModal">🔍 Scan QR Code</button></a>
</div>
<!--<div class="bottom-scanner fixed-bottom">
  <div class="container py-2">
    <h6 class="mb-2">QR Code Scanner</h6>
    <div id="qr-reader" style="width: 100%">
  
  </div>
  </div> -->
  
</div>







<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("searchVisitor").addEventListener("input", function() {
    const query = this.value;
    if (query.length > 2) {
      fetch(`search_visitor.php?name=${query}`)
        .then(res => res.text())
        .then(data => {
          document.getElementById("approvedVisitorsList").innerHTML = data;
          document.getElementById("checkedInVisitorsList").innerHTML = data;
        });
    }
  });
</script>
</body>
</html>
