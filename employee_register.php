<?php
session_start();

// Initialize variables
$message = '';
$message_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
        $message = "All fields are required!";
        $message_class = "error";
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST['name']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format!";
            $message_class = "error";
        } else {
            $conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");;

            if ($conn->connect_error) {
                $message = "Database connection failed: " . $conn->connect_error;
                $message_class = "error";
            } else {
                $stmt = null;
                $emailExists = false;

                try {
                    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
                    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

                    $stmt->bind_param("s", $email);
                    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

                    $stmt->store_result();
                    $emailExists = $stmt->num_rows > 0;

                    $stmt->close();

                    if ($emailExists) {
                        $message = "Email already exists. Please choose another email.";
                        $message_class = "error";
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
                        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

                        $stmt->bind_param("sss", $name, $email, $password_hash);

                        if ($stmt->execute()) {
                            $_SESSION['registration_success'] = true;
                            header("Location: account_success.php");
                            exit();
                        } else {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }
                    }
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $message_class = "error";
                } finally {
                    if ($conn instanceof mysqli) {
                        $conn->close();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    
    <style>
        :root {
            --primary-color: #0f9d58;
            --secondary-color: #0a7f44;
            --success-color: #34a853;
            --error-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f5f7ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: var(--dark-color);
        }

        .registration-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 8px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .registration-header h2 {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            padding: 12px 15px 12px 40px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            background-color: #f8f9fa;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(15, 157, 88, 0.2);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--gray-color);
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }

        #togglePassword {
            position: absolute;
            right: 15px;
            top: 40px;
            color: var(--gray-color);
            cursor: pointer;
        }

        #togglePassword:hover {
            color: var(--dark-color);
        }

        .password-strength {
            height: 4px;
            background-color: #e9ecef;
            margin-top: 8px;
            border-radius: 2px;
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
            display: flex;
            justify-content: center;
            align-items: center;
            transition: var(--transition);
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

        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
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
        <h2>Employee Registration</h2>
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
            <label for="name">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required
                   placeholder="John Doe" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
            <i class="fas fa-user input-icon"></i>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required
                   placeholder="john@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <i class="fas fa-envelope input-icon"></i>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required
                   placeholder="At least 8 characters" minlength="8">
            <i class="fas fa-lock input-icon"></i>
            <i class="fas fa-eye" id="togglePassword"></i>
            <div class="password-strength">
                <div class="password-strength-bar" id="passwordStrengthBar"></div>
            </div>
            <div class="password-hint" id="passwordHint">
                Password must contain at least 8 characters, including uppercase, lowercase, and numbers.
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordHint = document.getElementById('passwordHint');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            passwordInput.addEventListener('input', function () {
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                let width = 0;
                let color = '#f72585';
                if (strength <= 2) {
                    width = 33;
                    color = '#f72585';
                } else if (strength <= 4) {
                    width = 66;
                    color = '#ffbe0b';
                } else {
                    width = 100;
                    color = '#34a853';
                }

                passwordStrengthBar.style.width = width + '%';
                passwordStrengthBar.style.backgroundColor = color;
                passwordHint.style.display = password.length ? 'block' : 'none';
            });
        }

        const form = document.getElementById('registrationForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const password = passwordInput.value;
                const errors = [];

                if (password.length < 8) errors.push('Password must be at least 8 characters long');
                if (!/[A-Z]/.test(password)) errors.push('Password must contain at least one uppercase letter');
                if (!/[a-z]/.test(password)) errors.push('Password must contain at least one lowercase letter');
                if (!/[0-9]/.test(password)) errors.push('Password must contain at least one number');

                if (errors.length > 0) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        }
    });
</script>

</body>
</html>
