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

$query = "SELECT i.* FROM invoices i WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$invoice = $result->fetch_assoc();

// Fetch customers
$customers_query = "SELECT c.id, c.customer_id, c.fullname FROM customers c WHERE c.status = 'Active'";
$customers_result = $conn->query($customers_query);
$customers = $customers_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $due_date = sanitizeInput($_POST['due_date'] ?? '');
    $discount = floatval($_POST['discount'] ?? 0);
    $tax = floatval($_POST['tax'] ?? 0);
    $status = sanitizeInput($_POST['status'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');

    if (empty($due_date) || empty($status)) {
        $error = 'Please fill in all required fields';
    } else {
        $total = ($invoice['amount'] - $discount) + $tax;

        $update_query = "UPDATE invoices SET due_date = ?, discount = ?, tax = ?, total = ?, status = ?, notes = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        
        if ($stmt) {
            $stmt->bind_param('sdddssi', $due_date, $discount, $tax, $total, $status, $notes, $invoice_id);
            
            if ($stmt->execute()) {
                logAudit($conn, $_SESSION['admin_id'], 'Update Invoice', 'invoices', $invoice_id, $invoice, ['due_date' => $due_date, 'status' => $status]);
                $success = 'Invoice updated successfully!';
                
                // Refresh invoice data
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $invoice_id);
                $stmt->execute();
                $invoice = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Error updating invoice: ' . $stmt->error;
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
    <title>Edit Invoice - <?php echo APP_NAME; ?></title>
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
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Invoice Details:</strong> Amount: <?php echo formatCurrency($invoice['amount']); ?> | Current Total: <?php echo formatCurrency($invoice['total']); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Issue Date</label>
                                <input type="date" class="form-control" value="<?php echo $invoice['issue_date']; ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Due Date *</label>
                                <input type="date" name="due_date" class="form-control" required value="<?php echo $invoice['due_date']; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Discount</label>
                                <input type="number" name="discount" class="form-control" step="0.01" value="<?php echo $invoice['discount']; ?>" id="discount" onchange="calculateTotal()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tax (VAT)</label>
                                <input type="number" name="tax" class="form-control" step="0.01" value="<?php echo $invoice['tax']; ?>" id="tax" onchange="calculateTotal()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="Draft" <?php echo $invoice['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="Sent" <?php echo $invoice['status'] === 'Sent' ? 'selected' : ''; ?>>Sent</option>
                                    <option value="Paid" <?php echo $invoice['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="Overdue" <?php echo $invoice['status'] === 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
                                    <option value="Cancelled" <?php echo $invoice['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">New Total</label>
                                <input type="number" class="form-control" id="total" readonly value="<?php echo $invoice['total']; ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($invoice['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Invoice</button>
                                <a href="view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calculateTotal() {
            const amount = <?php echo $invoice['amount']; ?>;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const total = (amount - discount) + tax;
            document.getElementById('total').value = total.toFixed(2);
        }
    </script>
</body>
</html>
