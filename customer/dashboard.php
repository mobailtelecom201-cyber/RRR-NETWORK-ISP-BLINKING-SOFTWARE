<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['customer_id'])) {
    redirect('login.php');
}

$customer_name = $_SESSION['customer_fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">RRR Network</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-user fa-3x mb-3" style="color: #667eea;"></i>
                        <h5><?php echo $customer_name; ?></h5>
                        <p class="text-muted">Customer Account</p>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5>Welcome Back!</h5>
                    </div>
                    <div class="card-body">
                        <p>You are logged in as a customer. This portal will allow you to manage your account, view invoices, and make payments.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>