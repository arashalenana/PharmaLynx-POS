<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Validate Prescription ID

if (!isset($_GET['id'])) {
    die("Prescription ID is required.");
}

$prescription_id = (int)$_GET['id'];

try {
    // Fetch Prescription Details
    $stmt = $conn->prepare("SELECT p.*, c.first_name, c.last_name, c.phone FROM prescriptions p 
                            LEFT JOIN customers c ON p.customer_id = c.id 
                            WHERE p.id = ?");
    $stmt->execute([$prescription_id]);
    $prescription = $stmt->fetch();

    if (!$prescription) {
        die("Prescription not found.");
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription #<?php echo $prescription_id; ?> - PharmaLynx POS</title>
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

        .subtitle {
            font-weight: bold;
            font-size: 12px;
            margin: 5px 0;
            color: #555;
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

        .prescription-section {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin: 10px 0;
            font-size: 11px;
            line-height: 1.6;
        }

        .prescription-label {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .prescription-content {
            white-space: pre-wrap;
            word-wrap: break-word;
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
            
            .subtitle {
                font-size: 11px;
            }
            
            .info {
                font-size: 10px;
                margin-bottom: 10px;
            }
            
            .info span:first-child {
                min-width: 70px;
            }

            .prescription-section {
                font-size: 10px;
                padding: 8px 0;
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

            .prescription-section {
                font-size: 9px;
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

        <div class="subtitle">PRESCRIPTION</div>

        <div class="info">
            <div><span>Rx #:</span> <span>#<?php echo str_pad($prescription_id, 5, '0', STR_PAD_LEFT); ?></span></div>
            <div><span>Date:</span> <span><?php echo $prescription['date']; ?></span></div>
            <div><span>Patient:</span> <span><?php echo $prescription['first_name'] . " " . $prescription['last_name']; ?></span></div>
            <?php if ($prescription['phone']): ?>
            <div><span>Phone:</span> <span><?php echo $prescription['phone']; ?></span></div>
            <?php endif; ?>
        </div>

        <div class="prescription-section">
            <div class="prescription-label">Rx Instructions:</div>
            <div class="prescription-content"><?php echo htmlspecialchars($prescription['notes']); ?></div>
        </div>

        <div class="footer">
            <p>Keep this prescription for your records.<br>Fill within 30 days of issue date.</p>
        </div>
    </div>

    <div class="no-print-actions">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
        <a href="prescriptions.php" class="btn btn-back">
            Back to Prescriptions
        </a>
    </div>
</body>
</html>
