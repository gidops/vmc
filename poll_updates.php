<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$employee_id = $_SESSION['employee_id'];

// Get notifications
$notifications = $conn->query("SELECT name, status, updated_at 
    FROM visitors 
    WHERE employee_id = $employee_id 
    AND status IN ('approved','rejected')
    ORDER BY updated_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

// Get requests
$requests = $conn->query("SELECT name as visitor, email, 
    DATE(visit_date) as request_date, 
    reason as purpose, 
    status,
    unique_code
    FROM visitors 
    WHERE employee_id = $employee_id
    ORDER BY visit_date DESC")->fetch_all(MYSQLI_ASSOC);

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total_requests,
    IFNULL(SUM(status = 'approved'), 0) as approved,
    IFNULL(SUM(status = 'rejected'), 0) as declined
    FROM visitors 
    WHERE employee_id = $employee_id")->fetch_assoc();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'notifications' => $notifications,
    'requests' => $requests,
    'stats' => $stats
]);
?>