<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mikrotik_host = $_POST['mikrotik_host'] ?? '';
    $mikrotik_username = $_POST['mikrotik_username'] ?? '';
    $mikrotik_password = $_POST['mikrotik_password'] ?? '';
    $mikrotik_port = $_POST['mikrotik_port'] ?? 8728;
    
    if (empty($mikrotik_host) || empty($mikrotik_username) || empty($mikrotik_password)) {
        $error = 'Please fill in all required fields';
    } else {
        $settings = [
            'MIKROTIK_HOST' => $mikrotik_host,
            'MIKROTIK_USERNAME' => $mikrotik_username,
            'MIKROTIK_PASSWORD' => $mikrotik_password,
            'MIKROTIK_PORT' => $mikrotik_port
        ];
        
        foreach ($settings as $key => $value) {
            updateSetting($conn, $key, $value);
        }
        
        $success = 'Settings saved successfully';
    }
}

$host = getSetting($conn, 'MIKROTIK_HOST');
$username = getSetting($conn, 'MIKROTIK_USERNAME');
$port = getSetting($conn, 'MIKROTIK_PORT') ?? 8728;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MikroTik Settings - <?php echo APP_NAME; ?></title>
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
                <h5 class="mb-0">MikroTik Configuration</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">MikroTik Host/IP *</label>
                        <input type="text" name="mikrotik_host" class="form-control" value="<?php echo htmlspecialchars($host ?? ''); ?>" required>
                        <small class="text-muted">Example: 192.168.1.1</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="mikrotik_username" class="form-control" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="mikrotik_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Port</label>
                        <input type="number" name="mikrotik_port" class="form-control" value="<?php echo htmlspecialchars($port); ?>">
                        <small class="text-muted">Default: 8728</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                        <a href="index.php" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>