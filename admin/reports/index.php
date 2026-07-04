<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Total Revenue
$revenue_query = "SELECT SUM(total) as total_revenue FROM invoices WHERE status = 'Paid' AND issue_date BETWEEN ? AND ?";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$revenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

// Total Invoices
$invoices_query = "SELECT COUNT(*) as total_invoices FROM invoices WHERE issue_date BETWEEN ? AND ?";
$stmt = $conn->prepare($invoices_query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$total_invoices = $stmt->get_result()->fetch_assoc()['total_invoices'] ?? 0;

// Total Customers
$customers_query = "SELECT COUNT(*) as total_customers FROM customers";
$customers = $conn->query($customers_query)->fetch_assoc()['total_customers'] ?? 0;

// Total Expenses
$expenses_query = "SELECT SUM(amount) as total_expenses FROM expenses WHERE status = 'Approved' AND expense_date BETWEEN ? AND ?";
$stmt = $conn->prepare($expenses_query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$total_expenses = $stmt->get_result()->fetch_assoc()['total_expenses'] ?? 0;

// Payment Methods Summary
$payment_query = "SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM payments WHERE status = 'Completed' AND payment_date BETWEEN ? AND ? GROUP BY payment_method";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$payment_methods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top Customers
$top_customers_query = "SELECT c.fullname, COUNT(i.id) as invoice_count, SUM(i.total) as total_spent FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.issue_date BETWEEN ? AND ? GROUP BY c.id ORDER BY total_spent DESC LIMIT 10";
$stmt = $conn->prepare($top_customers_query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$top_customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #667eea; margin: 10px 0; }
        .stat-label { font-size: 14px; color: #666; }
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
                <a href="../../admin/expenses/index.php"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="../../admin/reports/index.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Report Filter</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-2" style="padding-top: 32px;">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value"><?php echo formatCurrency($revenue); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Invoices</div>
                            <div class="stat-value"><?php echo $total_invoices; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Customers</div>
                            <div class="stat-value"><?php echo $customers; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Expenses</div>
                            <div class="stat-value"><?php echo formatCurrency($total_expenses); ?></div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Methods</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Method</th>
                                                <th>Count</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payment_methods as $method): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($method['payment_method']); ?></td>
                                                    <td><?php echo $method['count']; ?></td>
                                                    <td><?php echo formatCurrency($method['total']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Top 10 Customers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Invoices</th>
                                                <th>Total Spent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($customer['fullname']); ?></td>
                                                    <td><?php echo $customer['invoice_count']; ?></td>
                                                    <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>