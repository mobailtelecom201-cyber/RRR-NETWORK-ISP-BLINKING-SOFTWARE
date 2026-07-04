<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$expense_id = intval($_GET['id'] ?? 0);
$error = '';

if ($expense_id <= 0) {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM expenses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $expense_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$expense = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitizeInput($_POST['category'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = sanitizeInput($_POST['expense_date'] ?? '');

    if (empty($category) || empty($description) || $amount <= 0 || empty($expense_date)) {
        $error = 'Please fill in all required fields';
    } else {
        $update_query = "UPDATE expenses SET category = ?, description = ?, amount = ?, expense_date = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssdsi', $category, $description, $amount, $expense_date, $expense_id);
        
        if ($stmt->execute()) {
            logAudit($conn, $_SESSION['admin_id'], 'Update Expense', 'expenses', $expense_id, $expense, ['category' => $category, 'amount' => $amount]);
            header('Location: view.php?id=' . $expense_id . '&success=Expense updated');
            exit();
        } else {
            $error = 'Error updating expense: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
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
                <a href="../../admin/billing/index.php"><i class="fas fa-file-invoice"></i> Invoices</a>
                <a href="../../admin/payments/index.php"><i class="fas fa-money-bill"></i> Payments</a>
                <a href="../../admin/mikrotik/index.php"><i class="fas fa-rss"></i> MikroTik</a>
                <a href="../../admin/sms/index.php"><i class="fas fa-sms"></i> SMS</a>
                <a href="../../admin/expenses/index.php" class="active"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="../../admin/reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Expense</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <input type="text" name="category" class="form-control" required value="<?php echo htmlspecialchars($expense['category']); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount (BDT) *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required value="<?php echo $expense['amount']; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Expense Date *</label>
                                <input type="date" name="expense_date" class="form-control" required value="<?php echo $expense['expense_date']; ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($expense['description']); ?></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Expense</button>
                                <a href="view.php?id=<?php echo $expense['id']; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>