<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN]);

$error = '';
$success = '';

// Get current settings
$app_name = getSetting($conn, 'APP_NAME') ?? APP_NAME;
$app_email = getSetting($conn, 'APP_EMAIL') ?? '';
$app_phone = getSetting($conn, 'APP_PHONE') ?? '';
$bkash_key = getSetting($conn, 'BKASH_API_KEY') ?? '';
$nagad_key = getSetting($conn, 'NAGAD_API_KEY') ?? '';
$rocket_key = getSetting($conn, 'ROCKET_API_KEY') ?? '';
$sms_gateway = getSetting($conn, 'SMS_GATEWAY_API') ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $setting_name = $_POST['setting_name'] ?? '';
    $setting_value = $_POST['setting_value'] ?? '';
    
    if (empty($setting_name) || empty($setting_value)) {
        $error = 'Please fill in all fields';
    } else {
        if (updateSetting($conn, $setting_name, $setting_value)) {
            $success = 'Setting updated successfully';
        } else {
            $error = 'Error updating setting';
        }
    }
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
        .nav-tabs { border-bottom: 2px solid #ddd; }
        .nav-tabs .nav-link { color: #667eea; border: none; }
        .nav-tabs .nav-link.active { color: white; background: #667eea; border-radius: 5px; }
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
                <a href="../../admin/settings/index.php" class="active"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">System Settings</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payment">Payment Gateway</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sms">SMS Configuration</a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="general" class="tab-pane fade show active">
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="APP_NAME">
                                    <div class="mb-3">
                                        <label class="form-label">Application Name</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($app_name); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                                
                                <hr>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="APP_EMAIL">
                                    <div class="mb-3">
                                        <label class="form-label">Application Email</label>
                                        <input type="email" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($app_email); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                                
                                <hr>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="APP_PHONE">
                                    <div class="mb-3">
                                        <label class="form-label">Application Phone</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($app_phone); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                            
                            <div id="payment" class="tab-pane fade">
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="BKASH_API_KEY">
                                    <div class="mb-3">
                                        <label class="form-label">bKash API Key</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($bkash_key); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                                
                                <hr>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="NAGAD_API_KEY">
                                    <div class="mb-3">
                                        <label class="form-label">Nagad API Key</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($nagad_key); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                                
                                <hr>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="ROCKET_API_KEY">
                                    <div class="mb-3">
                                        <label class="form-label">Rocket API Key</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($rocket_key); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                            
                            <div id="sms" class="tab-pane fade">
                                <form method="POST" action="">
                                    <input type="hidden" name="setting_name" value="SMS_GATEWAY_API">
                                    <div class="mb-3">
                                        <label class="form-label">SMS Gateway API URL</label>
                                        <input type="text" name="setting_value" class="form-control" value="<?php echo htmlspecialchars($sms_gateway); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
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