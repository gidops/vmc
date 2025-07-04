<?php
session_start();
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

if (!isset($_SESSION['receptionist_id'])) {
    exit("Unauthorized");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM visitors WHERE status='approved' ORDER BY visit_date DESC");

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="visitor-card mb-3 p-3 border rounded">
        <h5 class="mb-1"><?php echo htmlspecialchars($row['name']); ?></h5>
        <p class="mb-0">
            <strong>Host:</strong> <?php echo htmlspecialchars($row['host_name']); ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
            <strong>Visit Date:</strong> <?php echo htmlspecialchars($row['visit_date']); ?>
        </p>
    </div>
<?php
    endwhile;
else:
?>
    <p>No approved visitors found.</p>
<?php endif;

$conn->close();
?>
