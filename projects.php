<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Master Project/Pekerjaan';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $customer_id = $_POST['customer_id'] ?? 0;
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $status = $_POST['status'] ?? 'planning';
            $total_value = $_POST['total_value'] ?? 0;
            
            try {
                $stmt = $conn->prepare("INSERT INTO projects (code, name, customer_id, description, start_date, end_date, status, total_value) 
                                       VALUES (:code, :name, :customer_id, :description, :start_date, :end_date, :status, :total_value)");
                $stmt->execute([
                    'code' => $code,
                    'name' => $name,
                    'customer_id' => $customer_id,
                    'description' => $description,
                    'start_date' => $start_date ?: null,
                    'end_date' => $end_date ?: null,
                    'status' => $status,
                    'total_value' => $total_value
                ]);
                $success = "Project berhasil ditambahkan";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan project: " . $e->getMessage();
            }
        } elseif ($action == 'edit') {
            $id = $_POST['id'] ?? 0;
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $customer_id = $_POST['customer_id'] ?? 0;
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $status = $_POST['status'] ?? 'planning';
            $total_value = $_POST['total_value'] ?? 0;
            
            try {
                $stmt = $conn->prepare("UPDATE projects SET code=:code, name=:name, customer_id=:customer_id, 
                                       description=:description, start_date=:start_date, end_date=:end_date, 
                                       status=:status, total_value=:total_value WHERE id=:id");
                $stmt->execute([
                    'id' => $id,
                    'code' => $code,
                    'name' => $name,
                    'customer_id' => $customer_id,
                    'description' => $description,
                    'start_date' => $start_date ?: null,
                    'end_date' => $end_date ?: null,
                    'status' => $status,
                    'total_value' => $total_value
                ]);
                $success = "Project berhasil diupdate";
            } catch (PDOException $e) {
                $error = "Gagal mengupdate project: " . $e->getMessage();
            }
        } elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $conn->prepare("DELETE FROM projects WHERE id=:id");
                $stmt->execute(['id' => $id]);
                $success = "Project berhasil dihapus";
            } catch (PDOException $e) {
                $error = "Gagal menghapus project: " . $e->getMessage();
            }
        }
    }
}

// Get all projects with customer info
$projects = [];
$customers = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT p.*, c.name as customer_name 
                              FROM projects p 
                              LEFT JOIN customers c ON p.customer_id = c.id 
                              ORDER BY p.created_at DESC");
        $projects = $stmt->fetchAll();
        
        // Get all customers for dropdown
        $stmt = $conn->query("SELECT id, code, name FROM customers ORDER BY name");
        $customers = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data project: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Master Project/Pekerjaan</h3>
    <p>Kelola data project dan pekerjaan Anda</p>
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
        <h5 class="mb-0">Daftar Project</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Tambah Project
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Project</th>
                        <th>Customer</th>
                        <th>Tanggal Mulai</th>
                        <th>Status</th>
                        <th>Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['code']); ?></td>
                        <td><?php echo htmlspecialchars($project['name']); ?></td>
                        <td><?php echo htmlspecialchars($project['customer_name']); ?></td>
                        <td><?php echo $project['start_date'] ? formatDate($project['start_date']) : '-'; ?></td>
                        <td>
                            <?php
                            $badge_class = 'secondary';
                            switch($project['status']) {
                                case 'completed': $badge_class = 'success'; break;
                                case 'ongoing': $badge_class = 'primary'; break;
                                case 'planning': $badge_class = 'info'; break;
                                case 'cancelled': $badge_class = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatRupiah($project['total_value']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars($project['name']); ?>')">
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
                <h5 class="modal-title">Tambah Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Project *</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Project *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer *</label>
                        <select class="form-select" name="customer_id" required>
                            <option value="">Pilih Customer</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo htmlspecialchars($customer['code'] . ' - ' . $customer['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="planning">Planning</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nilai Project</label>
                            <input type="number" class="form-control" name="total_value" min="0" step="0.01" value="0">
                        </div>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Project *</label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Project *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Customer *</label>
                        <select class="form-select" name="customer_id" id="edit_customer_id" required>
                            <option value="">Pilih Customer</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo htmlspecialchars($customer['code'] . ' - ' . $customer['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" id="edit_start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="end_date" id="edit_end_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="planning">Planning</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nilai Project</label>
                            <input type="number" class="form-control" name="total_value" id="edit_total_value" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
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
                    <p>Apakah Anda yakin ingin menghapus project <strong id="delete_name"></strong>?</p>
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
function editProject(project) {
    document.getElementById('edit_id').value = project.id;
    document.getElementById('edit_code').value = project.code;
    document.getElementById('edit_name').value = project.name;
    document.getElementById('edit_customer_id').value = project.customer_id;
    document.getElementById('edit_description').value = project.description || '';
    document.getElementById('edit_start_date').value = project.start_date || '';
    document.getElementById('edit_end_date').value = project.end_date || '';
    document.getElementById('edit_status').value = project.status;
    document.getElementById('edit_total_value').value = project.total_value;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteProject(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
