<?php
// Database Configuration
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'rrr_network';

// Create connection
$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset('utf8mb4');

define('DB_HOST', $host);
define('DB_USER', $db_user);
define('DB_PASSWORD', $db_password);
define('DB_NAME', $db_name);
define('DB_CONNECTION', $conn);
?>