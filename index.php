<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config/paths.php';
require_once 'config/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';


// Fetch Real Stats
$today_sales = 0;
$today_transactions = 0;
$low_stock_count = 0;
$expiring_soon_count = 0;

try {
    // Today's Sales
    $stmt = $conn->query("SELECT SUM(total_amount) as total, COUNT(*) as count FROM sales WHERE DATE(created_at) = CURDATE()");
    $res = $stmt->fetch();
    $today_sales = $res['total'] ?? 0;
    $today_transactions = $res['count'] ?? 0;

    // Low Stock Count
    $stmt = $conn->query("SELECT COUNT(*) FROM medicines WHERE quantity < 10");
    $low_stock_count = $stmt->fetchColumn();

    // Expiring Soon Count (within 30 days)
    $stmt = $conn->query("SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $expiring_soon_count = $stmt->fetchColumn();

    // Recent Transactions
    $stmt = $conn->query("SELECT s.*, c.first_name, c.last_name FROM sales s LEFT JOIN customers c ON s.customer_id = c.id ORDER BY s.created_at DESC LIMIT 5");
    $recent_sales = $stmt->fetchAll();

    // Sales Trend (Last 7 Days)
    $stmt = $conn->query("SELECT DATE(created_at) as date, SUM(total_amount) as total FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
    $trend_data = $stmt->fetchAll();
    
    $labels = [];
    $values = [];
    foreach ($trend_data as $row) {
        $labels[] = date('D', strtotime($row['date']));
        $values[] = (float)$row['total'];
    }
} catch (PDOException $e) {}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="pages/sales.php" class="btn btn-sm btn-danger shadow-sm mt-2 mt-sm-0">
            <i class="fas fa-plus fa-sm text-white-50"></i> New Sale
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Sales Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-start border-danger border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Sales (Today)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatKSh($today_sales); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-start border-primary border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_transactions; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-start border-warning border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_count; ?> Items</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-start border-info border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Expiring Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $expiring_soon_count; ?> Items</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Trend (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_sales)): ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">No recent sales.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td>#<?php echo $sale['id']; ?></td>
                                        <td><?php echo $sale['customer_id'] ? $sale['first_name'] . " " . $sale['last_name'] : 'Walk-in'; ?></td>
                                        <td class="fw-bold"><?php echo formatKSh($sale['total_amount']); ?></td>
                                        <td>
                                            <a href="pages/generate_receipt.php?id=<?php echo $sale['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        <a href="pages/reports.php" class="btn btn-sm btn-outline-primary w-100">View All Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Sales (KSh)',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: 'rgba(139, 0, 0, 0.1)',
                    borderColor: '#8b0000',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: KSh ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
