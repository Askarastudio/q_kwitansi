<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Form Faktur Barang';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';
$delivery_note = null;
$items = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_number = $_POST['delivery_number'] ?? '';
    $customer_id = $_POST['customer_id'] ?? 0;
    $project_id = $_POST['project_id'] ?? null;
    $delivery_date = $_POST['delivery_date'] ?? date('Y-m-d');
    $notes = $_POST['notes'] ?? '';
    $delivered_by = $_POST['delivered_by'] ?? '';
    $received_by = $_POST['received_by'] ?? '';
    
    $item_ids = $_POST['item_id'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    
    try {
        $conn->beginTransaction();
        
        // Insert delivery note
        $stmt = $conn->prepare("INSERT INTO delivery_notes (delivery_number, customer_id, project_id, delivery_date, notes, delivered_by, received_by, created_by) 
                               VALUES (:delivery_number, :customer_id, :project_id, :delivery_date, :notes, :delivered_by, :received_by, :created_by)");
        $stmt->execute([
            'delivery_number' => $delivery_number,
            'customer_id' => $customer_id,
            'project_id' => $project_id ?: null,
            'delivery_date' => $delivery_date,
            'notes' => $notes,
            'delivered_by' => $delivered_by,
            'received_by' => $received_by,
            'created_by' => $_SESSION['user_id']
        ]);
        
        $delivery_note_id = $conn->lastInsertId();
        
        // Insert items
        $stmt = $conn->prepare("INSERT INTO delivery_note_items (delivery_note_id, item_id, description, quantity, unit) 
                               VALUES (:delivery_note_id, :item_id, :description, :quantity, :unit)");
        
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $stmt->execute([
                    'delivery_note_id' => $delivery_note_id,
                    'item_id' => $item_ids[$i] ?: null,
                    'description' => $descriptions[$i],
                    'quantity' => $quantities[$i],
                    'unit' => $units[$i]
                ]);
            }
        }
        
        $conn->commit();
        $success = "Faktur barang berhasil dibuat";
        
        // Redirect to view
        header("Location: delivery_note_pdf.php?id=" . $delivery_note_id);
        exit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Gagal membuat faktur barang: " . $e->getMessage();
    }
}

// Get customers and items
$customers = [];
$projects = [];
$available_items = [];

if ($conn) {
    try {
        $stmt = $conn->query("SELECT id, code, name FROM customers ORDER BY name");
        $customers = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, code, name FROM projects ORDER BY name");
        $projects = $stmt->fetchAll();
        
        $stmt = $conn->query("SELECT id, code, name, unit FROM items WHERE type = 'goods' ORDER BY name");
        $available_items = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Buat Faktur Barang / Surat Jalan</h3>
    <p>Buat dokumen pengiriman barang baru</p>
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
    <div class="card-header">
        <h5 class="mb-0">Form Faktur Barang</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Faktur *</label>
                    <input type="text" class="form-control" name="delivery_number" value="DN/<?php echo date('Ymd'); ?>/001" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" class="form-control" name="delivery_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer *</label>
                    <select class="form-select" name="customer_id" required>
                        <option value="">Pilih Customer</option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Project (Opsional)</label>
                    <select class="form-select" name="project_id">
                        <option value="">-</option>
                        <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dikirim Oleh</label>
                    <input type="text" class="form-control" name="delivered_by" value="<?php echo $_SESSION['full_name']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Diterima Oleh</label>
                    <input type="text" class="form-control" name="received_by">
                </div>
            </div>
            
            <hr class="my-4">
            
            <h5>Item Barang</h5>
            <div id="items-container">
                <div class="row item-row mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Pilih Barang</label>
                        <select class="form-select item-select" name="item_id[]" onchange="fillItemData(this)">
                            <option value="">Manual Input</option>
                            <?php foreach ($available_items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                    data-unit="<?php echo htmlspecialchars($item['unit']); ?>">
                                <?php echo htmlspecialchars($item['code'] . ' - ' . $item['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deskripsi *</label>
                        <input type="text" class="form-control item-description" name="description[]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty *</label>
                        <input type="number" class="form-control" name="quantity[]" min="0" step="0.01" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control item-unit" name="unit[]" value="unit">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger w-100" onclick="removeItem(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-secondary" onclick="addItem()">
                <i class="bi bi-plus-circle"></i> Tambah Item
            </button>
            
            <hr class="my-4">
            
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea class="form-control" name="notes" rows="3"></textarea>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan & Lihat PDF
                </button>
                <a href="delivery_notes.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();

$extra_js = "
<script>
function addItem() {
    const container = document.getElementById('items-container');
    const row = container.querySelector('.item-row').cloneNode(true);
    row.querySelectorAll('input').forEach(input => input.value = input.type === 'number' ? '1' : (input.name === 'unit[]' ? 'unit' : ''));
    row.querySelector('select').selectedIndex = 0;
    container.appendChild(row);
}

function removeItem(btn) {
    const container = document.getElementById('items-container');
    if (container.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
    } else {
        alert('Minimal harus ada 1 item');
    }
}

function fillItemData(select) {
    const option = select.options[select.selectedIndex];
    const row = select.closest('.item-row');
    
    if (option.value) {
        row.querySelector('.item-description').value = option.dataset.name || '';
        row.querySelector('.item-unit').value = option.dataset.unit || 'unit';
    }
}
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
