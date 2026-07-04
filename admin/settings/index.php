<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$success = '';
$error = '';

// Get current settings
$company_name = getSetting($conn, 'company_name') ?? 'RRR Network ISP';
$company_email = getSetting($conn, 'company_email') ?? 'info@example.com';
$company_phone = getSetting($conn, 'company_phone') ?? '+880';
$company_address = getSetting($conn, 'company_address') ?? '';
$sms_gateway_api = getSetting($conn, 'sms_gateway_api') ?? '';
$email_from = getSetting($conn, 'email_from') ?? 'noreply@example.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitizeInput($_POST['company_name'] ?? '');
    $company_email = sanitizeInput($_POST['company_email'] ?? '');
    $company_phone = sanitizeInput($_POST['company_phone'] ?? '');
    $company_address = sanitizeInput($_POST['company_address'] ?? '');
    $sms_gateway_api = sanitizeInput($_POST['sms_gateway_api'] ?? '');
    $email_from = sanitizeInput($_POST['email_from'] ?? '');

    // Update settings
    updateSetting($conn, 'company_name', $company_name);
    updateSetting($conn, 'company_email', $company_email);
    updateSetting($conn, 'company_phone', $company_phone);
    updateSetting($conn, 'company_address', $company_address);
    updateSetting($conn, 'sms_gateway_api', $sms_gateway_api);
    updateSetting($conn, 'email_from', $email_from);

    logAudit($conn, $_SESSION['admin_id'], 'Update Settings', 'settings', 0, null, null);
    $success = 'Settings updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #2c3e50; min-height: 100vh; padding: 20px 0; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; display: block; }
        .sidebar a.active { background: #34495e; border-left-color: #3498db; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .nav-tabs { border-bottom: 2px solid #eee; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #667eea; color: #667eea; }
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
                <a href="../../admin/reports/index.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="../../admin/settings/index.php" class="active"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> System Settings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <h6 class="text-muted mb-3"><i class="fas fa-building"></i> Company Information</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_name); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Email</label>
                                <input type="email" name="company_email" class="form-control" value="<?php echo htmlspecialchars($company_email); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Phone</label>
                                <input type="text" name="company_phone" class="form-control" value="<?php echo htmlspecialchars($company_phone); ?>">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Company Address</label>
                                <textarea name="company_address" class="form-control" rows="3"><?php echo htmlspecialchars($company_address); ?></textarea>
                            </div>

                            <div class="col-12" style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px;">
                                <h6 class="text-muted mb-3"><i class="fas fa-cog"></i> Integration Settings</h6>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">SMS Gateway API Key</label>
                                <input type="password" name="sms_gateway_api" class="form-control" value="<?php echo htmlspecialchars($sms_gateway_api); ?>" placeholder="Enter your SMS gateway API key">
                                <small class="form-text text-muted">Used for sending SMS notifications</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Email From Address</label>
                                <input type="email" name="email_from" class="form-control" value="<?php echo htmlspecialchars($email_from); ?>">
                                <small class="form-text text-muted">Sender email for system notifications</small>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                                <a href="../../admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                            </div>
                        </form>

                        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle"></i> System Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Application:</strong> <?php echo APP_NAME; ?></p>
                                    <p><strong>Version:</strong> 1.0.0</p>
                                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Database:</strong> Active</p>
                                    <p><strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>