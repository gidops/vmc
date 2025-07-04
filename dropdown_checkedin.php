<?php
include 'db.php';

$query = "SELECT id, name FROM visitors WHERE status = 'approved' ORDER BY 'name' ASC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0):
  while ($row = mysqli_fetch_assoc($result)): ?>
    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
  <?php endwhile;
else: ?>
  <option disabled>No checked-in visitors available</option>
<?php endif; ?>
