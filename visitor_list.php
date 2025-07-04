<?php
session_start();
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

if (!isset($_SESSION['cso_id'])) {
    exit("Unauthorized");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM visitors WHERE status='pending'");

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="visitor-item">
        <div class="visitor-info">
            <h5><?php echo htmlspecialchars($row['name']); ?></h5>
            <span class="badge bg-warning text-dark mb-1">Pending</span>
            <small><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
            <small><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></small>
            <small><i class="fas fa-user-shield me-1"></i> Visiting: <?php echo htmlspecialchars($row['host_name']); ?></small>
            <?php if (!empty($row['visit_date'])): ?>
                <small><i class="fas fa-calendar-day me-1"></i> <?php echo htmlspecialchars($row['visit_date']); ?></small>
            <?php endif; ?>
            <?php if (!empty($row['organization'])): ?>
                <small><i class="fas fa-building me-1"></i> Organization: <?php echo htmlspecialchars($row['organization']); ?></small>
            <?php endif; ?>
            <?php if (!empty($row['reason'])): ?>
                <small><i class="fas fa-info-circle me-1"></i> Reason: <?php echo htmlspecialchars($row['reason']); ?></small>
            <?php endif; ?>
        </div>
        <div class="action-buttons">
            <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i> Approve
            </a>
            <a href="deny.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">
                <i class="fas fa-times me-1"></i> Deny
            </a>
        </div>
    </div>
<?php
    endwhile;
else:
?>
    <div class="empty-state">
        <i class="fas fa-user-check fa-4x mb-3"></i>
        <h4>No pending approvals</h4>
        <p>All visitor requests have been processed</p>
    </div>
<?php endif;
$conn->close();
?>
