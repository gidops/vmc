<?php
// authload.php

session_start(); // Start the session

// Check if the user is logged in (for example, if host_id exists in session)
if (!isset($_SESSION['host_id'])) {
    // If not logged in, redirect to the login page (or show an error message)
    header("Location: login.php");
    exit();
}

// Continue loading the page (this is the 'protected' content)
?>
