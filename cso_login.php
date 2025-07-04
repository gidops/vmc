<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session if needed
// session_start();

// Database connection local
// $conn = new mysqli(hostname:"localhost", username:"root", password:"", database:"visitor_db");

// Database connection remote
 $conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; // Initialize error message

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash FROM cso WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();
        
        if (password_verify($password, $password_hash)) {
            session_start();
            $_SESSION['cso_id'] = $id; // Store CSO ID in session
            header("Location: cso_dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <title>CSO Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
        }

        h2 {
            text-align: center;
            color: #007570;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
            box-sizing: border-box;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #07AF8B;
            outline: none;
            box-shadow: 0 0 0 3px rgba(7, 175, 139, 0.2);
        }

        .login-btn {
            background-color: #07AF8B;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            background-color: #007570;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
        }

        .message.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #c62828;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: #07AF8B;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .forgot-password a {
            color: #7f8c8d;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
            color: #FFCA00;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Security Manager Login</h2>

    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="cso_login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <div class="forgot-password">
            <a href="password_reset.html">Forgot password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
    </form>
</div>

</body>
</html>
