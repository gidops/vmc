<?php
header('Content-Type: application/json'); // set JSON response ([php.net](https://www.php.net/manual/en/function.header.php?utm_source=chatgpt.com))

$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
if ($conn->connect_error) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Database connection failed']);
    exit;
}

if (empty($_POST['unique_code'])) {
    echo json_encode(['status' => 'ERROR', 'message' => 'No code provided']);
    exit;
}

$code = trim($_POST['unique_code']);

$stmt = $conn->prepare(
    "SELECT id, name AS visitor_name, company, host_name, purpose
     FROM visitors
     WHERE unique_code = ?
       AND status = 'approved'"
); // use prepared statement to prevent SQL injection ([php.net](https://www.php.net/manual/en/mysqli.prepare.php?utm_source=chatgpt.com))
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result(); // get_result() returns mysqli_result ([php.net](https://www.php.net/manual/en/mysqli-result.fetch-assoc.php?utm_source=chatgpt.com))

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc(); // fetch_assoc() returns associative array ([php.net](https://www.php.net/manual/en/mysqli-result.fetch-assoc.php?utm_source=chatgpt.com))
    echo json_encode([
        'status'       => 'FOUND',
        'visitor_id'   => $row['id'],
        'visitor_name' => $row['visitor_name'],
        'company'      => $row['company'],
        'host_name'    => $row['host_name'],
        'purpose'      => $row['purpose']
    ]);
} else {
    echo json_encode(['status' => 'NOT_FOUND', 'message' => 'Invalid access code']);
}

$stmt->close();
$conn->close();
