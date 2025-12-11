<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Master Customer';

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
            $address = $_POST['address'] ?? '';
            $city = $_POST['city'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $contact_person = $_POST['contact_person'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            try {
                $stmt = $conn->prepare("INSERT INTO customers (code, name, address, city, phone, email, contact_person, notes) 
                                       VALUES (:code, :name, :address, :city, :phone, :email, :contact_person, :notes)");
                $stmt->execute([
                    'code' => $code,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'phone' => $phone,
                    'email' => $email,
                    'contact_person' => $contact_person,
                    'notes' => $notes
                ]);
                $success = "Customer berhasil ditambahkan";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan customer: " . $e->getMessage();
            }
        } elseif ($action == 'edit') {
            $id = $_POST['id'] ?? 0;
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $city = $_POST['city'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $contact_person = $_POST['contact_person'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            try {
                $stmt = $conn->prepare("UPDATE customers SET code=:code, name=:name, address=:address, city=:city, 
                                       phone=:phone, email=:email, contact_person=:contact_person, notes=:notes 
                                       WHERE id=:id");
                $stmt->execute([
                    'id' => $id,
                    'code' => $code,
                    'name' => $name,
                    'address' => $address,
                    'city' => $city,
                    'phone' => $phone,
                    'email' => $email,
                    'contact_person' => $contact_person,
                    'notes' => $notes
                ]);
                $success = "Customer berhasil diupdate";
            } catch (PDOException $e) {
                $error = "Gagal mengupdate customer: " . $e->getMessage();
            }
        } elseif ($action == 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $conn->prepare("DELETE FROM customers WHERE id=:id");
                $stmt->execute(['id' => $id]);
                $success = "Customer berhasil dihapus";
            } catch (PDOException $e) {
                $error = "Gagal menghapus customer: " . $e->getMessage();
            }
        }
    }
}

// Get all customers
$customers = [];
if ($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
        $customers = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data customer: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Master Customer</h3>
    <p>Kelola data customer Anda</p>
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
        <h5 class="mb-0">Daftar Customer</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Tambah Customer
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kota</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Contact Person</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['code']); ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['city']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['contact_person']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
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
                <h5 class="modal-title">Tambah Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Customer *</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Customer *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
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
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Customer *</label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Customer *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" class="form-control" name="city" id="edit_city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" class="form-control" name="phone" id="edit_phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" id="edit_contact_person">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="2"></textarea>
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
                    <p>Apakah Anda yakin ingin menghapus customer <strong id="delete_name"></strong>?</p>
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
function editCustomer(customer) {
    document.getElementById('edit_id').value = customer.id;
    document.getElementById('edit_code').value = customer.code;
    document.getElementById('edit_name').value = customer.name;
    document.getElementById('edit_address').value = customer.address || '';
    document.getElementById('edit_city').value = customer.city || '';
    document.getElementById('edit_phone').value = customer.phone || '';
    document.getElementById('edit_email').value = customer.email || '';
    document.getElementById('edit_contact_person').value = customer.contact_person || '';
    document.getElementById('edit_notes').value = customer.notes || '';
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteCustomer(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
