<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'sales';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$generated_by = $_SESSION['role']; // Admin or Staff

$date_query = "";
$report_title = "";

switch ($filter) {
    case 'week':
        $date_query = "WHERE DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND DATE(s.created_at) <= CURDATE()";
        $report_title = "Weekly Sales Report";
        break;
    case 'month':
        $date_query = "WHERE DATE(s.created_at) >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND DATE(s.created_at) <= CURDATE()";
        $report_title = "Monthly Sales Report";
        break;
    case 'today':
    default:
        $date_query = "WHERE DATE(s.created_at) = CURDATE()";
        $report_title = "Daily Sales Report";
        break;
}

$data = [];
if ($type == 'sales') {
    try {
        $stmt = $conn->query("SELECT s.*, c.first_name, c.last_name, u.username as staff_name 
                              FROM sales s 
                              LEFT JOIN customers c ON s.customer_id = c.id 
                              JOIN users u ON s.user_id = u.id 
                              $date_query 
                              ORDER BY s.created_at DESC");
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Report Query Error: " . $e->getMessage());
    }
} elseif ($type == 'low_stock') {
    $report_title = "Low Stock Inventory Report";
    try {
        $stmt = $conn->query("SELECT * FROM medicines WHERE quantity < 10 ORDER BY quantity ASC");
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {}
} elseif ($type == 'expiry') {
    $report_title = "Expiring Medicines Report";
    try {
        $stmt = $conn->query("SELECT * FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date ASC");
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $report_title; ?> - PharmaLynx POS</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #000; 
            line-height: 1.6; 
            padding: 40px; 
            background-color: #fff; 
            margin: 0;
        }
        
        .report-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #ddd;
        }

        .header { 
            text-align: center; 
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 2px solid #8b0000;
        }
        
        .logo-container {
            background: #fff;
            padding: 0;
            display: inline-block;
            border-radius: 8px;
            margin-bottom: 0;
        }

        .header img { 
            width: 150px; 
            height: auto; 
            display: block;
        }
        
        .header h1 { 
            color: #8b0000 !important; 
            margin: 0; 
            font-size: 22px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
        }
        
        .header p { 
            margin: 5px 0 0; 
            color: #666; 
            font-style: italic; 
        }
        
        .info { 
            margin-bottom: 30px; 
            font-size: 0.95em; 
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #fff;
        }
        
        .info table { width: 100%; }
        .info td { padding: 5px 0; }
        
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            background: #fff;
        }
        
        table.data th, table.data td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
            color: #000;
        }
        
        table.data th { 
            background-color: #8b0000; 
            color: #fff; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 0.85em;
        }
        
        table.data tbody tr {
            background-color: #fff;
        }
        
        table.data tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        table.data tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 0.85em; 
            color: #666; 
            border-top: 1px solid #ddd; 
            padding-top: 20px; 
        }
        
        .total { 
            text-align: right; 
            font-weight: bold; 
            font-size: 1.2em; 
            margin-top: 30px; 
            color: #8b0000; 
            padding-right: 10px;
        }

        .no-print-actions {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-print { background: #8b0000; color: white; }
        .btn-print:hover { background: #a50000; }
        .btn-back { background: #666; color: white; margin-left: 10px; }
        .btn-back:hover { background: #777; }

        @media print {
            .no-print-actions { display: none; }
            body { 
                padding: 0; 
                background-color: #fff; 
                color: #000; 
                min-height: 100vh;
            }
            .report-container { 
                box-shadow: none; 
                border: none; 
                max-width: 100%; 
                padding: 0;
                background-color: #fff;
            }
            .header { border-bottom: 2px solid #8b0000; }
            .header h1 { color: #8b0000; }
            .header p { color: #333; }
            .info { background: #f9f9f9; color: #000; border: 1px solid #ddd; }
            table.data th { background-color: #fff; color: #8b0000; border: 1px solid #ddd; }
            table.data td { border: 1px solid #ddd; color: #000; }
            .total { color: #8b0000; }
            .footer { color: #333; border-top: 1px solid #ddd; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="no-print-actions">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
        <a href="reports.php" class="btn btn-back">
            Back to Reports
        </a>
    </div>

    <div class="report-container">
        <div class="header">
            <div class="logo-container">
                <img src="../assets/images/logo_print.png" alt="PharmaLynx Logo">
            </div>
            <h1>PharmaLynx POS</h1>
            <p>Quality Healthcare Services - Nairobi, Kenya</p>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td><strong>Report Type:</strong> <?php echo $report_title; ?></td>
                    <td style="text-align: right;"><strong>Date Generated:</strong> <?php echo date('Y-m-d H:i'); ?> (EAT)</td>
                </tr>
                <tr>
                    <td><strong>Generated By:</strong> <?php echo ucfirst($generated_by); ?></td>
                    <td style="text-align: right;"><strong>Period:</strong> <?php echo ucfirst($filter); ?></td>
                </tr>
            </table>
        </div>

        <table class="data">
            <thead>
                <?php if ($type == 'sales'): ?>
                    <tr>
                        <th>Sale ID</th>
                        <th>Customer</th>
                        <th>Staff</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Time</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Stock Level</th>
                        <th><?php echo ($type == 'expiry') ? 'Expiry Date' : 'Status'; ?></th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php 
                $total_sum = 0;
                if (empty($data)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 30px; color: #777;">No records found for this period.</td></tr>
                <?php else: 
                    foreach ($data as $row): 
                        if ($type == 'sales'):
                            $total_sum += $row['total_amount'];
                ?>
                    <tr>
                        <td>#S-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $row['customer_id'] ? $row['first_name'] . " " . $row['last_name'] : 'Walk-in'; ?></td>
                        <td><?php echo $row['staff_name']; ?></td>
                        <td><?php echo $row['payment_method']; ?></td>
                        <td>KSh <?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo ($type == 'expiry') ? $row['expiry_date'] : 'Low Stock'; ?></td>
                    </tr>
                <?php 
                        endif;
                    endforeach; 
                endif; ?>
            </tbody>
        </table>

        <?php if ($type == 'sales'): ?>
            <div class="total">
                Total Revenue: KSh <?php echo number_format($total_sum, 2); ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> PharmaLynx POS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
