<?php

// Start the session
session_start();

// If user is authenticated as HEI, send them to the HEI dashboard
if (!empty($_SESSION['heiUserID'])) {
    header('Location: /PRISM/HEI/hei-dashboard.php');
    exit;
}

// If not authenticated as CHED, send to the CHED login form
if (empty($_SESSION['chedID'])) {
    header('Location: /PRISM/CHED/login.php');
    exit;
}


?>