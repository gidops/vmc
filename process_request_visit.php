<?php
session_start();
require_once 'db_connection.php'; // Your DB config

if (!isset($_SESSION['receptionist_id'])) {
    header("Location: vmc_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $visitor_name = $conn->real_escape_string($_POST['visitor_name']);
    $visitor_email = $conn->real_escape_string($_POST['visitor_email']);
    $visitor_phone = $conn->real_escape_string($_POST['visitor_phone']);
    $visit_date = $conn->real_escape_string($_POST['visit_date']);
    $staff_id = (int)$_POST['staff_id'];
    $purpose = $conn->real_escape_string($_POST['purpose']);
    $receptionist_id = (int)$_SESSION['receptionist_id'];

    // Insert into visit_requests table
    $stmt = $conn->prepare("INSERT INTO visit_requests 
                          (visitor_name, visitor_email, visitor_phone, visit_date, staff_id, purpose, requested_by, status, request_date) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssssisi", $visitor_name, $visitor_email, $visitor_phone, $visit_date, $staff_id, $purpose, $receptionist_id);

    if ($stmt->execute()) {
        $request_id = $conn->insert_id;
        $_SESSION['success'] = "Visit request submitted! CSO will review it shortly.";
        
        // Notify CSO (via email/dashboard notification)
        header("Location: notify_cso.php?request_id=$request_id");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: vmc_dashboard.php");
        exit();
    }
} else {
    header("Location: vmc_dashboard.php");
    exit();
}
?>