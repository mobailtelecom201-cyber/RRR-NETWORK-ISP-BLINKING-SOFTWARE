<?php
// Common Functions

// Hash Password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify Password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate Customer ID
function generateCustomerId() {
    return 'CUST' . date('Ymd') . rand(1000, 9999);
}

// Generate Invoice Number
function generateInvoiceNumber() {
    return 'INV' . date('Ymd') . rand(10000, 99999);
}

// Format Currency
function formatCurrency($amount, $currency = 'BDT') {
    return $currency . ' ' . number_format($amount, 2);
}

// Format Date
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

// Redirect
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Sanitize Input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Validate Email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate Phone
function validatePhone($phone) {
    return preg_match('/^([0-9]{10,15})$/', $phone);
}

// Check Admin Permission
function checkPermission($required_role) {
    if (!isset($_SESSION['admin_id'])) {
        redirect('login.php');
    }
    
    $allowed_roles = is_array($required_role) ? $required_role : [$required_role];
    
    if (!in_array($_SESSION['admin_role'], $allowed_roles)) {
        redirect('dashboard.php?error=Unauthorized access');
    }
}

// Log Audit Trail
function logAudit($conn, $admin_id, $action, $table, $record_id, $old_values = null, $new_values = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $old_values_json = $old_values ? json_encode($old_values) : null;
    $new_values_json = $new_values ? json_encode($new_values) : null;
    
    $query = "INSERT INTO audit_logs (admin_id, action, affected_table, affected_record_id, old_values, new_values, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('issiisss', $admin_id, $action, $table, $record_id, $old_values_json, $new_values_json, $ip_address, $user_agent);
    
    return $stmt->execute();
}

// Get Setting Value
function getSetting($conn, $key) {
    $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return null;
}

// Update Setting Value
function updateSetting($conn, $key, $value) {
    $check_query = "SELECT id FROM settings WHERE setting_key = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('s', $key);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $query = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
    } else {
        $query = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
    }
    
    $stmt = $conn->prepare($query);
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->bind_param('ss', $value, $key);
    } else {
        $stmt->bind_param('ss', $key, $value);
    }
    
    return $stmt->execute();
}

// Send SMS
function sendSMS($phone, $message, $type = 'Custom') {
    // SMS Gateway Integration
    // Replace with actual SMS gateway API call
    return true;
}

// Send Email
function sendEmail($to, $subject, $message, $html = true) {
    // Email Integration
    // Replace with actual email sending logic
    return true;
}
?>