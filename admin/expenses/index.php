<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "1=1";
if (!empty($search)) {
    $search = sanitizeInput($search);
    $where .= " AND (category LIKE '%$search%' OR description LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $status_filter = sanitizeInput($status_filter);
    $where .= " AND status = '$status_filter'";
}

$count_query = "SELECT COUNT(*) as total FROM expenses WHERE $where";
$count_result = $conn->query($count_query);
$total = $count_result->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$query = "SELECT e.*, a.fullname as approved_by_name FROM expenses e LEFT JOIN admins a ON e.approved_by = a.id WHERE $where ORDER BY e.expense_date DESC LIMIT $offset, $limit";
$result = $conn->query($query);
$expenses = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_query = "SELECT SUM(CASE WHEN status='Approved' THEN amount ELSE 0 END) as approved_total FROM expenses WHERE $where";
$total_result = $conn->query($total_query);
$totals = $total_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-value { font-size: 28px; font-weight: bold; color: #667eea; }
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="stat-box">
                            <small class="text-muted">Total Approved Expenses</small>
                            <div class="stat-value"><?php echo formatCurrency($totals['approved_total'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Expense Management</h5>
                        <a href="create.php" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Add Expense</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Search expense..." id="search_input">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="status_filter" onchange="location.href='?status=' + this.value;">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenses as $expense): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($expense['category']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($expense['description'], 0, 30)) . (strlen($expense['description']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo formatCurrency($expense['amount']); ?></td>
                                            <td><?php echo formatDate($expense['expense_date'], 'Y-m-d'); ?></td>
                                            <td>
                                                <?php 
                                                $badge_class = $expense['status'] === 'Approved' ? 'success' : ($expense['status'] === 'Rejected' ? 'danger' : 'warning');
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $expense['status']; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($expense['approved_by_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                <a href="edit.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                <a href="delete.php?id=<?php echo $expense['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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