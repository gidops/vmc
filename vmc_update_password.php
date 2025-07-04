<?php
session_start();
require 'db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['receptionist_id'])) {
    header("Location: vmc_login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    if ($new_password !== $confirm_password) {
        $error = "New passwords don't match";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&^()_+\-=])[A-Za-z\d@$!%*#?&^()_+\-=]{8,}$/', $new_password)) {
        $error = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
    }

    if (empty($error)) {
        try {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $receptionist_id = $_SESSION['receptionist_id'];
            
            // Update password and set profile as completed
            $stmt = $conn->prepare("UPDATE receptionists SET password = ?, profile_completed = 1 WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $receptionist_id);
            
            if ($stmt->execute()) {
                $success = "Password updated successfully!";
                header("Refresh: 2; url=vmc_dashboard.php");
            } else {
                $error = "Error updating password: " . $conn->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Password | Visitor Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <style>
    :root {
      --primary-green: #004225;
      --accent-green: #007f5f;
      --yellow: #ffc107;
      --bg-light: #f4f6f9;
      --error-red: #dc3545;
      --success-green: #28a745;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 2rem;
    }

    .password-container {
      max-width: 500px;
      width: 100%;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      padding: 2rem;
    }

    .logo {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .logo img {
      height: 60px;
    }

    h1 {
      color: var(--primary-green);
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: #444;
      font-weight: 500;
    }

    input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
    }

    input:focus {
      outline: none;
      border-color: var(--accent-green);
      box-shadow: 0 0 0 3px rgba(0, 127, 95, 0.15);
    }

    .btn-update {
      background-color: var(--accent-green);
      color: white;
      border: none;
      padding: 0.75rem;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s ease;
      margin-top: 0.5rem;
    }

    .btn-update:hover {
      background-color: var(--primary-green);
    }

    .error-message {
      color: var(--error-red);
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: block;
    }

    .success-message {
      color: var(--success-green);
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: block;
      text-align: center;
      margin-bottom: 1rem;
    }

    .password-toggle {
      position: absolute;
      right: 10px;
      top: 38px;
      cursor: pointer;
      color: #777;
    }
  </style>
</head>
<body>
  <div class="password-container">
    <div class="logo">
      <img src="assets/logo-green-yellow.png" alt="Company Logo">
    </div>
    
    <h1>Update Your Password</h1>
    <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">Please set a new secure password</p>
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>
        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password')"></i>
        <small class="text-muted">Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
      </div>
      
      <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
      </div>
      
      <button type="submit" class="btn-update">Update Password</button>
    </form>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = input.nextElementSibling;
      
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }

    // Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&^()_+\-=])[A-Za-z\d@$!%*#?&^()_+\-=]{8,}$/;

      if (!passwordRegex.test(password)) {
        e.preventDefault();
        alert("Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.");
      }

      if (password !== confirmPassword) {
        e.preventDefault();
        alert("Passwords do not match.");
      }
    });
  </script>
</body>
</html>