<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Form Invoice';

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice_number = $_POST['invoice_number'] ?? '';
    $customer_id = $_POST['customer_id'] ?? 0;
    $project_id = $_POST['project_id'] ?? null;
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $due_date = $_POST['due_date'] ?? null;
    $tax = $_POST['tax'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    
    $item_ids = $_POST['item_id'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $prices = $_POST['price'] ?? [];
    
    try {
        $conn->beginTransaction();
        
        // Calculate totals
        $subtotal = 0;
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $subtotal += $quantities[$i] * $prices[$i];
            }
        }
        
        $total = $subtotal + $tax - $discount;
        
        // Insert invoice
        $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, customer_id, project_id, invoice_date, due_date, subtotal, tax, discount, total, notes, status, created_by) 
                               VALUES (:invoice_number, :customer_id, :project_id, :invoice_date, :due_date, :subtotal, :tax, :discount, :total, :notes, :status, :created_by)");
        $stmt->execute([
            'invoice_number' => $invoice_number,
            'customer_id' => $customer_id,
            'project_id' => $project_id ?: null,
            'invoice_date' => $invoice_date,
            'due_date' => $due_date ?: null,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'notes' => $notes,
            'status' => $status,
            'created_by' => $_SESSION['user_id']
        ]);
        
        $invoice_id = $conn->lastInsertId();
        
        // Insert items
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_id, description, quantity, unit, price, total) 
                               VALUES (:invoice_id, :item_id, :description, :quantity, :unit, :price, :total)");
        
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $item_total = $quantities[$i] * $prices[$i];
                $stmt->execute([
                    'invoice_id' => $invoice_id,
                    'item_id' => $item_ids[$i] ?: null,
                    'description' => $descriptions[$i],
                    'quantity' => $quantities[$i],
                    'unit' => $units[$i],
                    'price' => $prices[$i],
                    'total' => $item_total
                ]);
            }
        }
        
        $conn->commit();
        $success = "Invoice berhasil dibuat";
        
        // Redirect to PDF
        header("Location: invoice_pdf.php?id=" . $invoice_id);
        exit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Gagal membuat invoice: " . $e->getMessage();
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
        
        $stmt = $conn->query("SELECT id, code, name, unit, price FROM items ORDER BY name");
        $available_items = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Gagal mengambil data: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="page-heading">
    <h3>Buat Invoice</h3>
    <p>Buat invoice baru untuk customer</p>
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
        <h5 class="mb-0">Form Invoice</h5>
    </div>
    <div class="card-body">
        <form method="POST" id="invoiceForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Invoice *</label>
                    <input type="text" class="form-control" name="invoice_number" value="INV/<?php echo date('Ymd'); ?>/001" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" class="form-control" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" class="form-control" name="due_date">
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
            
            <hr class="my-4">
            
            <h5>Item</h5>
            <div id="items-container">
                <div class="row item-row mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Pilih Item</label>
                        <select class="form-select item-select" name="item_id[]" onchange="fillItemData(this)">
                            <option value="">Manual Input</option>
                            <?php foreach ($available_items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                    data-unit="<?php echo htmlspecialchars($item['unit']); ?>"
                                    data-price="<?php echo $item['price']; ?>">
                                <?php echo htmlspecialchars($item['code'] . ' - ' . $item['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deskripsi *</label>
                        <input type="text" class="form-control item-description" name="description[]" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty *</label>
                        <input type="number" class="form-control item-qty" name="quantity[]" min="0" step="0.01" value="1" required onchange="calculateTotal()">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control item-unit" name="unit[]" value="unit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Harga *</label>
                        <input type="number" class="form-control item-price" name="price[]" min="0" step="0.01" required onchange="calculateTotal()">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control item-total" readonly>
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
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control" id="subtotalDisplay" readonly>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Pajak</label>
                                <input type="number" class="form-control" name="tax" id="tax" min="0" step="0.01" value="0" onchange="calculateTotal()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Diskon</label>
                                <input type="number" class="form-control" name="discount" id="discount" min="0" step="0.01" value="0" onchange="calculateTotal()">
                            </div>
                            <hr>
                            <div class="mb-0">
                                <label class="form-label"><strong>TOTAL</strong></label>
                                <input type="text" class="form-control fw-bold" id="totalDisplay" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan & Lihat PDF
                </button>
                <a href="invoices.php" class="btn btn-secondary">
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
    row.querySelectorAll('input').forEach(input => {
        if (input.type === 'number') {
            input.value = input.name === 'quantity[]' ? '1' : '0';
        } else if (!input.readOnly) {
            input.value = input.name === 'unit[]' ? 'unit' : '';
        }
    });
    row.querySelector('select').selectedIndex = 0;
    container.appendChild(row);
}

function removeItem(btn) {
    const container = document.getElementById('items-container');
    if (container.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
        calculateTotal();
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
        row.querySelector('.item-price').value = option.dataset.price || '0';
        calculateTotal();
    }
}

function calculateTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const itemTotal = qty * price;
        
        row.querySelector('.item-total').value = formatRupiah(itemTotal);
        subtotal += itemTotal;
    });
    
    const tax = parseFloat(document.getElementById('tax').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const total = subtotal + tax - discount;
    
    document.getElementById('subtotalDisplay').value = formatRupiah(subtotal);
    document.getElementById('totalDisplay').value = formatRupiah(total);
}

function formatRupiah(amount) {
    return 'Rp ' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '\$&,');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>
";

include __DIR__ . '/app/views/layouts/main.php';
?>
