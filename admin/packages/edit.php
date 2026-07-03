<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$package_id = $_GET['id'] ?? 0;

$query = "SELECT * FROM packages WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_name = $_POST['package_name'] ?? '';
    $speed = $_POST['speed'] ?? '';
    $upload_speed = $_POST['upload_speed'] ?? '';
    $download_speed = $_POST['download_speed'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? 'Active';
    $description = $_POST['description'] ?? '';
    
    $update_query = "UPDATE packages SET package_name=?, speed=?, upload_speed=?, download_speed=?, price=?, status=?, description=? WHERE id=?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('ssssdsi', $package_name, $speed, $upload_speed, $download_speed, $price, $status, $description, $package_id);
    
    if ($update_stmt->execute()) {
        logAudit($conn, $_SESSION['admin_id'], 'Package Updated', 'packages', $package_id);
        header('Location: index.php?success=Updated');
        exit();
    } else {
        $error = 'Error: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package - <?php echo APP_NAME; ?></title>
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
                <h5 class="mb-0">Edit Package</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Name</label>
                            <input type="text" name="package_name" class="form-control" value="<?php echo htmlspecialchars($package['package_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Speed (Mbps)</label>
                            <input type="text" name="speed" class="form-control" value="<?php echo htmlspecialchars($package['speed']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Speed</label>
                            <input type="text" name="upload_speed" class="form-control" value="<?php echo htmlspecialchars($package['upload_speed'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Download Speed</label>
                            <input type="text" name="download_speed" class="form-control" value="<?php echo htmlspecialchars($package['download_speed'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monthly Price (BDT)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $package['price']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="Active" <?php echo $package['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $package['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($package['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>