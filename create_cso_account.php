<?php
// pages/auth/login.php

// Start session first
session_start();

// Load configuration and database
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Include auth functions
require_once __DIR__ . '/../../../includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: ../dashboard/" . $_SESSION['role'] . ".php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        $user = authenticate_user($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: ../dashboard/" . $user['role'] . ".php");
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | International Bank Visitors Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 450px;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .bank-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .bank-logo img {
            max-height: 60px;
        }
        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.1);
        }
        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
            padding: 0.5rem 1.5rem;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="bank-logo">
                <img src="/assets/images/logo.png" alt="International Bank Logo">
            </div>
            <h2 class="text-center mb-4">Secure Access Portal</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Corporate Email</label>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="Enter your registered email address">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
                <div class="text-center">
                    <p class="mb-1">New staff member? <a href="../auth/register.php">Request Access</a></p>
                    <p><a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Password Assistance</h5>
                    <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please contact your system administrator or IT support team to reset your password.</p>
                    <p>For security reasons, password resets require identity verification.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="mailto:it-support@internationalbank.com" class="btn btn-primary">Email IT Support</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/form-validation.js"></script>
</body>
</html>