<?php
session_start();
session_destroy();
header("Location: /biometric/login/login.php");
exit();
?>