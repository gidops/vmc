<?php
session_start();
require 'db_connection.php';

// Load PHPMailer manually
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

  

//just added

// Redirect if not logged in
if (!isset($_SESSION['employee_id'])) {
  header("Location: index.html");
  exit();
}


// Get employee name (either from session or database)
if (isset($_SESSION['name'])) {
  $name = $_SESSION['name'];
} else {
  $employee_id = $_SESSION['employee_id'];
  $stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
  $stmt->bind_param("i", $employee_id);
  $stmt->execute();
  $stmt->bind_result($name);
  $stmt->fetch();
  $stmt->close();
  $_SESSION['name'] = $name; // Store in session for future use
}


// Check if profile is completed
$employee_id = $_SESSION['employee_id'];
$stmt = $conn->prepare("SELECT profile_completed FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$stmt->bind_result($profile_completed);
$stmt->fetch();
$stmt->close();

if ($profile_completed == 0) {
  header("Location: update_profile.php");
  exit();
}



$employee_id = $_SESSION['employee_id'] ?? 0;





if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$notifications = [];
$requests = [];

// Handle new request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
//    $email = $conn->real_escape_string($_POST['email']);
    $organization = $conn->real_escape_string($_POST['organization']);
    $visit_date = $conn->real_escape_string($_POST['visit_date']);
    $purpose = $conn->real_escape_string($_POST['purpose']);
    

    $stmt = $conn->prepare("INSERT INTO purpose (employee_id, name, email, organization, visit_date, purpose, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("isssss", $employee_id, $name, $email, $organization, $visit_date, $purpose);

    $stmt->execute();
    //$stmt->bind_result($name);
    //$stmt->fetch();
    $stmt->close();

    // Optional: Send email using PHPMailer (if configured)
    $mail = new PHPMailer(true);
     try {
         $mail->isSMTP();
            $mail->Host = 'mail.aatcabuja.com.ng';
            $mail->SMTPAuth = true;
            $mail->Username = 'support@aatcabuja.com.ng';
            $mail->Password = 'Dw2bbgvhZmsp7QA';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
         $mail->setFrom('support@aatcabuja.com.ng', 'Abuja-AATC');
         $mail->addAddress($visitor_email);
         $mail->Subject = 'Visitor Request Received';
         $mail->Body = 'Your visit request has been received.';
         $mail->send();
     } catch (Exception $e) {}

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch notifications
$noti_result = $conn->query("SELECT name, updated_at FROM visitors WHERE employee_id = $employee_id AND status = 'approved' ORDER BY updated_at DESC LIMIT 5");
while ($row = $noti_result->fetch_assoc()) {
    $notifications[] = $row;
}




// Get approval statistics
//$stats = $conn->query("SELECT 
  //  COUNT(*) as total_requests,
    //SUM(status = 'approved') as approved,
    //SUM(status = 'denied') as declined
    //FROM visitors WHERE employee_id = $employee_id")->fetch_assoc();




 //Fetch request summary
//$count_result = $conn->query("SELECT status, COUNT(*) as count FROM visitors WHERE employee_id = $employee_id GROUP BY status");
//$summary = ['Approved' => 0, 'Declined' => 0, 'Pending' => 0];
//while ($row = $count_result->fetch_assoc()) {
 //   $summary[$row['status']] = $row['count'];
//}

// Fetch requests
$req_result = $conn->query("SELECT name, visit_date, reason, status, unique_code FROM visitors WHERE employee_id = $employee_id ORDER BY id DESC");
while ($row = $req_result->fetch_assoc()) {
    $requests[] = $row;
}


// Get approval statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total_requests,
    IFNULL(SUM(status = 'approved'), 0) as approved,
    IFNULL(SUM(status = 'rejected'), 0) as declined
    FROM visitors 
    WHERE employee_id = $employee_id")->fetch_assoc();

    

// Get notifications (last 3 status changes)
$notifications = $conn->query("SELECT name, status, updated_at 
    FROM visitors 
    WHERE employee_id = $employee_id AND status IN ('approved','rejected')
    ORDER BY updated_at ASC LIMIT 3");

// Get all requests with search functionality
$search = $_GET['search'] ?? '';
$where = $search ? "AND (name LIKE '%$search%' OR email LIKE '%$search%' OR organization LIKE '%$search%' OR reason LIKE '%$search%')" : "";
$requests = $conn->query("SELECT name as visitor, email, 
    DATE_FORMAT(visit_date, '%d %M %Y') as request_date,
    reason as purpose, 
    status,
    unique_code,
    created_at,
    requested_by_receptionist
    FROM visitors 
    WHERE (employee_id = $employee_id) $where
    ORDER BY created_at DESC");


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - AFREXIMBANK</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <style>
    :root {
      --primary: #07AF8B;
      --accent: #FFCA00;
      --deep: #007570;
      --bg: #f4f6f8;
      --text-dark: #1f2d3d;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg);
      color: var(--text-dark);
    }

    a {
      text-decoration: none;
      color: var(--primary);
    }

    .container {
      padding: 1rem;
      max-width: 1200px;
      margin: auto;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: white;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
      flex-wrap: wrap;
      gap: 1rem;
    }

    .topbar img {
      height: 45px;
    }

    .user-section {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .user-icon {
      font-size: 30px;
      color: var(--deep);
    }

    .new-request-btn {
      background: var(--accent);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: bold;
      color: #;
      cursor: pointer;
      transition: background 0.3s;
    }

    .new-request-btn:hover {
      background: #f0b800;
    }

    .main {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      margin-top: 2rem;
    }

    .left-panel,
    .right-panel {
      flex: 1 1 350px;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .new-request-box {
      background: var(--primary);
      height: 180px;
      border-radius: 12px;
      display: flex;
      justify-content: center;
      align-items: flex-end;
      padding: 1rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .new-request-btn a {
  color: black;
  text-decoration: none;
}

    .notifications,
    .my-requests {
      background: white;
      border-radius: 12px;
      padding: 1rem 1.5rem;
      box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }

    .notifications ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .notifications li {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      font-size: 14px;
    }

    .summary-header {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .tabs {
      display: flex;
      gap: 0.5rem;
    }

    .tabs button {
      border: none;
      background: #eee;
      padding: 0.4rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .tabs .active {
      background: var(--deep);
      color: white;
    }

    .stats {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .stat-card {
      flex: 1 1 100px;
      padding: 1rem;
      border-radius: 12px;
      color: white;
      text-align: center;
      font-weight: bold;
    }

    .gray { background: #6c757d; }
    .green { background: var(--primary); }
    .red { background: #b00020; }

    .my-requests table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th {
      background: var(--primary);
      color: white;
      text-align: left;
      padding: 0.6rem;
    }

    td {
      padding: 0.6rem;
      border-bottom: 1px solid #eee;
    }

    .badge {
      background: var(--primary);
      color: white;
      border-radius: 6px;
      padding: 0.2rem 0.6rem;
      font-size: 12px;
    }

    .search-box {
      padding: 0.4rem;
      border: 0px solid #ccc;
      border-radius: 6px;
      width: 100%;
      margin-top: 1rem;
    }

    .search-box i { position: absolute; left: 15px; top: 10px; color: #6c757d; }


    @media (max-width: 768px) {
      .topbar,
      .user-section {
        flex-direction: column;
        align-items: flex-start;
      }
      .search-box {
        width: 100%;
      }
    }

    .request-content {
  text-align: left;
  color: white;
  max-width: 600px;
}

.request-content h2 {
  font-size: 20px;
  margin-bottom: 0.5rem;
}

.request-content p {
  font-size: 14px;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.new-request-action {
  display: inline-block;
  background: var(--accent);
  color: #000;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  font-weight: bold;
  text-decoration: none;
  transition: background 0.3s;
}

.new-request-action:hover {
  background: #f0b800;
}

.scroll-box {
  max-height: 170px; /* Adjust depending on your row height */
  overflow-y: auto;
}

.accent { background: var(--accent); color: #000; }


/* Add this to your existing CSS */
.request-content {
        text-align: left;
        color: white;
        max-width: 100%; /* Changed from 600px to 100% */
        padding: 0 1rem; /* Add padding to prevent text from touching edges */
        box-sizing: border-box; /* Include padding in width calculation */
    }

    .request-content h2 {
        font-size: clamp(16px, 4vw, 20px); /* Responsive font size */
        margin-bottom: 0.5rem;
        word-wrap: break-word; /* Ensure long words break */
        overflow-wrap: break-word; /* Alternative for better browser support */
    }

    .request-content p {
        font-size: clamp(12px, 3vw, 14px); /* Responsive font size */
        margin-bottom: 1rem;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .new-request-box {
        background: var(--primary);
        min-height: 180px; /* Changed from height to min-height */
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    @media (max-width: 480px) {
        .request-content {
            padding: 0 0.5rem; /* Smaller padding on very small screens */
        }
        
        .request-content h2 {
            line-height: 1.3; /* Tighter line height on small screens */
        }
    }

/* Modal styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background-color: white;
  margin: 10% auto;
  padding: 20px;
  border-radius: 8px;
  width: 80%;
  max-width: 600px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover {
  color: black;
}

.modal-body {
  margin-top: 20px;
}

.modal-row {
  display: flex;
  margin-bottom: 10px;
}

.modal-label {
  font-weight: bold;
  width: 150px;
  color: var(--deep);
}

.modal-value {
  flex: 1;
}

.clickable-row {
  cursor: pointer;
}

.clickable-row:hover {
  background-color: #f5f5f5;
}

.status-pending {
  color: #FFA500;
}
.status-approved {
  color: #07AF8B;
}
.status-rejected {
  color: #b00020;
}
.status-checked_in {
  color: #07AF8B;
}
.status-checked_out {
  color: #6c757d;
}

  </style>
</head>
<body>

<div class="topbar">
  <img src="assets/logo-green-yellow.png" alt="Logo" />
  <div class="user-section">
    <a href="register_visitor_form.php">New request</a>
    <a href="update_profile_logged_in.php"><span class="material-icons user-icon">account_circle</span></a>
    <div class="user-info">
      <strong><?= htmlspecialchars($name) ?></strong><br>
      Location: <strong>Abuja</strong>
    </div>
    <form method="post" action="logout.php"><button class="new-request-btn">Logout</button></form>
  </div>
</div>

<div class="container">
  <div class="main">
    <div class="left-panel">
    <div class="new-request-box" id="request-form">
  <div class="request-content">
    <h2>Book visitors into Abuja AATC facilities</h2>
    <p>Request for entry permit for business associates and partners from the comfort of your office and get real-time notifications on the progress of your request.</p>
    <button class="new-request-btn" name="submit_request"><a href="register_visitor_form.php">New Request</a></button>
  </div>
</div>

      

      <div class="notifications">
        <h5>Notifications</h5>
        <ul>
          <?php foreach ($notifications as $note): ?>
            <li><span><span class="material-icons" style="color: var(--primary);">check_circle</span> <?= htmlspecialchars($note['name']) ?> approved.</span><span class="timestamp"><?= date("g:iA\<\b\\r\>d/m/Y", strtotime($note['updated_at'])) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="right-panel">
     <!-- <div class="summary-header">Summary</div> -->
      <div class="tabs">
        <!--<button class="active">PERSONAL</button>-->
        <!--<button>APPROVAL</button> -->
      </div>
      <div class="stats">
        <div class="stat-card accent"><h1><?= $stats['total_requests'] ?? 0 ?></h1><p>Requests</p></div>
        <div class="stat-card green"><h1><?php echo $stats['approved']; ?></h1><p>Approved</p></div>
        <div class="stat-card gray"><h1><?php echo $stats['declined']; ?></h1><p>Declined</p></div>
      </div>

      <div class="my-requests">
        <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">   
        
    <h5>My Requests</h5>
    
    <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem;">
         <input type="text" name="search" class="search-box" placeholder="Search visitors..." 
           value="<?= htmlspecialchars($search) ?>">
            <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer;">
             Search
            </button>
    <?php if ($search): ?>
        <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" style="color: var(--primary);">Clear</a>
    <?php endif; ?>
    </form>

        </div>

                        <div class="scroll-box">
  <table>
    <thead>
      <tr>
        <th>Visitor</th>
        <th>Date</th>
        <th>Purpose</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($requests->num_rows > 0): ?>
    
        <?php 
        // Reset pointer to beginning for the modal data fetch
        $requests->data_seek(0);
        while($req = $requests->fetch_assoc()): 
            // Get full visitor details for the modal
            $visitor_id = $req['unique_code'];
            $visitor_stmt = $conn->prepare("SELECT * FROM visitors WHERE unique_code = ?");
            $visitor_stmt->bind_param("s", $visitor_id);
            $visitor_stmt->execute();
            $visitor_result = $visitor_stmt->get_result();
            $visitor_details = $visitor_result->fetch_assoc();
            $visitor_stmt->close();
        ?>
        <tr class="clickable-row" onclick="openModal(<?= htmlspecialchars(json_encode($visitor_details), ENT_QUOTES, 'UTF-8') ?>)">
            <td>
                <?= htmlspecialchars($req['visitor']) ?><br>
                <small style="color: #666; font-size: 0.8em;">
                    Code: <?= htmlspecialchars($req['unique_code'] ?? 'N/A') ?>
                </small>
            </td>
            <td><?= htmlspecialchars($req['request_date']) ?></td>
            <td>
  <?= htmlspecialchars($req['purpose']) ?>
  <?php if (!empty($req['requested_by_receptionist'])): ?>
    <span class="badge" style="background: #ffca00; color: black; margin-left: 5px;">By Reception</span>
  <?php endif; ?>
</td>
            <td class="status-<?= strtolower($req['status']) ?>">
                <?= str_replace('_', ' ', ucwords(strtolower($req['status']), '_')) ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center text-muted">No requests found</td>
        </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal for visitor details -->
<div id="visitorModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Visitor Details</h3>
    <div class="modal-body" id="modalBody">
      <!-- Content will be inserted here by JavaScript -->
    </div>
  </div>
</div>

      </div>
    </div>
  </div>
</div>

<script>
// Polling interval in milliseconds (5 seconds)
const POLL_INTERVAL = 5000;
let pollTimeout;

// Function to fetch and update dashboard data
function pollDashboard() {
    fetch('poll_updates.php')
        .then(response => response.json())
        .then(data => {
            // Update notifications
            updateNotifications(data.notifications);
            
            // Update requests table
            updateRequestsTable(data.requests);
            
            // Update stats
            updateStats(data.stats);
            
            // Schedule next poll
            pollTimeout = setTimeout(pollDashboard, POLL_INTERVAL);
        })
        .catch(error => {
            console.error('Polling error:', error);
            // Retry after delay even if error occurs
            pollTimeout = setTimeout(pollDashboard, POLL_INTERVAL);
        });
}

// Function to update notifications
function updateNotifications(notifications) {
    const notificationsList = document.querySelector('.notifications ul');
    
    // Clear existing notifications
    notificationsList.innerHTML = '';
    
    // Add new notifications
    notifications.forEach(note => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>
                <span class="material-icons" style="color: var(--primary);">
                    ${note.status === 'approved' ? 'check_circle' : 'cancel'}
                </span> 
                ${note.name} ${note.status}.
            </span>
            <span class="timestamp">${formatTime(note.updated_at)}</span>
        `;
        notificationsList.appendChild(li);
    });
}

// Function to update requests table
function updateRequestsTable(requests) {
    const requestsTable = document.querySelector('.my-requests tbody');
    
    // Clear existing requests
    requestsTable.innerHTML = '';
    
    // Add new requests
    if (requests.length > 0) {
        requests.forEach(req => {
            const row = document.createElement('tr');
            row.className = 'clickable-row';
            row.onclick = function() {
                // Get full visitor details for modal
                fetchVisitorDetails(req.unique_code);
            };
            
            row.innerHTML = `
                <td>
                    ${req.visitor}<br>
                    <small style="color: #666; font-size: 0.8em;">
                        Code: ${req.unique_code || 'N/A'}
                    </small>
                </td>
                <td>${req.request_date}</td>
                <td>${req.purpose}</td>
                <td class="status-${req.status ? req.status.toLowerCase() : 'pending'}">
    ${req.status ? 
        (req.status.toLowerCase() === 'checked_in' ? 'Checked In' :
         req.status.toLowerCase() === 'checked_out' ? 'Checked Out' :
         req.status.charAt(0).toUpperCase() + req.status.slice(1).toLowerCase()) 
     : 'Pending'}
</td>


            `;
            requestsTable.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="5" class="text-center text-muted">No requests found</td>
        `;
        requestsTable.appendChild(row);
    }
}

// Function to update statistics
function updateStats(stats) {
    // Update stats cards
    document.querySelector('.stat-card.accent h1').textContent = stats.total_requests || 0;
    document.querySelector('.stat-card.green h1').textContent = stats.approved || 0;
    document.querySelector('.stat-card.gray h1').textContent = stats.declined || 0;
}

// Function to fetch visitor details for modal
function fetchVisitorDetails(uniqueCode) {
    fetch(`get_visitor.php?code=${uniqueCode}`)
        .then(response => response.json())
        .then(visitor => openModal(visitor))
        .catch(error => console.error('Error fetching visitor details:', error));
}

// Helper function to format time
function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) + 
        '<br>' + date.toLocaleDateString('en-US', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    pollDashboard();
    
    // Also poll when page becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // If page becomes visible, poll immediately
            clearTimeout(pollTimeout);
            pollDashboard();
        }
    });
});

// Clean up polling when leaving page
window.addEventListener('beforeunload', function() {
    clearTimeout(pollTimeout);
});
</script>

</body>
</html>

<script>
// Function to open modal with visitor details
function openModal(visitor) {
  const modal = document.getElementById('visitorModal');
  const modalBody = document.getElementById('modalBody');
  
  // Format the check-in and check-out times if they exist
  // Modify the formatTime function to accept a type parameter
const formatTime = (time, type) => {
  if (time) {
    return new Date(time).toLocaleString();
  } else {
    return type === 'check_in' ? 'Not checked in' : 'Not checked out';
  }
};
   
  // Create the modal content
  modalBody.innerHTML = `
    <div class="modal-row">
      <div class="modal-label">Visitor Name:</div>
      <div class="modal-value">${visitor.name || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Phone:</div>
      <div class="modal-value">${visitor.phone || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Email:</div>
      <div class="modal-value">${visitor.email || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Organization:</div>
      <div class="modal-value">${visitor.organization || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Visit Date:</div>
      <div class="modal-value">${visitor.visit_date || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Purpose:</div>
      <div class="modal-value">${visitor.reason || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Floor:</div>
      <div class="modal-value">${visitor.floor_of_visit || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Time of Visit:</div>
      <div class="modal-value">${visitor.time_of_visit || 'N/A'}</div>
    </div>
    <div class="modal-row">
      <div class="modal-label">Status:</div>
      <div class="modal-value status-${visitor.status ? visitor.status.toLowerCase() : 'pending'}">
  ${visitor.status ? 
     (visitor.status.toLowerCase() === 'checked_in' ? 'Checked In' : 
      visitor.status.charAt(0).toUpperCase() + visitor.status.slice(1).toLowerCase()) 
   : 'Pending'}
</div>
    </div>
    <div class="modal-row">
    <div class="modal-label">Check-in Time:</div>
    <div class="modal-value">${formatTime(visitor.check_in_time, 'check_in')}</div>
  </div>
  <div class="modal-row">
    <div class="modal-label">Check-out Time:</div>
    <div class="modal-value">${formatTime(visitor.check_out_time, 'check_out')}</div>
  </div>
    <div class="modal-row">
      <div class="modal-label">Unique Code:</div>
      <div class="modal-value">${visitor.unique_code || 'N/A'}</div>
    </div>
  `;
  
  modal.style.display = 'block';
}

// Function to close modal
function closeModal() {
  document.getElementById('visitorModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById('visitorModal');
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}
</script>