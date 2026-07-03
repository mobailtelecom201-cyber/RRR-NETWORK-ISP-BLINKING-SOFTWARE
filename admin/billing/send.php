<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$invoice_id = intval($_POST['invoice_id'] ?? 0);
$action = sanitizeInput($_POST['action'] ?? '');

if ($invoice_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
    exit();
}

// Fetch invoice details
$query = "SELECT i.*, c.email, c.phone FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    exit();
}

$invoice = $result->fetch_assoc();
$success = false;
$message = '';

if ($action === 'email') {
    // Send invoice via email
    $to = $invoice['email'];
    $subject = 'Invoice ' . $invoice['invoice_number'] . ' from ' . APP_NAME;
    $message_body = "Dear Customer,\n\nPlease find your invoice attached.\n\nInvoice Number: " . $invoice['invoice_number'] . "\nAmount Due: BDT " . number_format($invoice['total'], 2) . "\nDue Date: " . $invoice['due_date'] . "\n\nThank you for your business!\n\nBest regards,\n" . APP_NAME;
    
    if (sendEmail($to, $subject, $message_body)) {
        // Update invoice status to 'Sent'
        $update_query = "UPDATE invoices SET status = 'Sent', updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $invoice_id);
        
        if ($stmt->execute()) {
            logAudit($conn, $_SESSION['admin_id'], 'Send Invoice via Email', 'invoices', $invoice_id, null, null);
            $success = true;
            $message = 'Invoice sent successfully via email';
        } else {
            $message = 'Email sent but failed to update status';
        }
    } else {
        $message = 'Failed to send invoice via email';
    }
} else if ($action === 'sms') {
    // Send invoice notification via SMS
    $phone = $invoice['phone'];
    $sms_message = "Invoice " . $invoice['invoice_number'] . " Amount: BDT " . number_format($invoice['total'], 2) . ". Due: " . $invoice['due_date'] . ". Please pay on time. Thank you!";
    
    if (sendSMS($phone, $sms_message, 'Invoice')) {
        // Log SMS
        $log_query = "INSERT INTO sms_logs (customer_id, phone_number, message, sms_type, status) VALUES (?, ?, ?, 'Invoice', 'Sent')";
        $stmt = $conn->prepare($log_query);
        $stmt->bind_param('iss', $invoice['customer_id'], $phone, $sms_message);
        $stmt->execute();
        
        logAudit($conn, $_SESSION['admin_id'], 'Send Invoice via SMS', 'invoices', $invoice_id, null, null);
        $success = true;
        $message = 'Invoice notification sent successfully via SMS';
    } else {
        $message = 'Failed to send invoice via SMS';
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => $success, 'message' => $message]);
?>
