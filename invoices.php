<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Invoice';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $success = "Invoice berhasil dihapus";
    } catch (PDOException $e) {
        $error = "Gagal menghapus invoice: " . $e->getMessage();
    }
}

// Get all invoices
$invoices = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT i.*, c.name as customer_name, p.name as project_name, u.full_name as creator_name
                              FROM invoices i 
                              LEFT JOIN customers c ON i.customer_id = c.id 
                              LEFT JOIN projects p ON i.project_id = p.id
                              LEFT JOIN users u ON i.created_by = u.id
                              ORDER BY i.created_at DESC");
        $invoices = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data invoice: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Invoice</h3>
    <p>Kelola invoice Anda</p>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle"></i> <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Invoice</h5>
        <a href="invoice_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Invoice Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['project_name'] ?? '-'); ?></td>
                        <td><?php echo formatDate($invoice['invoice_date']); ?></td>
                        <td><?php echo formatRupiah($invoice['total']); ?></td>
                        <td>
                            <?php
                            $badge_class = 'secondary';
                            switch($invoice['status']) {
                                case 'paid': $badge_class = 'success'; break;
                                case 'sent': $badge_class = 'info'; break;
                                case 'cancelled': $badge_class = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($invoice['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="invoice_view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-info" title="Lihat">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="invoice_form.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="invoice_pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-success" title="PDF" target="_blank">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteInvoice(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['invoice_number']); ?>')" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus invoice <strong id="delete_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extra_js = "
<script>
function deleteInvoice(id, number) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = number;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
