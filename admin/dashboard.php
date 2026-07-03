<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirect('login.php');
}

$admin_name = $_SESSION['admin_fullname'];
$admin_role = $_SESSION['admin_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sidebar {
            background: #2c3e50;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
            border-left-color: #3498db;
        }
        .sidebar a.active {
            background: #34495e;
            border-left-color: #3498db;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .stat-box {
            padding: 20px;
            border-radius: 8px;
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-box.customers {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-box.revenue {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-box.packages {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-box.invoices {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-network-wired"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $admin_name; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Profile Settings</a></li>
                            <li><a class="dropdown-item" href="#">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="#"><i class="fas fa-users"></i> Customers</a>
                <a href="#"><i class="fas fa-box"></i> Packages</a>
                <a href="#"><i class="fas fa-file-invoice"></i> Invoices</a>
                <a href="#"><i class="fas fa-money-bill"></i> Payments</a>
                <a href="#"><i class="fas fa-rss"></i> MikroTik</a>
                <a href="#"><i class="fas fa-sms"></i> SMS</a>
                <a href="#"><i class="fas fa-wallet"></i> Expenses</a>
                <a href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-box customers">
                            <div class="stat-label">Total Customers</div>
                            <div class="stat-number">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box revenue">
                            <div class="stat-label">Monthly Revenue</div>
                            <div class="stat-number">0 BDT</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box packages">
                            <div class="stat-label">Active Packages</div>
                            <div class="stat-number">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box invoices">
                            <div class="stat-label">Pending Invoices</div>
                            <div class="stat-number">0</div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Welcome Back, <?php echo $admin_name; ?>!</h5>
                    </div>
                    <div class="card-body">
                        <p>Your role: <strong><?php echo $admin_role; ?></strong></p>
                        <p>This is your dashboard. Use the sidebar to navigate through different modules.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>