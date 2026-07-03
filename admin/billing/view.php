<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$invoice_id = $_GET['id'] ?? 0;

$query = "SELECT i.*, c.fullname, c.email, c.phone, c.address, c.customer_id FROM invoices i 
          JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    redirect('index.php');
}

// Get payments for this invoice
$payments_query = "SELECT * FROM payments WHERE invoice_id = ?";
$payments_stmt = $conn->prepare($payments_query);
$payments_stmt->bind_param('i', $invoice_id);
$payments_stmt->execute();
$payments = $payments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .invoice-summary { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-item.total { font-weight: bold; font-size: 18px; color: #667eea; border-top: 2px solid #667eea; padding-top: 10px; }
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
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p>
                                    <strong><?php echo htmlspecialchars($invoice['fullname']); ?></strong><br>
                                    Customer ID: <?php echo htmlspecialchars($invoice['customer_id']); ?><br>
                                    Email: <?php echo htmlspecialchars($invoice['email']); ?><br>
                                    Phone: <?php echo htmlspecialchars($invoice['phone']); ?><br>
                                    Address: <?php echo htmlspecialchars($invoice['address']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Invoice Details</h6>
                                <p>
                                    Issue Date: <?php echo formatDate($invoice['issue_date'], 'Y-m-d'); ?><br>
                                    Due Date: <?php echo formatDate($invoice['due_date'], 'Y-m-d'); ?><br>
                                    Status: <span class="badge bg-info"><?php echo $invoice['status']; ?></span><br>
                                    Payment Status: <span class="badge bg-<?php echo $invoice['payment_status'] === 'Paid' ? 'success' : 'danger'; ?>"><?php echo $invoice['payment_status']; ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="invoice-summary">
                            <div class="summary-item">
                                <span>Subtotal:</span>
                                <span><?php echo formatCurrency($invoice['amount']); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Discount:</span>
                                <span>-<?php echo formatCurrency($invoice['discount']); ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Tax:</span>
                                <span><?php echo formatCurrency($invoice['tax']); ?></span>
                            </div>
                            <div class="summary-item total">
                                <span>Total Due:</span>
                                <span><?php echo formatCurrency($invoice['total']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($invoice['notes'])): ?>
                            <div class="mt-3">
                                <h6>Notes</h6>
                                <p><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($payments)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                                            <td><span class="badge bg-success"><?php echo $payment['status']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="edit.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary w-100 mb-2"><i class="fas fa-edit"></i> Edit</a>
                        <a href="print.php?id=<?php echo $invoice['id']; ?>" class="btn btn-success w-100 mb-2" target="_blank"><i class="fas fa-print"></i> Print</a>
                        <a href="../payments/create.php?invoice_id=<?php echo $invoice['id']; ?>" class="btn btn-info w-100 mb-2"><i class="fas fa-money-bill"></i> Record Payment</a>
                        <a href="index.php" class="btn btn-secondary w-100"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>