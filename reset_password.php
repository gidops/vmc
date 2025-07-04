<?php
require 'db_connection.php';
require 'PHPMailer/src/PHPMailer.php'; // Your existing mailer file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $errors = [];
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = "No account found with that email";
        } else {
            $user = $result->fetch_assoc();
        }
        $stmt->close();
    }

    if (empty($errors) && isset($user)) {
        // Generate token (32 characters)
        $token = bin2hex(random_bytes(16));
        
        // Set expiration (1 hour from now)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete any existing tokens for this email
        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete_stmt->bind_param("s", $email);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Store new token in database
        $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $email, $token, $expires);
        
        if ($insert_stmt->execute()) {
            // Send email using your existing mailer
            $resetLink = "https://yourdomain.com/reset_password.php?token=$token";
            $subject = "Password Reset Request";
            $body = "Hello {$user['name']},<br><br>"
                  . "You requested to reset your password. Click the link below to proceed:<br><br>"
                  . "<a href='$resetLink'>Reset Password</a><br><br>"
                  . "This link will expire in 1 hour.<br><br>"
                  . "If you didn't request this, please ignore this email.";
            
            // Assuming your mailer has a sendEmail function
            if (sendEmail($email, $subject, $body)) {
                $success = "Password reset link sent to your email";
            } else {
                $errors[] = "Failed to send email. Please try again later.";
            }
        } else {
            $errors[] = "Error processing your request. Please try again.";
        }
        $insert_stmt->close();
    }
}
?>

<!-- Rest of your HTML form remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Your existing styles here */
  </style>
</head>
<body>
  <!-- Your existing HTML structure -->
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="password-card">
          <h3 class="text-primary-custom mb-3">🔐 Reset Your Password</h3>
          
          <?php if (isset($invalidToken)): ?>
            <div class="alert alert-danger">
              Invalid or expired password reset link. Please request a new one.
            </div>
          <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
              <?php endforeach; ?>
            </div>
          <?php elseif ($success): ?>
            <div class="alert alert-success">
              Password updated successfully! Redirecting to login page...
            </div>
          <?php else: ?>
            <form method="POST" action="reset_password.php">
              <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
              
              <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
              </div>

              <div class="mb-4">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
              </div>

              <button type="submit" class="btn btn-primary-custom w-100">Reset Password</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>