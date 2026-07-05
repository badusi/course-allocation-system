<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: /course-allocation-system/index.php');
exit();
?>
