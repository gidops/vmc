<?php
require 'db_connection.php';
require 'functions.php'; // Where you put getPendingVisitors()

$pendingVisitors = getPendingVisitors($conn);
?>

<h3>Approved Visitors</h3>
<?php if (count($pendingVisitors) > 0): ?>
    <div class="visitor-list">
        <?php foreach ($pendingVisitors as $visitor): ?>
            <div class="visitor-card">
                <!-- Same visitor card HTML as above -->
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No approved visitors pending check-in</p>
<?php endif; ?>