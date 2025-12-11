<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Surat';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $letter_number = $_POST['letter_number'] ?? '';
            $customer_id = $_POST['customer_id'] ?? null;
            $project_id = $_POST['project_id'] ?? null;
            $letter_date = $_POST['letter_date'] ?? date('Y-m-d');
            $subject = $_POST['subject'] ?? '';
            $content = $_POST['content'] ?? '';
            $type = $_POST['type'] ?? '';
            $recipient = $_POST['recipient'] ?? '';
            
            try {
                $stmt = $conn->prepare("INSERT INTO letters (letter_number, customer_id, project_id, letter_date, subject, content, type, recipient, created_by) 
                                       VALUES (:letter_number, :customer_id, :project_id, :letter_date, :subject, :content, :type, :recipient, :created_by)");
                $stmt->execute([
                    'letter_number' => $letter_number,
                    'customer_id' => $customer_id ?: null,
                    'project_id' => $project_id ?: null,
                    'letter_date' => $letter_date,
                    'subject' => $subject,
                    'content' => $content,
                    'type' => $type,
                    'recipient' => $recipient,
                    'created_by' => $_SESSION['user_id']
                ]);
                $success = "Surat berhasil ditambahkan";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan surat: " . $e->getMessage();
            }
        } elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $conn->prepare("DELETE FROM letters WHERE id=:id");
                $stmt->execute(['id' => $id]);
                $success = "Surat berhasil dihapus";
            } catch (PDOException $e) {
                $error = "Gagal menghapus surat: " . $e->getMessage();
            }
        }
    }
}

$letters = [];
$customers = [];
$projects = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT l.*, c.name as customer_name, p.name as project_name 
                              FROM letters l 
                              LEFT JOIN customers c ON l.customer_id = c.id 
                              LEFT JOIN projects p ON l.project_id = p.id 
                              ORDER BY l.created_at DESC");
        $letters = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, name FROM customers ORDER BY name");
        $customers = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, name FROM projects ORDER BY name");
        $projects = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Surat</h3>
    <p>Kelola surat-surat Anda</p>
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
        <h5 class="mb-0">Daftar Surat</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Buat Surat
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>No. Surat</th>
                        <th>Tanggal</th>
                        <th>Perihal</th>
                        <th>Tipe</th>
                        <th>Penerima</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($letters as $letter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($letter['letter_number']); ?></td>
                        <td><?php echo formatDate($letter['letter_date']); ?></td>
                        <td><?php echo htmlspecialchars($letter['subject']); ?></td>
                        <td><?php echo htmlspecialchars($letter['type']); ?></td>
                        <td><?php echo htmlspecialchars($letter['recipient']); ?></td>
                        <td>
                            <a href="letter_pdf.php?id=<?php echo $letter['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteLetter(<?php echo $letter['id']; ?>, '<?php echo htmlspecialchars($letter['letter_number']); ?>')">
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
                <h5 class="modal-title">Buat Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Surat *</label>
                            <input type="text" class="form-control" name="letter_number" value="<?php echo date('Y') . '/SRT/' . str_pad(count($letters) + 1, 4, '0', STR_PAD_LEFT); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" name="letter_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe Surat *</label>
                            <select class="form-select" name="type" required>
                                <option value="Surat Penawaran">Surat Penawaran</option>
                                <option value="Surat Pesanan">Surat Pesanan</option>
                                <option value="Surat Perjanjian">Surat Perjanjian</option>
                                <option value="Surat Jalan">Surat Jalan</option>
                                <option value="Surat Resmi">Surat Resmi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penerima *</label>
                            <input type="text" class="form-control" name="recipient" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer (Opsional)</label>
                        <select class="form-select" name="customer_id">
                            <option value="">-</option>
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
                    <div class="mb-3">
                        <label class="form-label">Perihal *</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Surat *</label>
                        <textarea class="form-control" name="content" rows="6" required></textarea>
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
                    <p>Apakah Anda yakin ingin menghapus surat <strong id="delete_name"></strong>?</p>
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
function deleteLetter(id, number) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = number;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
