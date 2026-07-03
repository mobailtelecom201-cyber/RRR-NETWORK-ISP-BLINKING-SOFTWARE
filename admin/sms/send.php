<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN]);

$customers_query = "SELECT id, customer_id, fullname, phone FROM customers WHERE status = 'Active'";
$customers = $conn->query($customers_query)->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    $sms_type = $_POST['sms_type'] ?? 'Custom';
    
    if (empty($phone) || empty($message)) {
        $error = 'Please fill in all fields';
    } else {
        // Save to SMS logs
        $insert_query = "INSERT INTO sms_logs (phone_number, message, sms_type, status, created_at) 
                         VALUES (?, ?, ?, 'Queued', NOW())";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('sss', $phone, $message, $sms_type);
        
        if ($stmt->execute()) {
            // In production, integrate with actual SMS gateway
            // sendSMS($phone, $message, $sms_type);
            
            $success = 'SMS queued for sending';
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send SMS - <?php echo APP_NAME; ?></title>
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
                <h5 class="mb-0">Send SMS Notification</h5>
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
                        <label class="form-label">Select Customer or Enter Phone</label>
                        <select class="form-control" onchange="setPhone(this.value)">
                            <option value="">-- Type Phone Number --</option>
                            <?php foreach ($customers as $cust): ?>
                                <option value="<?php echo $cust['phone']; ?>"><?php echo htmlspecialchars($cust['fullname']) . ' - ' . htmlspecialchars($cust['phone']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" id="phone" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message Type</label>
                        <select name="sms_type" class="form-control">
                            <option value="Custom">Custom</option>
                            <option value="Invoice">Invoice Notification</option>
                            <option value="Payment Reminder">Payment Reminder</option>
                            <option value="Disconnection Warning">Disconnection Warning</option>
                            <option value="Activation">Activation</option>
                            <option value="Deactivation">Deactivation</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                        <small class="text-muted">Character count: <span id="char_count">0</span>/160</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-send"></i> Send SMS</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function setPhone(phone) {
            document.getElementById('phone').value = phone;
        }
        
        document.querySelector('textarea[name="message"]').addEventListener('keyup', function() {
            document.getElementById('char_count').textContent = this.value.length;
        });
    </script>
</body>
</html>