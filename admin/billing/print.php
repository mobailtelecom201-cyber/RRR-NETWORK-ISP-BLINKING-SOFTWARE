<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$invoice_id = $_GET['id'] ?? 0;

$query = "SELECT i.*, c.fullname, c.email, c.phone, c.address, c.customer_id FROM invoices i 
          JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .print-button { display: none; }
        }
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 30px;
            background: white;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }
        .invoice-header h1 { color: #667eea; margin: 0; }
        .invoice-info { display: flex; justify-content: space-between; margin: 20px 0; }
        .invoice-details { margin: 20px 0; }
        .summary { margin-top: 30px; text-align: right; }
        .summary-item { display: flex; justify-content: space-between; margin: 10px 0; }
        .total-amount { font-weight: bold; font-size: 20px; color: #667eea; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Invoice</p>
        </div>
        
        <div class="invoice-info">
            <div>
                <h6>Bill To:</h6>
                <p>
                    <strong><?php echo htmlspecialchars($invoice['fullname']); ?></strong><br>
                    Customer ID: <?php echo htmlspecialchars($invoice['customer_id']); ?><br>
                    Phone: <?php echo htmlspecialchars($invoice['phone']); ?><br>
                    Email: <?php echo htmlspecialchars($invoice['email']); ?><br>
                    Address: <?php echo htmlspecialchars($invoice['address']); ?>
                </p>
            </div>
            <div>
                <h6>Invoice Details:</h6>
                <p>
                    Invoice #: <?php echo htmlspecialchars($invoice['invoice_number']); ?><br>
                    Issue Date: <?php echo formatDate($invoice['issue_date'], 'Y-m-d'); ?><br>
                    Due Date: <?php echo formatDate($invoice['due_date'], 'Y-m-d'); ?><br>
                    Amount Due: <?php echo formatCurrency($invoice['total']); ?>
                </p>
            </div>
        </div>
        
        <div class="invoice-details">
            <table class="table table-bordered">
                <thead>
                    <tr style="background: #f9f9f9;">
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Internet Service - Monthly Subscription</td>
                        <td style="text-align: right;"><?php echo formatCurrency($invoice['amount']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <span>Subtotal:</span>
                <span><?php echo formatCurrency($invoice['amount']); ?></span>
            </div>
            <div class="summary-item">
                <span>Discount:</span>
                <span>-<?php echo formatCurrency($invoice['discount']); ?></span>
            </div>
            <div class="summary-item">
                <span>Tax:</span>
                <span><?php echo formatCurrency($invoice['tax']); ?></span>
            </div>
            <div class="summary-item total-amount">
                <span>Total Amount Due:</span>
                <span><?php echo formatCurrency($invoice['total']); ?></span>
            </div>
        </div>
        
        <div style="margin-top: 40px; text-align: center; color: #999; font-size: 12px;">
            <p>Thank you for your business!</p>
            <p>Please make payment by the due date to avoid service interruption.</p>
        </div>
    </div>
    
    <div class="text-center mt-3 print-button">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print Invoice</button>
    </div>
</body>
</html>