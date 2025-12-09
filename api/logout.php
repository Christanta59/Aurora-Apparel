<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: ../public/login.php"); // redirect ke login
exit;
