<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$invoice_id = $_GET['invoice_id'] ?? 0;
$invoices = [];

if ($invoice_id > 0) {
    $query = "SELECT id, invoice_number, customer_id, total FROM invoices WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $query = "SELECT id, invoice_number, customer_id, total FROM invoices WHERE payment_status != 'Paid' ORDER BY id DESC";
    $invoices = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_id = $_POST['invoice_id'] ?? 0;
    $customer_id = $_POST['customer_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    $reference_number = $_POST['reference_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($invoice_id) || empty($amount) || empty($payment_method)) {
        $error = 'Please fill in required fields';
    } else {
        $payment_date = date('Y-m-d H:i:s');
        
        $insert_query = "INSERT INTO payments (invoice_id, customer_id, amount, payment_method, transaction_id, payment_date, status, reference_number, notes) 
                         VALUES (?, ?, ?, ?, ?, ?, 'Completed', ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('iidsssss', $invoice_id, $customer_id, $amount, $payment_method, $transaction_id, $payment_date, $reference_number, $notes);
        
        if ($stmt->execute()) {
            // Update invoice payment status
            $update_query = "UPDATE invoices SET payment_status = 'Paid', status = 'Paid' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('i', $invoice_id);
            $update_stmt->execute();
            
            logAudit($conn, $_SESSION['admin_id'], 'Payment Recorded', 'payments', $conn->insert_id);
            header('Location: index.php?success=Payment recorded successfully');
            exit();
        } else {
            $error = 'Error: ' . $conn->error;
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
    <style>
        body { background: #f5f7fa; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-network-wired"></i> <?php echo APP_NAME; ?></a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Record Payment</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice *</label>
                            <select name="invoice_id" class="form-control" required onchange="updateCustomer()">
                                <option value="">Select Invoice</option>
                                <?php foreach ($invoices as $inv): ?>
                                    <option value="<?php echo $inv['id']; ?>" data-customer="<?php echo $inv['customer_id']; ?>"><?php echo htmlspecialchars($inv['invoice_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Select Method</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="customer_id" id="customer_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (BDT) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function updateCustomer() {
            var select = document.querySelector('select[name="invoice_id"]');
            var option = select.options[select.selectedIndex];
            document.getElementById('customer_id').value = option.dataset.customer;
        }
    </script>
</body>
</html>