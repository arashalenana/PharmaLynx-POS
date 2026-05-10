<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/paths.php';
require_once '../config/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Set Timezone to EAT
date_default_timezone_set('Africa/Nairobi');

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$date_query = "";

switch ($filter) {
    case 'week':
        // Start of the week (Monday)
        $date_query = "AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        $filter_text = "This Week";
        break;
    case 'month':
        // Start of the month
        $date_query = "AND s.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        $filter_text = "This Month";
        break;
    case 'today':
    default:
        $date_query = "AND DATE(s.created_at) = CURDATE()";
        $filter_text = "Today";
        break;
}

// Fetch Sales History
$sales = [];
try {
    $stmt = $conn->query("SELECT s.*, c.first_name, c.last_name, u.username as staff_name 
                          FROM sales s 
                          LEFT JOIN customers c ON s.customer_id = c.id 
                          JOIN users u ON s.user_id = u.id 
                          WHERE 1=1 $date_query 
                          ORDER BY s.created_at DESC");
    $sales = $stmt->fetchAll();
} catch (PDOException $e) {}

// Calculate Totals
$total_revenue = 0;
foreach ($sales as $sale) {
    $total_revenue += $sale['total_amount'];
}

// Fetch Low Stock for the report section
$low_stock_count = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM medicines WHERE quantity < 10");
    $low_stock_count = $stmt->fetchColumn();
} catch (PDOException $e) {}

// Fetch Expiring Soon Count
$expiring_soon_count = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $expiring_soon_count = $stmt->fetchColumn();
} catch (PDOException $e) {}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
        <div class="btn-group shadow-sm">
            <a href="?filter=today" class="btn btn-sm <?php echo $filter == 'today' ? 'btn-danger' : 'btn-outline-danger'; ?>">Today</a>
            <a href="?filter=week" class="btn btn-sm <?php echo $filter == 'week' ? 'btn-danger' : 'btn-outline-danger'; ?>">This Week</a>
            <a href="?filter=month" class="btn btn-sm <?php echo $filter == 'month' ? 'btn-danger' : 'btn-outline-danger'; ?>">This Month</a>
        </div>
    </div>

    <div class="row">
        <!-- Report Cards -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-start border-danger border-4 h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold text-danger text-uppercase mb-1"><?php echo $filter_text; ?> Sales</h6>
                    <div class="h5 mb-3 font-weight-bold text-gray-800"><?php echo formatKSh($total_revenue); ?></div>
                    <a href="generate_report.php?type=sales&filter=<?php echo $filter; ?>" target="_blank" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-start border-warning border-4 h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold text-warning text-uppercase mb-1">Low Stock</h6>
                    <div class="h5 mb-3 font-weight-bold text-gray-800"><?php echo $low_stock_count; ?> Items Alerted</div>
                    <a href="generate_report.php?type=low_stock" target="_blank" class="btn btn-sm btn-warning">
                        <i class="fas fa-file-pdf me-1"></i> Generate PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-start border-info border-4 h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold text-info text-uppercase mb-1">Expiry Report</h6>
                    <div class="h5 mb-3 font-weight-bold text-gray-800"><?php echo $expiring_soon_count; ?> Items Expiring</div>
                    <a href="generate_report.php?type=expiry" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-file-pdf me-1"></i> Generate PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
            <h6 class="m-0 font-weight-bold text-primary mb-2 mb-sm-0">Sales History - <?php echo $filter_text; ?></h6>
        </div>
        <div class="card-body p-0 p-sm-3">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Staff</th>
                            <th>Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No sales found for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td>#S-<?php echo str_pad($sale['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $sale['customer_id'] ? $sale['first_name'] . " " . $sale['last_name'] : '<span class="text-muted">Walk-in</span>'; ?></td>
                                <td class="fw-bold"><?php echo formatKSh($sale['total_amount']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $sale['payment_method']; ?></span></td>
                                <td><?php echo $sale['staff_name']; ?></td>
                                <td><?php echo date('H:i', strtotime($sale['created_at'])); ?></td>
                                <td>
                                    <a href="generate_receipt.php?id=<?php echo $sale['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-print"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
