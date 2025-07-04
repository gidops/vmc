<?php
session_start();
require 'db_connection.php';

// Manually load PHPMailer (without composer)
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$employee_id = $_SESSION['employee_id'] ?? 0;

// DB Connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $organization = $conn->real_escape_string($_POST['organization']);
    $visit_date = $conn->real_escape_string($_POST['visit_date']);
    $purpose = $conn->real_escape_string($_POST['purpose']);
    
    // Insert with pending status
    $stmt = $conn->prepare("INSERT INTO visitors (name, email, organization, visit_date, reason, employee_id, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssssi", $name, $email, $organization, $visit_date, $purpose, $employee_id);
    
    if ($stmt->execute()) {
        // Send email notification
        sendVisitorEmail(
            $email, 
            $name, 
            $visit_date, 
            $purpose,
            'pending'
        );
        
        $_SESSION['success'] = "Visitor request submitted successfully! An email has been sent.";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Email sending function
function sendVisitorEmail($visitorEmail, $visitorName, $visitDate, $purpose, $status) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';  // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your@example.com';  // SMTP username
        $mail->Password   = 'yourpassword';      // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@yourdomain.com', 'Visitor Management System');
        $mail->addAddress($visitorEmail, $visitorName); // Visitor's email
        $mail->addAddress('admin@yourdomain.com');      // Admin notification
        
        // Content
        $mail->isHTML(true);
        
        if ($status == 'pending') {
            $mail->Subject = 'Your Visit Request Submission';
            $mail->Body    = "
                <h2>Visit Request Received</h2>
                <p>Dear $visitorName,</p>
                <p>Your visit request has been received and is pending approval.</p>
                <p><strong>Details:</strong></p>
                <ul>
                    <li>Date: $visitDate</li>
                    <li>Purpose: $purpose</li>
                    <li>Status: Pending Approval</li>
                </ul>
                <p>You'll receive another email once your request is processed.</p>
            ";
        } elseif ($status == 'approved') {
            $mail->Subject = 'Your Visit Request Approved';
            $mail->Body    = "
                <h2>Visit Request Approved</h2>
                <p>Dear $visitorName,</p>
                <p>Your visit request has been approved.</p>
                <p><strong>Approved Visit Details:</strong></p>
                <ul>
                    <li>Date: $visitDate</li>
                    <li>Purpose: $purpose</li>
                </ul>
                <p>Please bring a valid ID when you arrive.</p>
            ";
        } else {
            $mail->Subject = 'Your Visit Request Update';
            $mail->Body    = "
                <h2>Visit Request Status Update</h2>
                <p>Dear $visitorName,</p>
                <p>Your visit request status has been updated to: $status</p>
            ";
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Get approval statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total_requests,
    SUM(status = 'approved') as approved,
    SUM(status = 'denied') as declined
    FROM visitors WHERE employee_id = $employee_id")->fetch_assoc();

// Get notifications (last 3 status changes)
$notifications = $conn->query("SELECT name, status, updated_at 
    FROM visitors 
    WHERE employee_id = $employee_id AND status IN ('approved','denied')
    ORDER BY updated_at DESC LIMIT 3");

// Get all requests
$search = $_GET['search'] ?? '';
$where = $search ? "AND name LIKE '%$search%'" : "";
$requests = $conn->query("SELECT name as visitor, email, 
    DATE(visit_date) as request_date, 
    reason as purpose, 
    status
    FROM visitors 
    WHERE employee_id = $employee_id $where
    ORDER BY visit_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visitor Approval System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        :root { --primary: #007570; --secondary: #07AF8B; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .approval-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .approval-number { font-size: 2.5rem; font-weight: 700; color: var(--primary); }
        .notification-item { padding: 12px 0; border-bottom: 1px solid #eee; }
        .status-approved { color: #28a745; font-weight: 500; }
        .status-pending { color: #ffc107; font-weight: 500; }
        .status-denied { color: #dc3545; font-weight: 500; }
        .search-box { position: relative; }
        .search-box input { padding-left: 40px; border-radius: 20px; }
        .search-box i { position: absolute; left: 15px; top: 10px; color: #6c757d; }
        .new-request-btn { background-color: var(--primary); color: white; border: none; padding: 8px 20px; border-radius: 20px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <h4>PERSONAL</h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="approval-card text-center">
                            <div class="approval-number"><?= $stats['total_requests'] ?? 0 ?></div>
                            <div>Requests</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="approval-card text-center">
                            <div class="approval-number"><?= $stats['approved'] ?? 0 ?></div>
                            <div>Approved</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="approval-card text-center">
                            <div class="approval-number"><?= $stats['declined'] ?? 0 ?></div>
                            <div>Declined</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="approval-card">
                    <h5>Notifications</h5>
                    <?php if ($notifications->num_rows > 0): ?>
                        <?php while($note = $notifications->fetch_assoc()): ?>
                            <div class="notification-item">
                                <div>Request <?= htmlspecialchars($note['name']) ?> is <?= $note['status'] ?>.</div>
                                <small class="text-muted">
                                    <?= date('h:iA', strtotime($note['updated_at'])) ?><br>
                                    <?= date('d/m/Y', strtotime($note['updated_at'])) ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-muted">No notifications yet</div>
                    <?php endif; ?>
                </div>
                
                <button class="new-request-btn w-100 mt-3" data-bs-toggle="modal" data-bs-target="#newRequestModal">
                    <i class="bi bi-plus-circle"></i> New Request
                </button>
            </div>

            <div class="col-md-8">
                <div class="approval-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>My Requests</h5>
                        <form method="GET" class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control" placeholder="Search visitors..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Visitor</th>
                                    <th>Email</th>
                                    <th>Request Date</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($requests->num_rows > 0): ?>
                                    <?php while($req = $requests->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($req['visitor']) ?></td>
                                        <td><?= htmlspecialchars($req['email']) ?></td>
                                        <td><?= htmlspecialchars($req['request_date']) ?></td>
                                        <td><?= htmlspecialchars($req['purpose']) ?></td>
                                        <td class="status-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></td>
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
                </div>
            </div>
        </div>
    </div>

    <!-- New Request Modal -->
    <div class="modal fade" id="newRequestModal" tabindex="-1" aria-labelledby="newRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newRequestModalLabel">New Visitor Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Visitor Full Name*</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Visitor Email*</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Organization*</label>
                            <input type="text" name="organization" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Visit Date*</label>
                            <input type="date" name="visit_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose*</label>
                            <textarea name="purpose" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 60 seconds
        setTimeout(function(){ window.location.reload(); }, 60000);
    </script>
</body>
</html>
<?php $conn->close(); ?>