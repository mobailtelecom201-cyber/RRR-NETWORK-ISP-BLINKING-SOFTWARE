<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_TECHNICIAN]);

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as total FROM mikrotik_connections";
$total = $conn->query($count_query)->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$query = "SELECT m.*, c.fullname, c.customer_id FROM mikrotik_connections m 
          JOIN customers c ON m.customer_id = c.id 
          ORDER BY m.id DESC LIMIT $offset, $limit";
$result = $conn->query($query);
$connections = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MikroTik Integration - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .stat-card { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .stat-number { font-size: 28px; font-weight: bold; }
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
                <a href="../../admin/mikrotik/index.php" class="active"><i class="fas fa-rss"></i> MikroTik</a>
                <a href="../../admin/sms/index.php"><i class="fas fa-sms"></i> SMS</a>
                <a href="../../admin/expenses/index.php"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="../../admin/reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div>Active Connections</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number">Config</div>
                            <div><a href="settings.php" style="color: white; text-decoration: none;">MikroTik Settings</a></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number">Sync</div>
                            <div><a href="sync.php" style="color: white; text-decoration: none;">Sync Connections</a></div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">PPPoE Connections</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>PPPoE Username</th>
                                        <th>IP Address</th>
                                        <th>Uptime</th>
                                        <th>RX (GB)</th>
                                        <th>TX (GB)</th>
                                        <th>Last Sync</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($connections as $conn_data): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($conn_data['fullname']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($conn_data['pppoe_username']); ?></td>
                                            <td><?php echo htmlspecialchars($conn_data['remote_address'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($conn_data['uptime'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format(($conn_data['rx_bytes'] ?? 0) / (1024*1024*1024), 2); ?></td>
                                            <td><?php echo number_format(($conn_data['tx_bytes'] ?? 0) / (1024*1024*1024), 2); ?></td>
                                            <td><?php echo formatDate($conn_data['last_sync'], 'Y-m-d H:i'); ?></td>
                                            <td>
                                                <a href="disconnect.php?id=<?php echo $conn_data['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Disconnect this user?')"><i class="fas fa-ban"></i> Disconnect</a>
                                            </td>
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
</body>
</html>