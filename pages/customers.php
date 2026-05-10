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
    if (isset($_POST['add_customer'])) {
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);

        try {
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, phone) VALUES (?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $phone]);
            $message = "<div class='alert alert-success'>Customer added successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif (isset($_POST['update_customer'])) {
        $id = (int)$_POST['id'];
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $phone = sanitize($_POST['phone']);

        try {
            $stmt = $conn->prepare("UPDATE customers SET first_name=?, last_name=?, phone=? WHERE id=?");
            $stmt->execute([$first_name, $last_name, $phone, $id]);
            $message = "<div class='alert alert-success'>Customer updated successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif (isset($_POST['delete_customer'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
            $stmt->execute([$id]);
            $message = "<div class='alert alert-success'>Customer deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch Customers
$customers = [];
try {
    $stmt = $conn->query("SELECT * FROM customers ORDER BY first_name ASC");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Management</h1>
        <button class="btn btn-primary mt-2 mt-sm-0" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-user-plus"></i> Add Customer
        </button>
    </div>

    <?php echo $message; ?>

    <div class="card shadow mb-4">
        <div class="card-body p-0 p-sm-3">
            <div class="mb-3">
                <input type="text" id="customerSearch" class="form-control" placeholder="Search by customer name or phone..." style="max-width: 300px;">
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No customers found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>#C-<?php echo str_pad($customer['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-bold"><?php echo $customer['first_name']; ?></td>
                                <td><?php echo $customer['last_name']; ?></td>
                                <td><?php echo $customer['phone']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <button class="btn btn-outline-info btn-sm edit-btn" 
                                                data-id="<?php echo $customer['id']; ?>"
                                                data-first="<?php echo $customer['first_name']; ?>"
                                                data-last="<?php echo $customer['last_name']; ?>"
                                                data-phone="<?php echo $customer['phone']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                                            <button type="submit" name="delete_customer" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_customer" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" id="edit_first" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="edit_last" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_first').value = this.dataset.first;
            document.getElementById('edit_last').value = this.dataset.last;
            document.getElementById('edit_phone').value = this.dataset.phone;
        });
    });
});

document.getElementById('customerSearch').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table tbody tr');
    
    tableRows.forEach(row => {
        const firstName = row.cells[1].textContent.toLowerCase();
        const lastName = row.cells[2].textContent.toLowerCase();
        const phone = row.cells[3].textContent.toLowerCase();
        
        if (firstName.includes(searchValue) || lastName.includes(searchValue) || phone.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
