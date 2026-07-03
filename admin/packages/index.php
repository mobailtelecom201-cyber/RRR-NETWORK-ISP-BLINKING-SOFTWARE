<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$query = "SELECT * FROM packages ORDER BY id DESC";
$result = $conn->query($query);
$packages = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - <?php echo APP_NAME; ?></title>
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
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <a href="../../admin/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="../../admin/customers/index.php"><i class="fas fa-users"></i> Customers</a>
                <a href="../../admin/packages/index.php" class="active"><i class="fas fa-box"></i> Packages</a>
                <a href="../../admin/billing/index.php"><i class="fas fa-file-invoice"></i> Invoices</a>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Package Management</h5>
                        <a href="create.php" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Add Package</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($packages as $pkg): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($pkg['package_name']); ?></h5>
                                            <p class="card-text">
                                                <strong>Speed:</strong> <?php echo htmlspecialchars($pkg['speed']); ?><br>
                                                <strong>Price:</strong> <?php echo formatCurrency($pkg['price']); ?>/month<br>
                                                <strong>Status:</strong> <span class="badge bg-<?php echo $pkg['status'] === 'Active' ? 'success' : 'danger'; ?>"><?php echo $pkg['status']; ?></span>
                                            </p>
                                            <a href="edit.php?id=<?php echo $pkg['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="delete.php?id=<?php echo $pkg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete package?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>