<?php
session_start();
$error_message = "";

// DB Connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if the username and password match
    $stmt = $conn->prepare("SELECT id, name, password, profile_completed FROM receptionists WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($rec_id, $rec_name, $hashed_password, $profile_completed);
    $stmt->fetch();

    // Check if username exists and password matches
    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['receptionist_id'] = $rec_id;
        $_SESSION['receptionist_name'] = $rec_name;
        
        // Redirect based on profile completion status
        if ($profile_completed == 0) {
            header("Location: vmc_update_password.php");
        } else {
            header("Location: vmc_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Invalid username or password.";
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
    <title>VMC Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary: #07AF8B;
            --secondary: #FFCA00;
            --dark: #007570;
            --light: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #e6f7f4, #d1f2eb);
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 2.5rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 117, 112, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 117, 112, 0.2);
        }
        
        .logo {
            width: 180px;
            margin: 0 auto 1.5rem;
            display: block;
        }
        
        h2 {
            text-align: center;
            color: var(--dark);
            margin-bottom: 1.8rem;
            font-weight: 600;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(7, 175, 139, 0.2);
        }
        
        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-login:hover {
            background: var(--dark);
            transform: translateY(-2px);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-custom {
            background: #ff4757;
            color: white;
            border-radius: 8px;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .input-group-text {
            background: var(--light);
            border: 2px solid #e0e0e0;
            border-right: none;
        }
    </style>
</head>
<body>
    <div class="login-container animate__animated animate__fadeIn">
        <img src="assets/logo-green-yellow.png" alt="VMC Logo" class="logo">
        <h2>Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert-custom animate__animated animate__shakeX"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>