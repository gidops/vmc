<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session at the beginning
session_start();

// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$message = '';
$message_class = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query to verify the employee credentials
    $stmt = $conn->prepare("SELECT id, name, password, profile_completed FROM employees WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password, $profile_completed);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['employee_id'] = $id; // Store employee ID in session
        $_SESSION['name'] = $name; // Store name in session
        
        // Check if profile needs completion
        if ($profile_completed == 0) {
            header("Location: update_profile.php"); // Redirect to profile completion
        } else {
            header("Location: staff_dashboard.php"); // Redirect to dashboard
        }
        exit(); // Important to prevent further execution
    } else {
        $message = "Invalid credentials.";
        $message_class = "error";
        
        header("Location: index.html?error=1");
        exit();

        
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome. Please Login</title>
    <!-- CSS -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e0f7f4);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .logo {
            margin-top: 40px;
            margin-bottom: 20px;
            text-align: center;
        }

        .logo img {
            width: 150px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
            margin-bottom: 40px;
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
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
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
            color: #777;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
            color: #FFCA00;
        }
    </style>
</head>
<body>
    <div class="logo">
        <a href="index.html">
            <img src="assets/logo-green-yellow.png" alt="Company Logo">
        </a>
    </div>

    <div class="login-container">
        <h2>Welcome, Please Login</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="employee_login.php">
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
        <!--<div class="register-link">
            Don't have an account? <a href="employee_register.php">Register here</a>
        </div>-->
    </div>
</body>
</html>
