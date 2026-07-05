<?php
session_start();
session_destroy();
header('Location: /course-allocation-system/login.php');
exit();
?>
