<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("Sale ID is required.");
}

$sale_id = (int)$_GET['id'];

try {
    // Fetch Sale Details
    $stmt = $conn->prepare("SELECT s.*, c.first_name, c.last_name, u.username as staff_name 
                            FROM sales s 
                            LEFT JOIN customers c ON s.customer_id = c.id 
                            JOIN users u ON s.user_id = u.id 
                            WHERE s.id = ?");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();

    if (!$sale) {
        die("Sale not found.");
    }

    // Fetch Sale Items
    $stmt = $conn->prepare("SELECT si.*, m.name as medicine_name 
                            FROM sale_items si 
                            JOIN medicines m ON si.medicine_id = m.id 
                            WHERE si.sale_id = ?");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $sale_id; ?> - PharmaLynx POS</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            line-height: 1.4; 
            color: #000; 
            background: #f4f4f4; 
            padding: 10px;
        }
        
        .receipt { 
            background: #fff; 
            width: 100%; 
            max-width: 320px;
            margin: 0 auto 20px; 
            padding: 15px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            border: 1px solid #ddd;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 10px; 
        }
        
        .logo-container { 
            background: #fff; 
            padding: 0; 
            display: inline-block; 
            border-radius: 5px; 
            margin-bottom: 5px; 
        }
        
        .logo { 
            width: 100px; 
            height: auto; 
            display: block; 
            margin: 0 auto; 
        }
        
        .title { 
            font-size: 14px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin: 5px 0; 
            color: #8b0000; 
        }
        
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        .info { 
            margin-bottom: 12px; 
            font-size: 11px;
        }
        
        .info div { 
            display: flex; 
            justify-content: space-between; 
            padding: 2px 0;
            word-wrap: break-word;
        }
        
        .info span:first-child {
            font-weight: bold;
            min-width: 80px;
        }
        
        .info span:last-child {
            text-align: right;
            flex: 1;
        }
        
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 12px;
            font-size: 11px;
        }
        
        .table th { 
            text-align: left; 
            border-bottom: 1px solid #000; 
            padding: 4px 2px;
            font-weight: bold;
        }
        
        .table td { 
            padding: 3px 2px; 
            vertical-align: top;
        }
        
        .table th:nth-child(2),
        .table td:nth-child(2) {
            text-align: center;
        }
        
        .table th:nth-child(3),
        .table td:nth-child(3) {
            text-align: right;
        }
        
        .total-section { 
            border-top: 1px dashed #000; 
            padding-top: 8px;
            font-size: 11px;
            margin-bottom: 10px;
        }
        
        .total-row { 
            display: flex; 
            justify-content: space-between; 
            font-weight: bold; 
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .payment-info {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        
        .footer { 
            text-align: center; 
            margin-top: 10px; 
            font-size: 10px; 
            border-top: 1px dashed #000; 
            padding-top: 8px;
        }
        
        .footer p {
            margin: 0;
            line-height: 1.3;
        }

        .no-print-actions {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 320px;
            margin: 0 auto;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
        }

        .btn-print { 
            background: #8b0000; 
            color: #fff;
        }
        
        .btn-print:hover { 
            background: #a50000; 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-back { 
            background: #333; 
            color: #fff;
        }
        
        .btn-back:hover { 
            background: #555;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        @media print {
            body { 
                background: none; 
                padding: 0; 
            }
            .receipt { 
                box-shadow: none; 
                width: 100%; 
                max-width: none;
                border: none;
                padding: 0;
                margin: 0;
            }
            .no-print-actions { 
                display: none; 
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .receipt {
                max-width: 100%;
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .logo {
                width: 80px;
            }
            
            .title {
                font-size: 12px;
            }
            
            .header p {
                font-size: 10px;
            }
            
            .info {
                font-size: 10px;
                margin-bottom: 10px;
            }
            
            .info span:first-child {
                min-width: 70px;
            }
            
            .table {
                font-size: 10px;
                margin-bottom: 10px;
            }
            
            .table th {
                padding: 3px 1px;
            }
            
            .table td {
                padding: 2px 1px;
            }
            
            .total-row {
                font-size: 11px;
            }
            
            .footer {
                font-size: 9px;
                margin-top: 8px;
                padding-top: 6px;
            }
            
            .no-print-actions {
                max-width: 100%;
                gap: 8px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 13px;
            }
        }

        @media (max-width: 360px) {
            .receipt {
                padding: 10px;
            }
            
            .logo {
                width: 70px;
            }
            
            .title {
                font-size: 11px;
            }
            
            .info {
                font-size: 9px;
            }
            
            .info span:first-child {
                min-width: 60px;
            }
            
            .table {
                font-size: 9px;
            }
            
            .total-row {
                font-size: 10px;
            }
            
            .btn {
                padding: 9px 12px;
                font-size: 12px;
            }
        }

        /* Landscape orientation */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 5px;
            }
            
            .receipt {
                padding: 10px;
                margin-bottom: 10px;
            }
            
            .header {
                margin-bottom: 8px;
                padding-bottom: 5px;
            }
            
            .logo {
                width: 60px;
            }
            
            .title {
                font-size: 11px;
                margin: 2px 0;
            }
            
            .header p {
                font-size: 9px;
                margin: 1px 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="logo-container">
                <img src="../assets/images/logo_print.png" alt="Logo" class="logo">
            </div>
            <h1 class="title">PharmaLynx POS</h1>
            <p>Quality Healthcare Services<br>Nairobi, Kenya</p>
        </div>

        <div class="info">
            <div><span>Receipt No:</span> <span>#<?php echo str_pad($sale_id, 5, '0', STR_PAD_LEFT); ?></span></div>
            <div><span>Date:</span> <span><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></span></div>
            <div><span>Employee:</span> <span><?php echo $sale['staff_name']; ?></span></div>
            <div><span>Customer:</span> <span><?php echo $sale['customer_id'] ? $sale['first_name'] . " " . $sale['last_name'] : 'Walk-in'; ?></span></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['medicine_name']; ?></td>
                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span>TOTAL (KSh):</span>
                <span><?php echo number_format($sale['total_amount'], 2); ?></span>
            </div>
            <div class="payment-info">
                <span>Payment Method:</span>
                <span><?php echo $sale['payment_method']; ?></span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for shopping with us!<br>Get well soon.</p>
        </div>
    </div>

    <div class="no-print-actions">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
        <a href="reports.php" class="btn btn-back">
            Back to Reports
        </a>
    </div>

</body>
</html>
