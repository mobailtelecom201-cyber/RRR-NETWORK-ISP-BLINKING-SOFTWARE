<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$expense_id = intval($_GET['id'] ?? 0);

if ($expense_id <= 0) {
    header('Location: index.php');
    exit();
}

$delete_query = "DELETE FROM expenses WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param('i', $expense_id);

if ($stmt->execute()) {
    logAudit($conn, $_SESSION['admin_id'], 'Delete Expense', 'expenses', $expense_id, null, null);
    header('Location: index.php?success=Expense deleted');
} else {
    header('Location: index.php?error=Failed to delete expense');
}
?>