<?php
session_start();
require 'db_connection.php';

if (!isset($_GET['code'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$unique_code = $conn->real_escape_string($_GET['code']);
$visitor_stmt = $conn->prepare("SELECT * FROM visitors WHERE unique_code = ?");
$visitor_stmt->bind_param("s", $unique_code);
$visitor_stmt->execute();
$visitor_result = $visitor_stmt->get_result();
$visitor_details = $visitor_result->fetch_assoc();
$visitor_stmt->close();

header('Content-Type: application/json');
echo json_encode($visitor_details);
?>