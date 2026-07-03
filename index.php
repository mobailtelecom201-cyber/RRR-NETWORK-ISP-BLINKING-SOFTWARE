<?php
// Main entry point - Redirect to appropriate location

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

if (isset($_SESSION['customer_id'])) {
    header('Location: customer/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RRR Network ISP - Billing Software</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container-center {
            text-align: center;
            color: white;
        }
        .welcome-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 50px 40px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .welcome-box h1 {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .welcome-box p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 30px;
        }
        .btn-group-custom {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-custom {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-admin {
            background: white;
            color: #667eea;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            color: #667eea;
        }
        .btn-customer {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-customer:hover {
            background: white;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-center">
        <div class="welcome-box">
            <h1>RRR Network ISP</h1>
            <p>Professional Billing Software for Internet Service Providers</p>
            
            <div class="btn-group-custom">
                <a href="admin/login.php" class="btn-custom btn-admin">Admin Login</a>
                <a href="customer/login.php" class="btn-custom btn-customer">Customer Portal</a>
            </div>
        </div>
    </div>
</body>
</html>