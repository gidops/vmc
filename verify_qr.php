<?php
include 'db_connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['qr_data'])) {
        throw new Exception('No QR data received');
    }

    $qrData = $_POST['qr_data'];
    
    $query = "SELECT 
                id,
                name AS visitor_name,
                host_name,
                phone,
                email,
                status,
                qr_code,
                organization AS company,
                reason AS purpose,
                host_id
              FROM visitors
              WHERE qr_code = ? AND status = 'approved'";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $qrData);
    if (!$stmt->execute()) {
        throw new Exception('Execute error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($visitor = $result->fetch_assoc()) {
        $response = [
            'status' => 'FOUND',
            'message' => 'Visitor found: ' . $visitor['visitor_name'],
            'visitor_id' => $visitor['id'],
            'visitor_name' => $visitor['visitor_name'],
            'company' => $visitor['company'],
            'purpose' => $visitor['purpose'],
            'host_name' => $visitor['host_name'],
            'host_id' => $visitor['host_id'],
            'phone' => $visitor['phone'],
            'email' => $visitor['email'],
            'qr_data' => $visitor['qr_code']
        ];
        
        // Update check-in time if not already set
        if (empty($visitor['check_in_time'])) {
    $updateQuery = "UPDATE visitors SET 
                   check_in_time = NOW(),
                   status = 'checked_in'
                   WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $visitor['id']);
    $updateStmt->execute();
    $updateStmt->close();
}

    } else {
        $response = [
            'status' => 'NOT_FOUND',
            'message' => 'No approved visitor found with the provided QR code'
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>