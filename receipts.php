<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Kwitansi';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $receipt_number = $_POST['receipt_number'] ?? '';
            $customer_id = $_POST['customer_id'] ?? 0;
            $project_id = $_POST['project_id'] ?? null;
            $invoice_id = $_POST['invoice_id'] ?? null;
            $receipt_date = $_POST['receipt_date'] ?? date('Y-m-d');
            $amount = $_POST['amount'] ?? 0;
            $payment_method = $_POST['payment_method'] ?? '';
            $description = $_POST['description'] ?? '';
            $received_by = $_POST['received_by'] ?? $_SESSION['full_name'];
            
            try {
                $stmt = $conn->prepare("INSERT INTO receipts (receipt_number, customer_id, project_id, invoice_id, receipt_date, amount, payment_method, description, received_by, created_by) 
                                       VALUES (:receipt_number, :customer_id, :project_id, :invoice_id, :receipt_date, :amount, :payment_method, :description, :received_by, :created_by)");
                $stmt->execute([
                    'receipt_number' => $receipt_number,
                    'customer_id' => $customer_id,
                    'project_id' => $project_id ?: null,
                    'invoice_id' => $invoice_id ?: null,
                    'receipt_date' => $receipt_date,
                    'amount' => $amount,
                    'payment_method' => $payment_method,
                    'description' => $description,
                    'received_by' => $received_by,
                    'created_by' => $_SESSION['user_id']
                ]);
                $success = "Kwitansi berhasil ditambahkan";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan kwitansi: " . $e->getMessage();
            }
        } elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $conn->prepare("DELETE FROM receipts WHERE id=:id");
                $stmt->execute(['id' => $id]);
                $success = "Kwitansi berhasil dihapus";
            } catch (PDOException $e) {
                $error = "Gagal menghapus kwitansi: " . $e->getMessage();
            }
        }
    }
}

// Get all receipts
$receipts = [];
$customers = [];
$projects = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT r.*, c.name as customer_name, p.name as project_name 
                              FROM receipts r 
                              LEFT JOIN customers c ON r.customer_id = c.id 
                              LEFT JOIN projects p ON r.project_id = p.id 
                              ORDER BY r.created_at DESC");
        $receipts = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, code, name FROM customers ORDER BY name");
        $customers = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, code, name FROM projects ORDER BY name");
        $projects = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Kwitansi</h3>
    <p>Kelola kwitansi pembayaran</p>
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
        <h5 class="mb-0">Daftar Kwitansi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Buat Kwitansi
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. Kwitansi</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $receipt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($receipt['receipt_number']); ?></td>
                        <td><?php echo htmlspecialchars($receipt['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($receipt['project_name'] ?? '-'); ?></td>
                        <td><?php echo formatDate($receipt['receipt_date']); ?></td>
                        <td><?php echo formatRupiah($receipt['amount']); ?></td>
                        <td><?php echo htmlspecialchars($receipt['payment_method']); ?></td>
                        <td>
                            <a href="receipt_pdf.php?id=<?php echo $receipt['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteReceipt(<?php echo $receipt['id']; ?>, '<?php echo htmlspecialchars($receipt['receipt_number']); ?>')">
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Kwitansi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Kwitansi *</label>
                            <input type="text" class="form-control" name="receipt_number" value="KWT/<?php echo date('Ymd'); ?>/<?php echo str_pad(count($receipts) + 1, 4, '0', STR_PAD_LEFT); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" name="receipt_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer *</label>
                        <select class="form-select" name="customer_id" required>
                            <option value="">Pilih Customer</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project (Opsional)</label>
                        <select class="form-select" name="project_id">
                            <option value="">-</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah (Rp) *</label>
                            <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Metode Pembayaran *</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Cek">Cek</option>
                                <option value="Giro">Giro</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diterima Oleh</label>
                        <input type="text" class="form-control" name="received_by" value="<?php echo $_SESSION['full_name']; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
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
                    <p>Apakah Anda yakin ingin menghapus kwitansi <strong id="delete_name"></strong>?</p>
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
function deleteReceipt(id, number) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = number;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
