<?php
// Database configuration
$servername = "localhost";
$username = "aatcabuj_admin";
$password = "Sgt.pro@501";
$database = "aatcabuj_visitors_version_2";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Display error page with Bootstrap styling
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                background-color: #f8f9fa;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                max-width: 600px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                border-left: 5px solid #dc3545;
            }
            .error-icon {
                font-size: 3rem;
                color: #dc3545;
            }
            .error-details {
                background-color: #f8d7da;
                border-radius: 5px;
                padding: 15px;
                font-family: monospace;
                font-size: 0.9rem;
            }
            .btn-retry {
                transition: all 0.3s;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-card card">
                <div class="card-body p-5 text-center">
                    <div class="error-icon mb-4">
                        <i class="fas fa-database"></i>
                    </div>
                    <h2 class="card-title mb-3">Database Connection Failed</h2>
                    <p class="card-text mb-4">We're unable to connect to the database server. Please check the configuration and try again.</p>
                    
                    <div class="error-details text-start mb-4">
                        <div class="mb-2"><strong>Error:</strong> <?php echo htmlspecialchars($conn->connect_error); ?></div>
                        <div class="mb-2"><strong>Server:</strong> <?php echo htmlspecialchars($servername); ?></div>
                        <div class="mb-2"><strong>Database:</strong> <?php echo htmlspecialchars($database); ?></div>
                        <div><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></div>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-primary btn-retry" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-2"></i> Retry Connection
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap 5 JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    die();
}
?>

<!-- If connection is successful, you can include this file in other pages -->
<!-- The connection will be available through the $conn variable -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Successful</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #28a745;
        }
        .success-icon {
            font-size: 3rem;
            color: #28a745;
        }
        .db-details {
            background-color: #e8f5e9;
            border-radius: 5px;
            padding: 15px;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
   <!-- <div class="container">
        <div class="success-card card">
            <div class="card-body p-5 text-center">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="card-title mb-3">Database Connection Successful</h2>
                <p class="card-text mb-4">You are now connected to the database server.</p>
                
                <div class="db-details text-start mb-4">
                    <div class="mb-2"><strong>Server:</strong> <?php echo htmlspecialchars($servername); ?></div>
                    <div class="mb-2"><strong>Database:</strong> <?php echo htmlspecialchars($database); ?></div>
                    <div><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></div>
                </div>
                
                <div class="d-flex justify-content-center">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i> Continue to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>