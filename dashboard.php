<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$page_title = 'Dashboard';

// Get statistics
$db = new Database();
$conn = $db->getConnection();

$stats = [
    'customers' => 0,
    'items' => 0,
    'projects' => 0,
    'invoices' => 0,
    'receipts' => 0,
    'delivery_notes' => 0,
    'letters' => 0,
    'total_value' => 0
];

if ($conn) {
    try {
        // Count customers
        $stmt = $conn->query("SELECT COUNT(*) as count FROM customers");
        $stats['customers'] = $stmt->fetch()['count'];
        
        // Count items
        $stmt = $conn->query("SELECT COUNT(*) as count FROM items");
        $stats['items'] = $stmt->fetch()['count'];
        
        // Count projects
        $stmt = $conn->query("SELECT COUNT(*) as count FROM projects");
        $stats['projects'] = $stmt->fetch()['count'];
        
        // Count invoices
        $stmt = $conn->query("SELECT COUNT(*) as count FROM invoices");
        $stats['invoices'] = $stmt->fetch()['count'];
        
        // Count receipts
        $stmt = $conn->query("SELECT COUNT(*) as count FROM receipts");
        $stats['receipts'] = $stmt->fetch()['count'];
        
        // Count delivery notes
        $stmt = $conn->query("SELECT COUNT(*) as count FROM delivery_notes");
        $stats['delivery_notes'] = $stmt->fetch()['count'];
        
        // Count letters
        $stmt = $conn->query("SELECT COUNT(*) as count FROM letters");
        $stats['letters'] = $stmt->fetch()['count'];
        
        // Get total project value
        $stmt = $conn->query("SELECT SUM(total_value) as total FROM projects WHERE status IN ('ongoing', 'completed')");
        $result = $stmt->fetch();
        $stats['total_value'] = $result['total'] ?? 0;
        
        // Get recent invoices
        $stmt = $conn->query("SELECT i.*, c.name as customer_name 
                              FROM invoices i 
                              LEFT JOIN customers c ON i.customer_id = c.id 
                              ORDER BY i.created_at DESC LIMIT 5");
        $recent_invoices = $stmt->fetchAll();
        
        // Get recent projects
        $stmt = $conn->query("SELECT p.*, c.name as customer_name 
                              FROM projects p 
                              LEFT JOIN customers c ON p.customer_id = c.id 
                              ORDER BY p.created_at DESC LIMIT 5");
        $recent_projects = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // Handle error silently
    }
}

// Start output buffering for content
ob_start();
?>

<div class="page-heading">
    <h3>Dashboard</h3>
    <p>Selamat datang, <?php echo $_SESSION['full_name']; ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card purple">
            <h6>Total Customer</h6>
            <h2><?php echo $stats['customers']; ?></h2>
            <i class="bi bi-people icon"></i>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card blue">
            <h6>Total Project</h6>
            <h2><?php echo $stats['projects']; ?></h2>
            <i class="bi bi-briefcase icon"></i>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card green">
            <h6>Total Invoice</h6>
            <h2><?php echo $stats['invoices']; ?></h2>
            <i class="bi bi-receipt icon"></i>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card orange">
            <h6>Nilai Project</h6>
            <h2><?php echo formatRupiah($stats['total_value']); ?></h2>
            <i class="bi bi-cash icon"></i>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-box-seam" style="font-size: 40px; color: #667eea;"></i>
                <h5 class="mt-3"><?php echo $stats['items']; ?></h5>
                <p class="text-muted mb-0">Barang/Jasa</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-cash-coin" style="font-size: 40px; color: #7ac29a;"></i>
                <h5 class="mt-3"><?php echo $stats['receipts']; ?></h5>
                <p class="text-muted mb-0">Kwitansi</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-truck" style="font-size: 40px; color: #5b9bd5;"></i>
                <h5 class="mt-3"><?php echo $stats['delivery_notes']; ?></h5>
                <p class="text-muted mb-0">Faktur Barang</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-envelope" style="font-size: 40px; color: #f7b731;"></i>
                <h5 class="mt-3"><?php echo $stats['letters']; ?></h5>
                <p class="text-muted mb-0">Surat</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Data -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoice Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_invoices)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_invoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Belum ada invoice</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_projects)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Project</th>
                                <th>Customer</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['code']); ?></td>
                                <td><?php echo htmlspecialchars($project['name']); ?></td>
                                <td><?php echo htmlspecialchars($project['customer_name']); ?></td>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Belum ada project</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/app/views/layouts/main.php';
?>
