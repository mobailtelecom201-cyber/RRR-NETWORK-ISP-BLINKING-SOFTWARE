<?php
// Session Configuration
session_start();

// Set session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check if session is expired
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: login.php?msg=Session expired');
        exit();
    }
}

$_SESSION['last_activity'] = time();

// Session security
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} else {
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header('Location: login.php?msg=Security violation');
        exit();
    }
}
?>