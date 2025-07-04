<?php
require_once "db_connection.php";

header("Content-Type: application/json");

// Get the code from POST
if (!isset($_POST['code']) || empty(trim($_POST['code']))) {
    echo json_encode([
        "status" => "ERROR",
        "message" => "No code provided."
    ]);
    exit;
}

$code = trim($_POST['code']);

// Prepare and execute the query using 'unique_code'
$sql = "SELECT * FROM visitors WHERE unique_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $visitor = $result->fetch_assoc();

// Check if check_in_time is not set and update
    if (empty($visitor['check_in_time'])) {
        // Update check-in time and status
        $updateQuery = "UPDATE visitors SET check_in_time = NOW(), status = 'checked_in' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $visitor['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }

    // Return visitor details
    echo json_encode([
        "status" => "FOUND",
        "visitor" => [
            "id" => $visitor["id"],
            "visitor_name" => $visitor["name"],
            "email" => $visitor["email"],
            "phone" => $visitor["phone"],
            "company" => $visitor["organization"],
            "purpose" => $visitor["reason"],
            "host_name" => $visitor["host_name"],
            "arrival_time" => $visitor["arrival_time"],
            "visit_date" => $visitor["visit_date"],
            "status" => $visitor["status"],
            "unique_code" => $visitor["unique_code"]
        ]
    ]);
} else {
    // No match found
    echo json_encode([
        "status" => "NOT_FOUND",
        "message" => "No visitor found with that code."
    ]);
}

$stmt->close();
$conn->close();
?>
