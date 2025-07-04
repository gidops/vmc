<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the PHP QR Code library
include('phpqrcode/qrlib.php');

// Load PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Ensure ID is an integer to prevent SQL injection

    // Create the 'qr_codes' directory if it doesn't exist
    if (!file_exists('qr_codes')) {
        mkdir('qr_codes', 0777, true);
    }

    // Generate a unique QR Code
    $qr_code = "QR-" . uniqid();
    $qr_code_path = 'qr_codes/' . $qr_code . '.png';

    // Generate and save the QR code image
    QRcode::png($qr_code, $qr_code_path);

    // Update visitor status and assign QR code
    $stmt = $conn->prepare("UPDATE visitors SET status='approved', qr_code=? WHERE id=?");
    $stmt->bind_param("si", $qr_code, $id);
    $stmt->execute();
    //var_dump($id);
    

    // Retrieve visitor details
    $stmt = $conn->prepare("SELECT visitors.name, visitors.email, visitors.visit_date, visitors.time_of_visit, visitors.floor_of_visit, visitors.reason, visitors.unique_code, employees.name AS host_name, employees.email AS host_email FROM visitors LEFT JOIN employees ON visitors.employee_id = employees.id WHERE visitors.id=?");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    

        if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
        $visitor_name = $visitor['name'];
        $visitor_email = $visitor['email'];
        $host_name = $visitor['host_name']; 
        $visit_date = date('d-m-Y', strtotime($visitor['visit_date']));
        $visit_time = $visitor['time_of_visit'];
        $visit_location = $visitor['floor_of_visit'];
        $visit_purpose = $visitor['reason'];
        $unique_code = $visitor['unique_code'];


        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'mail.aatcabuja.com.ng';
            $mail->SMTPAuth = true;
            $mail->Username = 'support@aatcabuja.com.ng';
            $mail->Password = 'Dw2bbgvhZmsp7QA';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Email Setup
            $mail->setFrom('support@aatcabuja.com.ng', 'Abuja-AATC');
            $mail->addAddress($visitor_email);
            $mail->Subject = "Appointment Confirmed";
            
            // HTML Email Content
            $mail->isHTML(true);
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                         body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { 
            background-color: #07AF8B; 
            color: white; 
            padding: 30px 20px; 
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content { 
            padding: 25px;
        }
        .footer { 
            background-color: #007570;
            color: white;
            padding: 15px 20px; 
            text-align: center; 
            font-size: 12px; 
        }
        .qr-code { 
            text-align: center; 
            margin: 25px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #FFCA00;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .details-table td:first-child {
            font-weight: bold;
            color: #007570;
            width: 30%;
        }
        .highlight {
            background-color: rgba(255, 202, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 3px solid #FFCA00;
        }
        .button {
            display: inline-block;
            background-color: #FFCA00;
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 15px 0;
        }
                    </style>
                </head>
                <body>
    <div class='container'>
        <div class='header'>
            <h2>Appointment Confirmed</h2>
        </div>
        <div class='content'>
            <p>Dear $visitor_name,</p>
            <p>Your appointment request has been <strong>approved</strong>. Below are your appointment details:</p>
            <table class='details-table'>
                <tr>
                    <td><strong>Host:</strong></td>
                    <td>$host_name</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>$visit_date</td>
                </tr>
                <tr>
                    <td><strong>Time:</strong></td>
                    <td>$visit_time</td>
                </tr>
                <tr>
                    <td><strong>Purpose:</strong></td>
                    <td>$visit_purpose</td>
                </tr>
                <tr>
                    <td><strong>Unique Code:</strong></td>
                    <td><strong>$unique_code</strong></td>
                </tr>
            </table>
            <div class='qr-code'>
                <p><strong>Show this QR code at the gate on arrival:</strong></p>
                <img src='cid:qr_code' alt='QR Code' style='width: 200px; height: 200px;'>
            </div>
            <p>Please arrive on time. If you are unable to attend, kindly notify your host in advance.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
            ";
            
            // Add embedded QR code image
            $mail->addEmbeddedImage($qr_code_path, 'qr_code');
            
            // Attach the QR code image as well
            $mail->addAttachment($qr_code_path, 'Your_QR_Code.png');

            // Send Email
            $mail->send();
            
            if (!empty($visitor['host_email'])) {
                $host_email = $visitor['host_email'];
    
                $host_mail = new PHPMailer(true);
            try {
                // SMTP Configuration
                $host_mail->isSMTP();
                $host_mail->Host = 'smtp.gmail.com';
                $host_mail->SMTPAuth = true;
                $host_mail->Username = 'ugorjigideon2@gmail.com';
                $host_mail->Password = 'vveehxmeldoknxtg';
                $host_mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $host_mail->Port = 465;

                // Email Setup
                $host_mail->setFrom('ugorjigideon2@gmail.com', 'VMS System');
                $host_mail->addAddress($host_email);
                $host_mail->Subject = "Your Visitor's Appointment Has Been Approved";
                $host_mail->isHTML(true);
                $host_mail->Body = "
                    <html>
                    <body>
                        <p>Dear Host,</p>
                        <p>Your visitor, <strong>$visitor_name , $unique_code</strong>, has been approved.</p>
                        <p>They will be arriving with the following QR code:</p>
                        <img src='cid:qr_code' alt='QR Code' style='width: 200px; height: 200px;'>
                        
                        <p>Please be prepared for their visit.</p>
                    </body>
                    </html>
        ";

                // Attach the same QR code
                $host_mail->addEmbeddedImage($qr_code_path, 'qr_code');

                // Send the email
                $host_mail->send();
        } catch (Exception $e) {
        error_log("Host email sending failed: " . $host_mail->ErrorInfo);
    }
}

            // Display success message with Bootstrap styling
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Approval Success</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        text-align: center;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <div class="success-card card">
                        <div class="card-body">
                            <div class="mb-4 text-success">
                                <i class="fas fa-check-circle fa-5x"></i>
                            </div>
                            <h2 class="card-title mb-3">Approval Successful</h2>
                            <p class="card-text mb-4">Visitor has been approved and the QR code has been sent to their email address.</p>
                            <div class="d-flex justify-content-center">
                                <a href="cso_dashboard.php" class="btn" style="background-color: #FFCA00; color: #000; font-weight: bold;">
                                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            ';
            
        } catch (Exception $e) {
            // Display error message with Bootstrap styling
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Approval Error</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        text-align: center;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <div class="error-card card">
                        <div class="card-body">
                            <div class="mb-4 text-danger">
                                <i class="fas fa-exclamation-circle fa-5x"></i>
                            </div>
                            <h2 class="card-title mb-3">Approval Error</h2>
                            <p class="card-text mb-4">Email sending failed: ' . htmlspecialchars($mail->ErrorInfo) . '</p>
                            <div class="d-flex justify-content-center">
                                <a href="cso_dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            ';
            
        }
    } else {
        // Display visitor not found error with Bootstrap styling
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Visitor Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>
        <body>
            <div class="alert alert-danger m-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> Visitor not found.
            </div>
            <div class="m-4">
                <a href="cso_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                </a>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        ';
    }

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Display ID not provided error with Bootstrap styling
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="alert alert-danger m-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> No visitor ID provided.
        </div>
        <div class="m-4">
            <a href="cso_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
            </a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    ';
}
?>

