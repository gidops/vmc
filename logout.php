<?php
session_start();
session_destroy();
header("Location: index.html"); // Adjust to the path of your login page
exit();
