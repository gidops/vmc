<?php
require 'db_connection.php';
header('Content-Type: application/json');

// Simple email function (replace with your actual email method)
function sendEmail($to, $subject, $message) {
    $headers = "From: ugorjigideon2@gmail.com\r\n";
    $headers .= "Reply-To: noreply@yourdomain.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($data['visitor_id']) || !isset($data['employee_id'])) {
        throw new Exception('Missing required parameters');
    }

    // Fetch visitor and employee details
    $query = "SELECT 
                v.name AS visitor_name,
                v.organization,
                v.check_in_time,
                e.name AS employee_name,
                e.email AS employee_email
              FROM visitors v
              JOIN employees e ON v.employee_id = e.id
              WHERE v.id = ? AND e.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $data['visitor_id'], $data['employee_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Visitor or employee not found');
    }
    
    $details = $result->fetch_assoc();
    
    // Prepare email content
    $subject = "Visitor Arrival: {$details['visitor_name']}";
    $message = "
        <h2>Visitor Notification</h2>
        <p>Hello {$details['employee_name']},</p>
        <p>Your visitor <strong>{$details['visitor_name']}</strong> from 
        <strong>{$details['organization']}</strong> has arrived.</p>
        <p>Check-in time: {$details['check_in_time']}</p>
        <p>Please proceed to reception.</p>
    ";
    
    // Send notification
    $notificationSent = sendEmail(
        $details['employee_email'],
        $subject,
        $message
    );
    
    // Log the notification (optional)
    $logQuery = "UPDATE visitors 
                SET notification_sent = TRUE,
                notification_time = NOW()
                WHERE id = ?";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bind_param("i", $data['visitor_id']);
    $logStmt->execute();
    
    if ($notificationSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Host notified successfully',
            'email' => $details['employee_email']
        ]);
    } else {
        throw new Exception('Email could not be sent');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>