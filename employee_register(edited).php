<<?php
session_start();

$message = '';
$message_class = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        $message = "All fields are required!";
        $message_class = "error";
    } elseif (!preg_match("/^[A-Za-z\s\-]{2,30}$/", $first_name)) {
        $message = "First name must be 2-30 characters and contain only letters, spaces, or hyphens.";
        $message_class = "error";
    } elseif (!preg_match("/^[A-Za-z\s\-]{2,30}$/", $last_name)) {
        $message = "Last name must be 2-30 characters and contain only letters, spaces, or hyphens.";
        $message_class = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $message = "Username must be 3-20 characters long and contain only letters, numbers, or underscores.";
        $message_class = "error";
    } else {
        // Connect to DB
        $conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
        if ($conn->connect_error) {
            $message = "Database connection failed: " . $conn->connect_error;
            $message_class = "error";
        } else {
            try {
                // Check if username exists
                $stmt = $conn->prepare("SELECT id FROM employees WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $message = "Username already exists. Please choose another.";
                    $message_class = "error";
                } else {
                    // Hash and insert
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, username, password) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $first_name, $last_name, $username, $password_hash);

                    if ($stmt->execute()) {
                        $_SESSION['registration_success'] = true;
                        header("Location: account_success.php");
                        exit();
                    } else {
                        $message = "Error inserting record: " . $stmt->error;
                        $message_class = "error";
                    }
                }
                $stmt->close();
            } catch (Exception $e) {
                $message = $e->getMessage();
                $message_class = "error";
            } finally {
                $conn->close();
            }
        }
    }
}



$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);

    // Validate first name
    if (!preg_match("/^[A-Za-z\s\-]{2,30}$/", $first_name)) {
        $errors[] = "First name must be 2-30 characters and contain only letters, spaces, or hyphens.";
    }

    // Validate last name
    if (!preg_match("/^[A-Za-z\s\-]{2,30}$/", $last_name)) {
        $errors[] = "Last name must be 2-30 characters and contain only letters, spaces, or hyphens.";
    }

    // If no errors, proceed
    if (empty($errors)) {
        // Proceed with storing in DB or whatever comes next
    } else {
        // Handle errors (e.g., show to user)
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <!-- CSS -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --error-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7ff;
            color: var(--dark-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .registration-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }
        
        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .registration-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .registration-header p {
            color: var(--gray-color);
            font-size: 0.95rem;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 40px 12px 40px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
            outline: none;
        }
        
        .input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-color);
    font-size: 1rem;
    pointer-events: none;
}

        
        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }
        
        #togglePassword {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-color);
    cursor: pointer;
    font-size: 1rem;
}

        
        #togglePassword:hover {
            color: var(--dark-color);
        }
        
        .password-strength {
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            background-color: var(--error-color);
            transition: var(--transition);
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: var(--gray-color);
            margin-top: 5px;
            display: none;
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .btn-submit:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .message {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .message.success {
            background-color: rgba(76, 201, 240, 0.1);
            color: #1a936f;
            border-left: 4px solid #1a936f;
        }
        
        .message.error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .no-login-message {
            text-align: center;
            margin-top: 20px;
            color: var(--gray-color);
            font-size: 0.9rem;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gray-color);
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .registration-container {
                padding: 30px 20px;
            }
            
            .registration-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h2>User Registration</h2>
            <p>Create your account to access the system</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <i class="fas <?php echo $message_class === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="registrationForm">
        <div class="form-group">
    <label for="first name">First Name</label>
    <input type="text" class="form-control" id="first-name" name="first-name" required 
           placeholder="John"
           pattern="[A-Za-z\s\-]{2,30}"
           title="First name should only contain letters, spaces, or hyphens (2-30 characters)."
           value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
    <i class="fas fa-user input-icon"></i>
</div>

<div class="form-group">
    <label for="last-name">Last Name</label>
    <input type="text" class="form-control" id="last-name" name="last-name" required 
           placeholder="Doe"
           pattern="[A-Za-z\s\-]{2,30}"
           title="Last name should only contain letters, spaces, or hyphens (2-30 characters)."
           value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
    <i class="fas fa-user input-icon"></i>
</div>


            <div class="form-group">
                <label for="username">Username</label>
                <input type="username" class="form-control" id="username" name="username" required 
                       placeholder="Username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="At least 8 characters" minlength="8">
               <!-- <i class="fas fa-lock input-icon"></i> -->
                <i class="fas fa-eye input-icon" id="togglePassword"></i>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-hint" id="passwordHint">
                    Password must contain at least 8 characters, including uppercase, lowercase, and numbers
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus me-2"></i> Register Now
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="employee_login.php">Login here</a>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        
        // Password strength indicator
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordHint = document.getElementById('passwordHint');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Calculate strength
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character type checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            let width = 0;
            let color = '#f72585'; // red
            
            if (strength <= 2) {
                width = 33;
                color = '#f72585'; // red
            } else if (strength <= 4) {
                width = 66;
                color = '#ffbe0b'; // yellow
            } else {
                width = 100;
                color = '#4cc9f0'; // blue
            }
            
            if (passwordStrengthBar) {
                passwordStrengthBar.style.width = width + '%';
                passwordStrengthBar.style.backgroundColor = color;
            }
            
            // Show/hide hint
            if (password.length > 0 && passwordHint) {
                passwordHint.style.display = 'block';
            } else if (passwordHint) {
                passwordHint.style.display = 'none';
            }
        });
        
        // Form validation
        const form = document.getElementById('registrationForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                const password = document.getElementById('password').value;
                const errors = [];
                
                // Check password requirements
                if (password.length < 8) {
                    valid = false;
                    errors.push('Password must be at least 8 characters long');
                } 
                if (!/[A-Z]/.test(password)) {
                    valid = false;
                    errors.push('Password must contain at least one uppercase letter');
                }
                if (!/[a-z]/.test(password)) {
                    valid = false;
                    errors.push('Password must contain at least one lowercase letter');
                }
                if (!/[0-9]/.test(password)) {
                    valid = false;
                    errors.push('Password must contain at least one number');
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert(errors.join('\n'));  // Show all errors in one alert
                }
            });
        }
    });
</script>
</body>
</html>