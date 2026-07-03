<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "1=1";
if (!empty($search)) {
    $search = sanitizeInput($search);
    $where .= " AND (c.fullname LIKE '%$search%' OR p.transaction_id LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $status_filter = sanitizeInput($status_filter);
    $where .= " AND p.status = '$status_filter'";
}

$count_query = "SELECT COUNT(*) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE $where";
$count_result = $conn->query($count_query);
$total = $count_result->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$query = "SELECT p.*, c.fullname FROM payments p JOIN customers c ON p.customer_id = c.id WHERE $where ORDER BY p.id DESC LIMIT $offset, $limit";
$result = $conn->query($query);
$payments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .badge { padding: 8px 12px; }
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
                <a href="../../admin/payments/index.php" class="active"><i class="fas fa-money-bill"></i> Payments</a>
                <a href="../../admin/mikrotik/index.php"><i class="fas fa-rss"></i> MikroTik</a>
                <a href="../../admin/sms/index.php"><i class="fas fa-sms"></i> SMS</a>
                <a href="../../admin/expenses/index.php"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="../../admin/reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment Management</h5>
                        <a href="create.php" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Record Payment</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Search by customer or transaction ID...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" onchange="location.href='?status=' + this.value;">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Failed" <?php echo $status_filter === 'Failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Customer</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></strong></td>
                                            <td><?php echo htmlspecialchars($payment['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                                            <td><?php echo formatDate($payment['payment_date'], 'Y-m-d H:i'); ?></td>
                                            <td>
                                                <?php 
                                                $badge_class = $payment['status'] === 'Completed' ? 'success' : ($payment['status'] === 'Pending' ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $payment['status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($pages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>