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


$message = "";

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_prescription'])) {
        $customer_id = (int)$_POST['customer_id'];
        $notes = sanitize($_POST['notes']);
        $date = $_POST['date'];

        try {
            $stmt = $conn->prepare("INSERT INTO prescriptions (customer_id, notes, date) VALUES (?, ?, ?)");
            $stmt->execute([$customer_id, $notes, $date]);
            $message = "<div class='alert alert-success'>Prescription added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif (isset($_POST['delete_prescription'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = $conn->prepare("DELETE FROM prescriptions WHERE id=?");
            $stmt->execute([$id]);
            $message = "<div class='alert alert-success'>Prescription deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch Prescriptions with Customer Names
$prescriptions = [];
try {
    $stmt = $conn->query("SELECT p.*, c.first_name, c.last_name FROM prescriptions p JOIN customers c ON p.customer_id = c.id ORDER BY p.date DESC");
    $prescriptions = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch Customers for the dropdown
$customers = [];
try {
    $stmt = $conn->query("SELECT id, first_name, last_name FROM customers ORDER BY first_name ASC");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Prescriptions</h1>
        <button class="btn btn-primary mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#addPrescriptionModal">
            <i class="fas fa-plus"></i> New Prescription
        </button>
    </div>

    <?php echo $message; ?>

    <div class="card shadow mb-4">
        <div class="card-body p-0 p-sm-3">
            <div class="mb-3">
                <input type="text" id="prescriptionSearch" class="form-control" placeholder="Search by customer name..." style="max-width: 300px;">
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Notes</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prescriptions)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No prescriptions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($prescriptions as $p): ?>
                            <tr>
                                <td>#PR-<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="fw-bold text-wrap" style="min-width: 120px;"><?php echo $p['first_name'] . " " . $p['last_name']; ?></div>
                                </td>
                                <td>
                                    <div class="text-wrap" style="min-width: 200px; max-width: 400px;">
                                        <?php echo nl2br($p['notes']); ?>
                                    </div>
                                </td>
                                <td><?php echo $p['date']; ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="generate_prescription_receipt.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-info" title="Generate Receipt">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" name="delete_prescription" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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

<!-- Add Prescription Modal -->
<div class="modal fade" id="addPrescriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Prescription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Choose Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo $c['first_name'] . " " . $c['last_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prescription Notes</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Dosage instructions..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_prescription" class="btn btn-primary">Save Prescription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('prescriptionSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('.table tbody tr');
        
        tableRows.forEach(row => {
            const customerName = row.cells[1].textContent.toLowerCase();
            if (customerName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
