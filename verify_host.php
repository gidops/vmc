<?php
include 'db_connection.php';
session_start();

$response = [];

if (isset($_SESSION['host_id'])) {
    $host_id = $_SESSION['host_id'];

    $query = "SELECT name, checked_in, check_in_time FROM visitors WHERE host_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $host_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'name' => $row['name'],
            'checked_in' => (bool)$row['checked_in'],
            'check_in_time' => $row['check_in_time']
        ];
    }

    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>

