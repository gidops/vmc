<?php
require 'db_connection.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ugorjigideon2@gmail.com'; // Your email
                $mail->Password   = 'ahvnysmiwyjiervi'; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Recipients
                $mail->setFrom('no-reply@yourdomain.com', 'Visitor Management System');
                $mail->addAddress($email, $user['name']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                
                $resetLink = "http://localhost/AATCVMS/reset_password.php?token=$token";
                $mail->Body    = "Hello {$user['name']},<br><br>"
                              . "You requested to reset your password. Click the link below to proceed:<br><br>"
                              . "<a href='$resetLink'>Reset Password</a><br><br>"
                              . "This link will expire in 1 hour.<br><br>"
                              . "If you didn't request this, please ignore this email.";
                
                $mail->send();
                $success = "Password reset link sent to your email";
            } catch (Exception $e) {
                $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $errors[] = "Error processing your request. Please try again.";
        }
        $insert_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .password-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
        .btn-primary-custom {
            background-color: #FFCA00;
            color: #000;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="password-card">
                    <h2 class="text-center mb-4">Forgot Password</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($success)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">Send Reset Link</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>