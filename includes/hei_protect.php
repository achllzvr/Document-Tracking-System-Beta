<?php

// Start the session
session_start();

// If user is authenticated as CHED, send them to the CHED dashboard
if (!empty($_SESSION['chedID'])) {
    header('Location: /PRISM/CHED/ched-dashboard.php');
    exit;
}

// If not authenticated as HEI, send to the HEI login form
if (empty($_SESSION['heiUserID'])) {
    header('Location: /PRISM/HEI/login.php');
    exit;
}

?>