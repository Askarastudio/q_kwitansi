<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Detail Invoice';

$id = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

// Get invoice data
$stmt = $conn->prepare("SELECT i.*, c.name as customer_name, c.address as customer_address, 
                        p.name as project_name
                        FROM invoices i
                        LEFT JOIN customers c ON i.customer_id = c.id
                        LEFT JOIN projects p ON i.project_id = p.id
                        WHERE i.id = :id");
$stmt->execute(['id' => $id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    header("Location: invoices.php");
    exit();
}

// Get items
$stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
$stmt->execute(['id' => $id]);
$items = $stmt->fetchAll();

ob_start();
?>

<div class="page-heading">
    <h3>Detail Invoice</h3>
    <p>Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Invoice Information</h5>
        <div>
            <a href="invoice_pdf.php?id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
                <i class="bi bi-file-pdf"></i> Export PDF
            </a>
            <a href="invoices.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Customer</h6>
                <p><strong><?php echo htmlspecialchars($invoice['customer_name']); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?></p>
                
                <?php if ($invoice['project_name']): ?>
                <h6 class="mt-3">Project</h6>
                <p><?php echo htmlspecialchars($invoice['project_name']); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-end">
                <h6>Invoice Details</h6>
                <p>
                    <strong>No. Invoice:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?><br>
                    <strong>Tanggal:</strong> <?php echo formatDate($invoice['invoice_date']); ?><br>
                    <?php if ($invoice['due_date']): ?>
                    <strong>Jatuh Tempo:</strong> <?php echo formatDate($invoice['due_date']); ?><br>
                    <?php endif; ?>
                    <strong>Status:</strong> 
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
                </p>
            </div>
        </div>
        
        <hr class="my-4">
        
        <h6>Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo number_format($item['quantity'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo formatRupiah($item['price']); ?></td>
                        <td><?php echo formatRupiah($item['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <?php if ($invoice['notes']): ?>
                <h6>Catatan</h6>
                <p><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <table class="table">
                    <tr>
                        <td><strong>Subtotal</strong></td>
                        <td class="text-end"><?php echo formatRupiah($invoice['subtotal']); ?></td>
                    </tr>
                    <?php if ($invoice['tax'] > 0): ?>
                    <tr>
                        <td>Pajak</td>
                        <td class="text-end"><?php echo formatRupiah($invoice['tax']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($invoice['discount'] > 0): ?>
                    <tr>
                        <td>Diskon</td>
                        <td class="text-end">-<?php echo formatRupiah($invoice['discount']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="background: #f8f9fa;">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-end"><strong style="font-size: 18px; color: #667eea;"><?php echo formatRupiah($invoice['total']); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
