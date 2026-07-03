<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

header('Content-Type: application/json');

$action = sanitizeInput($_POST['action'] ?? '');
$invoice_ids = $_POST['invoice_ids'] ?? [];

if (empty($invoice_ids) || !is_array($invoice_ids)) {
    echo json_encode(['success' => false, 'message' => 'No invoices selected']);
    exit();
}

$success = true;
$count = 0;

if ($action === 'mark_sent') {
    foreach ($invoice_ids as $id) {
        $id = intval($id);
        $query = "UPDATE invoices SET status = 'Sent' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $count++;
            logAudit($conn, $_SESSION['admin_id'], 'Bulk Mark Sent', 'invoices', $id, null, null);
        }
    }
    $message = "Marked $count invoice(s) as sent";
} else if ($action === 'mark_overdue') {
    foreach ($invoice_ids as $id) {
        $id = intval($id);
        $query = "UPDATE invoices SET status = 'Overdue' WHERE id = ? AND status != 'Paid'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $count++;
            logAudit($conn, $_SESSION['admin_id'], 'Bulk Mark Overdue', 'invoices', $id, null, null);
        }
    }
    $message = "Marked $count invoice(s) as overdue";
} else if ($action === 'cancel') {
    foreach ($invoice_ids as $id) {
        $id = intval($id);
        $query = "UPDATE invoices SET status = 'Cancelled' WHERE id = ? AND status != 'Paid'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $count++;
            logAudit($conn, $_SESSION['admin_id'], 'Bulk Cancel', 'invoices', $id, null, null);
        }
    }
    $message = "Cancelled $count invoice(s)";
} else {
    $success = false;
    $message = 'Invalid action';
}

echo json_encode([
    'success' => $success,
    'message' => $message,
    'affected' => $count
]);
?>
