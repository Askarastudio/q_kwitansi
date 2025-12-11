<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Faktur Barang';

$db = new Database();
$conn = $db->getConnection();

// Similar structure to receipts - list, create, delete functionality
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM delivery_notes WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $success = "Faktur barang berhasil dihapus";
    } catch (PDOException $e) {
        $error = "Gagal menghapus faktur barang: " . $e->getMessage();
    }
}

$delivery_notes = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT dn.*, c.name as customer_name, p.name as project_name 
                              FROM delivery_notes dn 
                              LEFT JOIN customers c ON dn.customer_id = c.id 
                              LEFT JOIN projects p ON dn.project_id = p.id 
                              ORDER BY dn.created_at DESC");
        $delivery_notes = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Faktur Barang / Surat Jalan</h3>
    <p>Kelola faktur pengiriman barang</p>
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
        <h5 class="mb-0">Daftar Faktur Barang</h5>
        <a href="delivery_note_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Faktur Barang
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th>Tanggal</th>
                        <th>Dikirim Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($delivery_notes as $dn): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dn['delivery_number']); ?></td>
                        <td><?php echo htmlspecialchars($dn['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($dn['project_name'] ?? '-'); ?></td>
                        <td><?php echo formatDate($dn['delivery_date']); ?></td>
                        <td><?php echo htmlspecialchars($dn['delivered_by']); ?></td>
                        <td>
                            <a href="delivery_note_pdf.php?id=<?php echo $dn['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteDeliveryNote(<?php echo $dn['id']; ?>, '<?php echo htmlspecialchars($dn['delivery_number']); ?>')">
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
                    <p>Apakah Anda yakin ingin menghapus faktur <strong id="delete_name"></strong>?</p>
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
function deleteDeliveryNote(id, number) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = number;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
