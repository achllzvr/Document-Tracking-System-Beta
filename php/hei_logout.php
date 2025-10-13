<?php
// Generic logout for CHED/HEI users
session_start();
session_unset();
session_destroy();

// Redirect to a default landing page: choose CHED login
header('Location: /PRISM/php/hei_login.php');
exit;

?>
