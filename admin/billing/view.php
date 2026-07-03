<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$invoice_id = intval($_GET['id'] ?? 0);

if ($invoice_id <= 0) {
    header('Location: index.php');
    exit();
}

$query = "SELECT i.*, c.fullname, c.customer_id, c.email, c.phone, c.address FROM invoices i 
          JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$invoice = $result->fetch_assoc();

// Fetch payments
$payments_query = "SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC";
$stmt = $conn->prepare($payments_query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$payments_result = $stmt->get_result();
$payments = $payments_result->fetch_all(MYSQLI_ASSOC);

$total_paid = 0;
foreach ($payments as $payment) {
    if ($payment['status'] === 'Completed') {
        $total_paid += $payment['amount'];
    }
}

$remaining = $invoice['total'] - $total_paid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .invoice-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .invoice-title { font-size: 32px; font-weight: bold; margin-bottom: 20px; color: #2c3e50; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #ecf0f1; padding-bottom: 20px; }
        .invoice-details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .summary-table { margin-top: 30px; }
        .badge { padding: 8px 12px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-network-wired"></i> <?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../admin/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar no-print">
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
                <div class="row mb-3 no-print">
                    <div class="col-md-12">
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        <a href="edit.php?id=<?php echo $invoice['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                        <a href="print.php?id=<?php echo $invoice['id']; ?>" class="btn btn-info" target="_blank"><i class="fas fa-print"></i> Print</a>
                        <button onclick="window.print()" class="btn btn-info"><i class="fas fa-download"></i> Download PDF</button>
                    </div>
                </div>

                <div class="invoice-box">
                    <div class="invoice-header">
                        <div>
                            <h1 class="invoice-title">INVOICE</h1>
                            <p><strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                        </div>
                        <div class="text-end">
                            <h3><?php echo APP_NAME; ?></h3>
                            <p><small>Professional ISP Billing Solution</small></p>
                        </div>
                    </div>

                    <div class="row invoice-details">
                        <div class="col-md-6">
                            <h6><strong>Bill To:</strong></h6>
                            <p>
                                <strong><?php echo htmlspecialchars($invoice['fullname']); ?></strong><br>
                                Customer ID: <?php echo htmlspecialchars($invoice['customer_id']); ?><br>
                                Email: <?php echo htmlspecialchars($invoice['email']); ?><br>
                                Phone: <?php echo htmlspecialchars($invoice['phone']); ?><br>
                                Address: <?php echo htmlspecialchars($invoice['address']); ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p>
                                <strong>Invoice Date:</strong> <?php echo formatDate($invoice['issue_date'], 'Y-m-d'); ?><br>
                                <strong>Due Date:</strong> <?php echo formatDate($invoice['due_date'], 'Y-m-d'); ?><br>
                                <strong>Status:</strong> <span class="badge bg-<?php echo $invoice['status'] === 'Paid' ? 'success' : 'warning'; ?>"><?php echo $invoice['status']; ?></span>
                            </p>
                        </div>
                    </div>

                    <table class="table table-bordered summary-table">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Internet Service</td>
                                <td class="text-end"><?php echo formatCurrency($invoice['amount']); ?></td>
                            </tr>
                            <?php if ($invoice['discount'] > 0): ?>
                                <tr>
                                    <td><strong>Discount</strong></td>
                                    <td class="text-end">-<?php echo formatCurrency($invoice['discount']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($invoice['tax'] > 0): ?>
                                <tr>
                                    <td><strong>Tax (VAT)</strong></td>
                                    <td class="text-end">+<?php echo formatCurrency($invoice['tax']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-light" style="border-top: 2px solid #ddd;">
                                <th>Total Amount Due</th>
                                <th class="text-end"><?php echo formatCurrency($invoice['total']); ?></th>
                            </tr>
                        </tbody>
                    </table>

                    <?php if (count($payments) > 0): ?>
                        <div class="mt-4">
                            <h6><strong>Payment History</strong></h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo formatDate($payment['payment_date'], 'Y-m-d H:i'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                                            <td><span class="badge bg-<?php echo $payment['status'] === 'Completed' ? 'success' : 'warning'; ?>"><?php echo $payment['status']; ?></span></td>
                                            <td><?php echo htmlspecialchars($payment['reference_number'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <strong>Payment Summary:</strong> Total Paid: <?php echo formatCurrency($total_paid); ?> | Remaining: <?php echo formatCurrency($remaining); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="mt-4">
                            <h6><strong>Notes</strong></h6>
                            <p><?php echo htmlspecialchars($invoice['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
