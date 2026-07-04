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

$query = "SELECT e.*, a.fullname as approved_by_name FROM expenses e LEFT JOIN admins a ON e.approved_by = a.id WHERE e.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $expense_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$expense = $result->fetch_assoc();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    
    if ($action === 'approve') {
        $update_query = "UPDATE expenses SET status = 'Approved', approved_by = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ii', $_SESSION['admin_id'], $expense_id);
        
        if ($stmt->execute()) {
            logAudit($conn, $_SESSION['admin_id'], 'Approve Expense', 'expenses', $expense_id, null, null);
            header('Location: index.php?success=Expense approved');
            exit();
        }
    } else if ($action === 'reject') {
        $update_query = "UPDATE expenses SET status = 'Rejected', approved_by = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ii', $_SESSION['admin_id'], $expense_id);
        
        if ($stmt->execute()) {
            logAudit($conn, $_SESSION['admin_id'], 'Reject Expense', 'expenses', $expense_id, null, null);
            header('Location: index.php?error=Expense rejected');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Expense - <?php echo APP_NAME; ?></title>
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
                        <h5 class="mb-0"><i class="fas fa-eye"></i> Expense Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Category</strong></label>
                                <p><?php echo htmlspecialchars($expense['category']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Amount</strong></label>
                                <p class="h5" style="color: #667eea;"><?php echo formatCurrency($expense['amount']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Expense Date</strong></label>
                                <p><?php echo formatDate($expense['expense_date'], 'Y-m-d'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Status</strong></label>
                                <p>
                                    <?php 
                                    $badge_class = $expense['status'] === 'Approved' ? 'success' : ($expense['status'] === 'Rejected' ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?> p-2"><?php echo $expense['status']; ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label"><strong>Description</strong></label>
                                <div class="alert alert-light" style="border: 1px solid #ddd;">
                                    <p><?php echo htmlspecialchars($expense['description']); ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($expense['approved_by_name'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Approved By</strong></label>
                                    <p><?php echo htmlspecialchars($expense['approved_by_name']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-12">
                                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                                
                                <?php if ($expense['status'] === 'Pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-times"></i> Reject</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="edit.php?id=<?php echo $expense['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $expense['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>