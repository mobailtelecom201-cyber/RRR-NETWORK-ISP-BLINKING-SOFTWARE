<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$customers_query = "SELECT id, customer_id, fullname FROM customers WHERE status = 'Active'";
$customers = $conn->query($customers_query)->fetch_all(MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? 0;
    $billing_id = $_POST['billing_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $tax = $_POST['tax'] ?? 0;
    $due_date = $_POST['due_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($customer_id) || empty($amount) || empty($due_date)) {
        $error = 'Please fill in required fields';
    } else {
        $invoice_number = generateInvoiceNumber();
        $total = $amount - $discount + $tax;
        $issue_date = date('Y-m-d');
        
        $insert_query = "INSERT INTO invoices (invoice_number, customer_id, billing_id, issue_date, due_date, amount, discount, tax, total, status, payment_status, notes) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft', 'Unpaid', ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('siisddddds', $invoice_number, $customer_id, $billing_id, $issue_date, $due_date, $amount, $discount, $tax, $total, $notes);
        
        if ($stmt->execute()) {
            logAudit($conn, $_SESSION['admin_id'], 'Invoice Created', 'invoices', $conn->insert_id);
            header('Location: view.php?id=' . $conn->insert_id . '&success=Invoice created');
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
    <title>Create Invoice - <?php echo APP_NAME; ?></title>
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
                <h5 class="mb-0">Create New Invoice</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer *</label>
                            <select name="customer_id" class="form-control" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $cust): ?>
                                    <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['fullname']) . ' (' . htmlspecialchars($cust['customer_id']) . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Amount (BDT) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Discount (BDT)</label>
                            <input type="number" name="discount" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tax (BDT)</label>
                            <input type="number" name="tax" class="form-control" step="0.01" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Invoice</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>