<?php
// db_connection.php
$servername = "localhost";
$username = "aatcabuj_admin"; 
$password = "Sgt.pro@501";
$database = "aatcabuj_visitors_version_2";


// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>