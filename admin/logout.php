<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$auth = new Auth($conn);
$auth->logout();

header('Location: login.php?msg=Logged out successfully');
exit();
?>