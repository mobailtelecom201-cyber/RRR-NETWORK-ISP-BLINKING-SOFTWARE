<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

checkPermission([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_OPERATOR]);

$invoice_id = intval($_GET['id'] ?? 0);

if ($invoice_id <= 0) {
    header('Location: index.php');
    exit();
}

$query = "SELECT i.*, c.fullname, c.customer_id, c.email, c.phone, c.address FROM invoices i 
          JOIN customers c ON i.customer_id = c.id WHERE i.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$invoice = $result->fetch_assoc();

// Fetch payments
$payments_query = "SELECT * FROM payments WHERE invoice_id = ? AND status = 'Completed' ORDER BY payment_date DESC";
$stmt = $conn->prepare($payments_query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$payments_result = $stmt->get_result();
$payments = $payments_result->fetch_all(MYSQLI_ASSOC);

$total_paid = 0;
foreach ($payments as $payment) {
    $total_paid += $payment['amount'];
}

$remaining = $invoice['total'] - $total_paid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: white; }
        .container { max-width: 850px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .company-info h2 { font-size: 28px; margin-bottom: 5px; }
        .invoice-info { text-align: right; }
        .invoice-info h3 { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
        .details { margin-bottom: 30px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .detail-column { flex: 1; }
        .detail-column h5 { font-weight: bold; margin-bottom: 10px; }
        .detail-column p { margin: 5px 0; font-size: 14px; line-height: 1.6; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #f5f5f5; border-top: 2px solid #333; border-bottom: 2px solid #333; padding: 12px; text-align: left; font-weight: bold; }
        .table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .table tr:last-child td { border-bottom: 2px solid #333; }
        .summary { margin-top: 30px; float: right; width: 300px; }
        .summary-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; }
        .summary-item.total { font-weight: bold; border-bottom: 2px solid #333; margin-top: 10px; padding-top: 10px; font-size: 16px; }
        .clearfix { clear: both; }
        .notes { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; }
        .notes h6 { font-weight: bold; margin-bottom: 10px; }
        .status-box { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-top: 20px; }
        @media print {
            body { margin: 0; padding: 0; }
            .container { max-width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                <h2><?php echo APP_NAME; ?></h2>
                <p><small>Professional ISP Billing Solution</small></p>
            </div>
            <div class="invoice-info">
                <h3>INVOICE</h3>
                <p><strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
            </div>
        </div>

        <div class="details">
            <div class="details-row">
                <div class="detail-column">
                    <h5>Bill To:</h5>
                    <p><strong><?php echo htmlspecialchars($invoice['fullname']); ?></strong></p>
                    <p>Customer ID: <?php echo htmlspecialchars($invoice['customer_id']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($invoice['email']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($invoice['phone']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($invoice['address']); ?></p>
                </div>
                <div class="detail-column" style="text-align: right;">
                    <p><strong>Invoice Date:</strong> <?php echo formatDate($invoice['issue_date'], 'Y-m-d'); ?></p>
                    <p><strong>Due Date:</strong> <?php echo formatDate($invoice['due_date'], 'Y-m-d'); ?></p>
                    <p><strong>Invoice Status:</strong> <?php echo htmlspecialchars($invoice['status']); ?></p>
                    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($invoice['payment_status']); ?></p>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Internet Service / Monthly Subscription</td>
                    <td style="text-align: right;"><?php echo formatCurrency($invoice['amount']); ?></td>
                </tr>
                <?php if ($invoice['discount'] > 0): ?>
                    <tr>
                        <td><strong>Discount</strong></td>
                        <td style="text-align: right;"><strong>-<?php echo formatCurrency($invoice['discount']); ?></strong></td>
                    </tr>
                <?php endif; ?>
                <?php if ($invoice['tax'] > 0): ?>
                    <tr>
                        <td><strong>Tax (VAT)</strong></td>
                        <td style="text-align: right;"><strong>+<?php echo formatCurrency($invoice['tax']); ?></strong></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="summary">
            <?php if ($invoice['discount'] > 0 || $invoice['tax'] > 0): ?>
                <?php if ($invoice['discount'] > 0): ?>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span><?php echo formatCurrency($invoice['amount']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Discount:</span>
                        <span>-<?php echo formatCurrency($invoice['discount']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($invoice['tax'] > 0): ?>
                    <div class="summary-item">
                        <span>Tax (VAT):</span>
                        <span>+<?php echo formatCurrency($invoice['tax']); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="summary-item total">
                <span>Total Amount Due:</span>
                <span><?php echo formatCurrency($invoice['total']); ?></span>
            </div>
        </div>

        <div class="clearfix"></div>

        <?php if (count($payments) > 0): ?>
            <div class="status-box">
                <h6>Payment Information</h6>
                <p><strong>Total Paid:</strong> <?php echo formatCurrency($total_paid); ?></p>
                <p><strong>Remaining Balance:</strong> <?php echo formatCurrency($remaining); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($invoice['notes'])): ?>
            <div class="notes">
                <h6>Notes:</h6>
                <p><?php echo htmlspecialchars($invoice['notes']); ?></p>
            </div>
        <?php endif; ?>

        <div class="notes" style="margin-top: 50px; text-align: center; border-top: 1px solid #ddd;">
            <p><small>This is an electronically generated invoice. No signature is required.</small></p>
            <p><small>Thank you for your business!</small></p>
            <p><small>Printed on: <?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
