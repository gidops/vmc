<?php
include 'db.php';

$query = "SELECT * FROM visitors WHERE status = 'checked_in' ORDER BY check_in_time DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0): ?>
  <ul class="list-group">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?= htmlspecialchars($row['name']) ?></strong><br>
          <small>Arrived at: <?= htmlspecialchars($row['check_in_time']) ?></small>
        </div>
        <form method="POST" action="checkout_visitor.php" class="d-inline">
          <input type="hidden" name="visitor_id" value="<?= $row['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm">Check Out</button>
        </form>
      </li>
    <?php endwhile; ?>
  </ul>
<?php else: ?>
  <div class="alert alert-secondary">No visitors currently checked in.</div>
<?php endif; ?>
