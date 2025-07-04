<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['visitor_id'])) {
  $visitor_id = intval($_POST['visitor_id']);

  $query = "UPDATE visitors SET status = 'checked_out', checkout_time = NOW() WHERE id = $visitor_id";
  if (mysqli_query($conn, $query)) {
    header("Location: receptionist_dashboard.php");
    exit();
  } else {
    echo "Error updating status: " . mysqli_error($conn);
  }
} else {
  echo "Invalid request.";
}
