<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Master Barang/Jasa';

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
            $type = $_POST['type'] ?? 'goods';
            $unit = $_POST['unit'] ?? '';
            $price = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            
            try {
                $stmt = $conn->prepare("INSERT INTO items (code, name, type, unit, price, description) 
                                       VALUES (:code, :name, :type, :unit, :price, :description)");
                $stmt->execute([
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'unit' => $unit,
                    'price' => $price,
                    'description' => $description
                ]);
                $success = "Barang/Jasa berhasil ditambahkan";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan barang/jasa: " . $e->getMessage();
            }
        } elseif ($action == 'edit') {
            $id = $_POST['id'] ?? 0;
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $type = $_POST['type'] ?? 'goods';
            $unit = $_POST['unit'] ?? '';
            $price = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            
            try {
                $stmt = $conn->prepare("UPDATE items SET code=:code, name=:name, type=:type, unit=:unit, 
                                       price=:price, description=:description WHERE id=:id");
                $stmt->execute([
                    'id' => $id,
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'unit' => $unit,
                    'price' => $price,
                    'description' => $description
                ]);
                $success = "Barang/Jasa berhasil diupdate";
            } catch (PDOException $e) {
                $error = "Gagal mengupdate barang/jasa: " . $e->getMessage();
            }
        } elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $conn->prepare("DELETE FROM items WHERE id=:id");
                $stmt->execute(['id' => $id]);
                $success = "Barang/Jasa berhasil dihapus";
            } catch (PDOException $e) {
                $error = "Gagal menghapus barang/jasa: " . $e->getMessage();
            }
        }
    }
}

// Get all items
$items = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
        $items = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data barang/jasa: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Master Barang/Jasa</h3>
    <p>Kelola data barang dan jasa Anda</p>
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
        <h5 class="mb-0">Daftar Barang/Jasa</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Tambah Barang/Jasa
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['code']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $item['type'] == 'goods' ? 'primary' : 'success'; ?>">
                                <?php echo $item['type'] == 'goods' ? 'Barang' : 'Jasa'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo formatRupiah($item['price']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
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
                <h5 class="modal-title">Tambah Barang/Jasa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode *</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe *</label>
                            <select class="form-select" name="type" required>
                                <option value="goods">Barang</option>
                                <option value="service">Jasa</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan</label>
                            <input type="text" class="form-control" name="unit" placeholder="unit, pcs, jam, dll">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga *</label>
                        <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
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
                <h5 class="modal-title">Edit Barang/Jasa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode *</label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe *</label>
                            <select class="form-select" name="type" id="edit_type" required>
                                <option value="goods">Barang</option>
                                <option value="service">Jasa</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan</label>
                            <input type="text" class="form-control" name="unit" id="edit_unit">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga *</label>
                        <input type="number" class="form-control" name="price" id="edit_price" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
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
                    <p>Apakah Anda yakin ingin menghapus <strong id="delete_name"></strong>?</p>
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
function editItem(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_code').value = item.code;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_type').value = item.type;
    document.getElementById('edit_unit').value = item.unit || '';
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_description').value = item.description || '';
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteItem(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
