<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash FROM gate WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();
        
        if (password_verify($password, $password_hash)) {
            $_SESSION['gate_id'] = $id; // Store Gate Officer ID in session
            header("Location: gate_dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            echo "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No account found with that email.</div>";
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
  <title>Gate Login</title>
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
    color: #007570; /* Your specified green shade */
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
    <h2>Gate Operative Login</h2>
    <form method="POST" action="gate_login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <div class="forgot-password">
            <a href="gate_password_reset.html">Forgot password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
    </form>
   <!-- <div class="register-link">
        Don't have an account? <a href="gate_register.php">Register here</a>
    </div> -->
</div>

</body>
</html>
