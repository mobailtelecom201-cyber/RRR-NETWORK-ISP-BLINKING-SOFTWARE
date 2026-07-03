<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$customer_id = $_GET['id'] ?? 0;

$query = "SELECT c.*, p.package_name, p.price FROM customers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .info-group { margin-bottom: 15px; }
        .info-label { font-weight: 600; color: #667eea; }
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
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($customer['fullname']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <div class="info-label">Customer ID:</div>
                            <div><?php echo htmlspecialchars($customer['customer_id']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Email:</div>
                            <div><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone:</div>
                            <div><?php echo htmlspecialchars($customer['phone']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Address:</div>
                            <div><?php echo htmlspecialchars($customer['address']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Package:</div>
                            <div><?php echo htmlspecialchars($customer['package_name']); ?> - <?php echo formatCurrency($customer['price']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Status:</div>
                            <div><span class="badge bg-<?php echo $customer['status'] === 'Active' ? 'success' : 'danger'; ?>"><?php echo $customer['status']; ?></span></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Registered:</div>
                            <div><?php echo formatDate($customer['created_at']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary w-100 mb-2"><i class="fas fa-edit"></i> Edit</a>
                        <a href="../billing/index.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-info w-100 mb-2"><i class="fas fa-file-invoice"></i> Invoices</a>
                        <a href="index.php" class="btn btn-secondary w-100"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>