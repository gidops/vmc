<?php
session_start();
require 'db_connection.php';

// Check if user is logged in (adjust according to your auth system)
if (!isset($_SESSION['employee_id'])) {
    header("Location: new_landing_page.html");
    exit();
}

// Get user details (optional - for displaying in header)
$user_id = $_SESSION['employee_id'];
$user_stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate password
    if (empty($newPassword)) {
        $errors[] = "Password is required";
    } elseif (strlen($newPassword) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $newPassword) || 
              !preg_match("/[a-z]/", $newPassword) || 
              !preg_match("/[0-9]/", $newPassword)) {
        $errors[] = "Password must include uppercase, lowercase, and numbers";
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // If validation passes
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password in database
        $update_stmt = $conn->prepare("UPDATE employees SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashedPassword, $user_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            // Redirect to dashboard after 2 seconds
            header("Refresh: 2; url=dashboard.php");
        } else {
            $errors[] = "Error updating password: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Password - Visitor Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .brand-header {
      background-color: #ffffff;
      border-bottom: 2px solid #e0e0e0;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .brand-header img {
      height: 50px;
    }
    .logout-btn {
      background-color: #FFCA00;
      color: #000;
      border: none;
    }
    .password-card {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .btn-primary-custom {
      background-color: #FFCA00;
      color: #000;
      border: none;
    }
    .btn-primary-custom:hover {
      background-color: #e6b800;
    }
    .form-control:focus {
      border-color: #07AF8B;
      box-shadow: 0 0 0 0.25rem rgba(7, 175, 139, 0.25);
    }
    .text-primary-custom {
      color: #007570;
    }
    .alert {
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="brand-header">
    <img src="assets/logo-green-yellow.png" alt="Company Logo">
    <div class="d-flex align-items-center gap-3">
      <div class="text-end">
        <div><strong><?= htmlspecialchars($user['name'] ?? 'User') ?></strong></div>
        <div class="text-muted" style="font-size: 0.9rem;">Location: Abuja</div>
      </div>
      <a href="logout.php" class="btn logout-btn">Logout</a>
    </div>
  </div>

  <!-- Main Section -->
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="password-card">
          <h3 class="text-primary-custom mb-3">🔐 Set Up Your Account Password</h3>
          <p class="text-muted">Secure access to your dashboard starts with a strong password.</p>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success">
              Password updated successfully! Redirecting to dashboard...
            </div>
          <?php else: ?>
            <form method="POST" action="create_password.php">
              <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" 
                      placeholder="Enter your new password" required>
                <div class="form-text">Use at least 8 characters, including symbols & numbers.</div>
              </div>

              <div class="mb-4">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                      placeholder="Re-enter your new password" required>
                <div class="form-text">Make sure both passwords match.</div>
              </div>

              <button type="submit" class="btn btn-primary-custom w-100">Save Password</button>
            </form>

            <hr class="my-4">

            <h6 class="text-muted">✅ Password Requirements:</h6>
            <ul class="text-muted small">
              <li>Minimum 8 characters</li>
              <li>Use uppercase, lowercase, numbers & symbols</li>
              <li>Avoid common words or repeated characters</li>
            </ul>

            <div class="text-muted mt-4 small">
              🔒 Your account is protected with industry-standard encryption.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>