<?php
// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

// Check connection
if ($conn->connect_error) {
    die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Error</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
            <style>
                .error-container {
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #f8f9fa;
                }
                .error-card {
                    max-width: 500px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <div class='error-card card border-danger'>
                    <div class='card-body text-center'>
                        <i class='fas fa-database fa-5x text-danger mb-4'></i>
                        <h2 class='card-title'>Database Connection Failed</h2>
                        <p class='card-text'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>
                        <a href='cso_dashboard.php' class='btn btn-primary mt-3'>
                            <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
    ");
}

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Sanitize input by casting to integer

    // Update visitor status to denied
    $result = $conn->query("UPDATE visitors SET status='rejected' WHERE id=$id");
    
    if ($result) {
        // Success response with Bootstrap styling
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Visitor Denied</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
            <style>
                .success-container {
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #f8f9fa;
                }
                .success-card {
                    max-width: 500px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
            </style>
        </head>
        <body>
            <div class='success-container'>
                <div class='success-card card border-success'>
                    <div class='card-body text-center'>
                        <i class='fas fa-user-slash fa-5x text-warning mb-4'></i>
                        <h2 class='card-title'>Visitor Denied</h2>
                        <p class='card-text'>The visitor entry has been successfully denied.</p>
                        <div class='d-grid gap-2 d-md-flex justify-content-md-center mt-4'>
                            <a href='cso_dashboard.php' class='btn btn-primary me-md-2'>
                                <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                            </a>
                            <a href='pending_visitors.php' class='btn btn-outline-secondary'>
                                <i class='fas fa-list me-2'></i> View Pending Visitors
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
        ";
    } else {
        // Error response with Bootstrap styling
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
        </head>
        <body>
            <div class='alert alert-danger m-4' role='alert'>
                <i class='fas fa-exclamation-triangle me-2'></i> Error denying visitor: " . htmlspecialchars($conn->error) . "
            </div>
            <div class='m-4'>
                <a href='cso_dashboard.php' class='btn btn-primary'>
                    <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                </a>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
        ";
    }
} else {
    // ID not provided response with Bootstrap styling
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    </head>
    <body>
        <div class='alert alert-danger m-4' role='alert'>
            <i class='fas fa-exclamation-triangle me-2'></i> No visitor ID provided.
        </div>
        <div class='m-4'>
            <a href='cso_dashboard.php' class='btn btn-primary'>
                <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
            </a>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>
    ";
}

// Close database connection
$conn->close();
?>