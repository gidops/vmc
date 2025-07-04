<?php
// Database connection
$servername = "localhost";
$username = "aatcabuj_admin";
$password = "Sgt.pro@501";
$dbname = "aatcabuj_visitors_version_2";

// Create connection
$connection = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone if needed
date_default_timezone_set('Africa/Lagos');

// Two separate queries approach
$checked_in_sql = "SELECT 
                    v.id, v.name, v.phone, v.organization,
                    v.reason as visit_purpose, v.host_name,
                    v.check_in_time, v.check_out_time,
                    v.floor_of_visit, r.name as receptionist_name,
                    v.status, v.created_at
                   FROM visitors v
                   LEFT JOIN receptionists r ON v.receptionist_id = r.id
                   WHERE v.status = 'checked_in' AND DATE(v.created_at) = CURDATE()";

$checked_out_sql = "SELECT 
                    v.id, v.name, v.phone, v.organization,
                    v.reason as visit_purpose, v.host_name,
                    v.check_in_time, v.check_out_time,
                    v.floor_of_visit, r.name as receptionist_name,
                    v.status, v.created_at
                   FROM visitors v
                   LEFT JOIN receptionists r ON v.receptionist_id = r.id
                   WHERE v.status = 'checked_out' AND DATE(v.check_out_time) = CURDATE()";

// Execute both queries
$checked_in_result = mysqli_query($connection, $checked_in_sql);
$checked_out_result = mysqli_query($connection, $checked_out_sql);

// Check for query errors
if (!$checked_in_result) {
    die("Error in checked_in query: " . mysqli_error($connection));
}

if (!$checked_out_result) {
    die("Error in checked_out query: " . mysqli_error($connection));
}

// Combine results
$all_visitors = array();

// Add checked-in visitors
while($row = mysqli_fetch_assoc($checked_in_result)) {
    $all_visitors[] = $row;
}

// Add checked-out visitors
while($row = mysqli_fetch_assoc($checked_out_result)) {
    $all_visitors[] = $row;
}

// Sort by created_at time (most recent first)
usort($all_visitors, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Generate CSV
$filename = 'visitors_report_' . date('Y-m-d') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Open output stream
$output = fopen('php://output', 'w');

// CSV Headers
$headers = array(
    'Visitor Name',
    'Company Name',
    'Visit Purpose',
    'In',
    'Out',
    'Host Name',
    'Meeting room',
    'Inserted by',
);

// Write headers to CSV
fputcsv($output, $headers);

// Write data to CSV
foreach ($all_visitors as $visitor) {
    $row = array(
        $visitor['name'],
        $visitor['organization'],
        $visitor['visit_purpose'],
        $visitor['check_in_time'] ? date('H:i:s', strtotime($visitor['check_in_time'])) : '',
        $visitor['check_out_time'] ? date('H:i:s', strtotime($visitor['check_out_time'])) : '',
        $visitor['host_name'],
        $visitor['floor_of_visit'],
        $visitor['receptionist_name'] ? $visitor['receptionist_name'] : 'N/A',
    );
    
    fputcsv($output, $row);
}

// Close output stream
fclose($output);

// Close database connection
mysqli_close($connection);

// Debug mode (uncomment to see data instead of downloading CSV)
/*
echo "<h2>Debug Mode - Today's Visitors</h2>";
echo "<p>Total visitors found: " . count($all_visitors) . "</p>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>";
foreach ($headers as $header) {
    echo "<th>" . htmlspecialchars($header) . "</th>";
}
echo "</tr>";

foreach ($all_visitors as $visitor) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($visitor['id']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['name']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['phone']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['organization']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['visit_purpose']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['host_name']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['check_in_time'] ? $visitor['check_in_time'] : 'Not checked in') . "</td>";
    echo "<td>" . htmlspecialchars($visitor['check_out_time'] ? $visitor['check_out_time'] : 'Not checked out') . "</td>";
    echo "<td>" . htmlspecialchars($visitor['floor_of_visit']) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['receptionist_name'] ? $visitor['receptionist_name'] : 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars(ucfirst($visitor['status'])) . "</td>";
    echo "<td>" . htmlspecialchars($visitor['created_at']) . "</td>";
    echo "</tr>";
}
echo "</table>";
*/
?>