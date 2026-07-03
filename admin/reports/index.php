<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$page = $_GET['page'] ?? 1;
$limit = 30;
$offset = ($page - 1) * $limit;
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Revenue Report
$revenue_query = "SELECT SUM(total) as total_revenue, COUNT(id) as total_invoices FROM invoices 
                  WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'Paid'";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_assoc();

// Collection Report
$collection_query = "SELECT SUM(amount) as total_collected FROM payments 
                     WHERE DATE(payment_date) BETWEEN ? AND ? AND status = 'Completed'";
$stmt = $conn->prepare($collection_query);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$collection_data = $stmt->get_result()->fetch_assoc();

// Expense Report
$expense_query = "SELECT SUM(amount) as total_expenses FROM expenses 
                  WHERE DATE(expense_date) BETWEEN ? AND ? AND status = 'Approved'";
$stmt = $conn->prepare($expense_query);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$expense_data = $stmt->get_result()->fetch_assoc();

// Customer Report
$customer_query = "SELECT COUNT(id) as total_customers, 
                   SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_customers
                   FROM customers";
$customer_data = $conn->query($customer_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .stat-card { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; }
        .stat-label { font-size: 14px; opacity: 0.9; }
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
                <a href="../../admin/reports/index.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3" style="padding-top: 32px;">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                                <button type="button" class="btn btn-success" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-number"><?php echo formatCurrency($revenue_data['total_revenue'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="stat-label">Total Collected</div>
                            <div class="stat-number"><?php echo formatCurrency($collection_data['total_collected'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="stat-label">Total Expenses</div>
                            <div class="stat-number"><?php echo formatCurrency($expense_data['total_expenses'] ?? 0); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="stat-label">Active Customers</div>
                            <div class="stat-number"><?php echo $customer_data['active_customers']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Revenue Summary</h5>
                            </div>
                            <div class="card-body">
                                <p>Total Invoices: <strong><?php echo $revenue_data['total_invoices']; ?></strong></p>
                                <p>Total Revenue: <strong><?php echo formatCurrency($revenue_data['total_revenue'] ?? 0); ?></strong></p>
                                <p>Average Invoice: <strong><?php echo $revenue_data['total_invoices'] > 0 ? formatCurrency($revenue_data['total_revenue'] / $revenue_data['total_invoices']) : formatCurrency(0); ?></strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Summary</h5>
                            </div>
                            <div class="card-body">
                                <p>Total Customers: <strong><?php echo $customer_data['total_customers']; ?></strong></p>
                                <p>Active Customers: <strong><?php echo $customer_data['active_customers']; ?></strong></p>
                                <p>Inactive Customers: <strong><?php echo $customer_data['total_customers'] - $customer_data['active_customers']; ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>