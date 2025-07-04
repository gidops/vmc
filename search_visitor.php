<?php
include 'db.php';

$name = isset($_GET['name']) ? mysqli_real_escape_string($conn, $_GET['name']) : '';

$query = "SELECT * FROM visitors 
          WHERE (status = 'approved' OR status = 'checked_in') 
          AND name LIKE '%$name%' 
          ORDER BY visit_date DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0): ?>
  <ul class="list-group">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?= htmlspecialchars($row['name']) ?></strong><br>
          <small><?= htmlspecialchars($row['organization']) ?> - <?= htmlspecialchars($row['reason']) ?></small>
        </div>
        <span class="badge bg-<?= $row['status'] == 'approved' ? 'success' : 'primary' ?>">
          <?= ucfirst($row['status']) ?>
        </span>
      </li>
    <?php endwhile; ?>
  </ul>
<?php else: ?>
  <div class="alert alert-warning">No matching visitors found.</div>
<?php endif; ?>
