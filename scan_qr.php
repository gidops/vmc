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
                <!--<div class='card-body text-center'>
                        <i class='fas fa-database fa-5x text-danger mb-4'></i>
                        <h2 class='card-title'>Database Connection Failed</h2>
                        <p class='card-text'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>
                        <a href='dashboard.php' class='btn btn-primary mt-3'>
                            <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                        </a>
                    </div> -->
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
    ");
}

// Check if QR code is provided
if (isset($_GET['qr_code'])) {
    $qr_code = $conn->real_escape_string($_GET['qr_code']);
    $result = $conn->query("SELECT * FROM visitors WHERE qr_code='$qr_code' AND status='approved'");

    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
        // Access granted response
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Access Granted</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
            <style>
                .result-container {
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #f8f9fa;
                }
                .result-card {
                    max-width: 500px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                .qr-code {
                    width: 200px;
                    height: 200px;
                    margin: 0 auto 20px;
                    background-color: white;
                    padding: 10px;
                    border-radius: 8px;
                }
                .visitor-photo {
                    width: 120px;
                    height: 120px;
                    object-fit: cover;
                    border-radius: 50%;
                    border: 3px solid #fff;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class='result-container'>
                <div class='result-card card border-success'>
                    <div class='card-body'>
                        <div class='mb-4'>
                            <img src='" . htmlspecialchars($visitor['picture']) . "' alt='Visitor Photo' class='visitor-photo'>
                            <div class='qr-code'>
                                <img src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_code) . "' alt='QR Code'>
                            </div>
                        </div>
                        <h2 class='card-title text-success mb-3'>
                            <i class='fas fa-check-circle me-2'></i> Access Granted
                        </h2>
                        <h4 class='mb-3'>" . htmlspecialchars($visitor['name']) . "</h4>
                        <div class='visitor-details text-start mb-4'>
                            <p class='mb-2'><i class='fas fa-phone me-2'></i> " . htmlspecialchars($visitor['phone']) . "</p>
                            <p class='mb-2'><i class='fas fa-envelope me-2'></i> " . htmlspecialchars($visitor['email']) . "</p>
                            <p><i class='fas fa-calendar-day me-2'></i> " . (!empty($visitor['visit_date']) ? htmlspecialchars($visitor['visit_date']) : 'No date specified') . "</p>
                        </div>
                        <!-- <div class='d-grid'>
                            <a href='dashboard.php' class='btn btn-primary'>
                                <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                            </a>
                        </div> -->
                    </div>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
        ";
    } else {
        // Access denied response
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Access Denied</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
            <style>
                .result-container {
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #f8f9fa;
                }
                .result-card {
                    max-width: 500px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='result-container'>
                <div class='result-card card border-danger'>
                    <div class='card-body'>
                        <div class='mb-4 text-danger'>
                            <i class='fas fa-times-circle fa-5x'></i>
                        </div>
                        <h2 class='card-title text-danger mb-3'>Access Denied</h2>
                        <p class='mb-4'>The QR code is either invalid or the visitor hasn't been approved.</p>
                        <!-- <div class='d-grid gap-2'>
                            <a href='dashboard.php' class='btn btn-primary'>
                                <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                            </a>
                            <a href='verify.php' class='btn btn-outline-secondary'>
                                <i class='fas fa-qrcode me-2'></i> Try Another Code
                            </a>
                        </div> -->
                    </div>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>
        ";
    }
} else {
    // No QR code provided response
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>QR Code Verification</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
        <style>
            .verification-container {
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f8f9fa;
            }
            .verification-card {
                max-width: 500px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .qr-scanner-placeholder {
                width: 300px;
                height: 300px;
                background-color: #e9ecef;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class='verification-container'>
            <div class='verification-card card'>
                <div class='card-body'>
                    <div class='mb-4 text-primary'>
                        <i class='fas fa-qrcode fa-5x'></i>
                    </div>
                    <h2 class='card-title mb-3'>QR Code Verification</h2>
                    <p class='mb-4'>Scan or enter the visitor's QR code to verify access</p>
                    
                    <div class='qr-scanner-placeholder mb-4'>
                        <i class='fas fa-camera fa-3x text-secondary'></i>
                    </div>
                    
                    <form action='verify.php' method='GET' class='mb-4'>
                        <div class='input-group'>
                            <input type='text' name='qr_code' class='form-control form-control-lg' placeholder='Enter QR code manually'>
                            <button class='btn btn-primary' type='submit'>
                                <i class='fas fa-check'></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- <div class='d-grid'>
                        <a href='dashboard.php' class='btn btn-outline-secondary'>
                            <i class='fas fa-arrow-left me-2'></i> Return to Dashboard
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>
    ";
}

// Close database connection
$conn->close();
?>