<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$invoice_id = intval($_GET['id'] ?? 0);

if ($invoice_id <= 0) {
    header('Location: index.php');
    exit();
}

// Check if invoice exists
$query = "SELECT id, invoice_number FROM invoices WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$invoice = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete associated payments first
    $delete_payments = "DELETE FROM payments WHERE invoice_id = ?";
    $stmt = $conn->prepare($delete_payments);
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    
    // Delete invoice
    $delete_query = "DELETE FROM invoices WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $invoice_id);
    
    if ($stmt->execute()) {
        logAudit($conn, $_SESSION['admin_id'], 'Delete Invoice', 'invoices', $invoice_id, $invoice, null);
        header('Location: index.php?success=Invoice deleted successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Invoice - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; padding-top: 20px; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trash-alt"></i> Confirm Delete</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6><strong>Warning!</strong></h6>
                            <p>You are about to permanently delete invoice <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>. This action cannot be undone.</p>
                        </div>

                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure?');">
                                    <i class="fas fa-trash-alt"></i> Yes, Delete Invoice
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
