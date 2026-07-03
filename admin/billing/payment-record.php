<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$invoice_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

if ($invoice_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch invoice
$query = "SELECT i.*, c.fullname FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
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
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $reference_number = sanitizeInput($_POST['reference_number'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');

    if ($amount <= 0 || empty($payment_method)) {
        $error = 'Please provide amount and payment method';
    } else {
        $payment_date = date('Y-m-d H:i:s');
        $insert_query = "INSERT INTO payments (invoice_id, customer_id, amount, payment_method, transaction_id, payment_date, status, reference_number, notes) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Completed', ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $transaction_id = 'TXN' . date('YmdHis') . rand(1000, 9999);
        
        if ($stmt) {
            $stmt->bind_param('iidsssss', $invoice_id, $invoice['customer_id'], $amount, $payment_method, $transaction_id, $payment_date, $reference_number, $notes);
            
            if ($stmt->execute()) {
                // Check if fully paid
                $total_paid_query = "SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id = ? AND status = 'Completed'";
                $stmt2 = $conn->prepare($total_paid_query);
                $stmt2->bind_param('i', $invoice_id);
                $stmt2->execute();
                $paid_result = $stmt2->get_result()->fetch_assoc();
                $total_paid = $paid_result['total_paid'] ?? 0;
                
                // Update payment status
                if ($total_paid >= $invoice['total']) {
                    $update_query = "UPDATE invoices SET payment_status = 'Paid', status = 'Paid' WHERE id = ?";
                } else if ($total_paid > 0) {
                    $update_query = "UPDATE invoices SET payment_status = 'Partial' WHERE id = ?";
                } else {
                    $update_query = "UPDATE invoices SET payment_status = 'Unpaid' WHERE id = ?";
                }
                
                $stmt3 = $conn->prepare($update_query);
                $stmt3->bind_param('i', $invoice_id);
                $stmt3->execute();
                
                logAudit($conn, $_SESSION['admin_id'], 'Record Payment', 'payments', 0, null, ['invoice_id' => $invoice_id, 'amount' => $amount]);
                $success = 'Payment recorded successfully!';
                
                header('Location: view.php?id=' . $invoice_id);
                exit();
            } else {
                $error = 'Error recording payment: ' . $stmt->error;
            }
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .form-group label { font-weight: 600; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-network-wired"></i> <?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <a href="../../admin/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="../../admin/customers/index.php"><i class="fas fa-users"></i> Customers</a>
                <a href="../../admin/packages/index.php"><i class="fas fa-box"></i> Packages</a>
                <a href="../../admin/billing/index.php" class="active"><i class="fas fa-file-invoice"></i> Invoices</a>
                <a href="../../admin/payments/index.php"><i class="fas fa-money-bill"></i> Payments</a>
                <a href="../../admin/mikrotik/index.php"><i class="fas fa-rss"></i> MikroTik</a>
                <a href="../../admin/sms/index.php"><i class="fas fa-sms"></i> SMS</a>
                <a href="../../admin/expenses/index.php"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="../../admin/reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill"></i> Record Payment for Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <h6>Invoice Information</h6>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($invoice['fullname']); ?></p>
                            <p><strong>Invoice Total:</strong> <?php echo formatCurrency($invoice['total']); ?></p>
                        </div>

                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Amount *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required placeholder="0.00" max="<?php echo $invoice['total']; ?>">
                                <small class="form-text text-muted">Max: <?php echo formatCurrency($invoice['total']); ?></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Rocket">Rocket</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Transaction ID, Check #, etc.">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <input type="datetime-local" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Add any payment notes"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Record Payment</button>
                                <a href="view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
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
