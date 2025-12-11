<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - Q-Berkas</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar Styles */
        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            padding: 0 20px;
            margin-bottom: 20px;
        }
        
        .menu-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 0.5;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin: 0 20px 5px;
            transition: all 0.3s;
        }
        
        .menu-item:hover,
        .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand h4 {
            margin: 0;
            font-weight: 700;
            color: #333;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info .name {
            font-weight: 600;
            color: #333;
            display: block;
        }
        
        .user-info .role {
            font-size: 12px;
            color: #999;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
        }
        
        .page-heading {
            margin-bottom: 30px;
        }
        
        .page-heading h3 {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .page-heading p {
            color: #999;
            margin: 0;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 20px;
            font-weight: 700;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Stats Cards */
        .stats-card {
            border-radius: 15px;
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card.blue {
            background: linear-gradient(135deg, #5b9bd5 0%, #3b7fc4 100%);
        }
        
        .stats-card.green {
            background: linear-gradient(135deg, #7ac29a 0%, #5aaa77 100%);
        }
        
        .stats-card.orange {
            background: linear-gradient(135deg, #f7b731 0%, #f77f00 100%);
        }
        
        .stats-card .icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            opacity: 0.2;
        }
        
        .stats-card h6 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stats-card h2 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            
            #main-content {
                margin-left: 0;
            }
        }
    </style>
    
    <?php if(isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-header">
            <h3><i class="bi bi-file-earmark-text"></i> Q-Berkas</h3>
            <p>Sistem Manajemen Dokumen</p>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Menu Utama</div>
                <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Master Data</div>
                <a href="customers.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Customer
                </a>
                <a href="items.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'items.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i> Barang/Jasa
                </a>
                <a href="projects.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>">
                    <i class="bi bi-briefcase"></i> Project/Pekerjaan
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Dokumen</div>
                <a href="invoices.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                    <i class="bi bi-receipt"></i> Invoice
                </a>
                <a href="receipts.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'receipts.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cash-coin"></i> Kwitansi
                </a>
                <a href="delivery_notes.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'delivery_notes.php' ? 'active' : ''; ?>">
                    <i class="bi bi-truck"></i> Faktur Barang
                </a>
                <a href="letters.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'letters.php' ? 'active' : ''; ?>">
                    <i class="bi bi-envelope"></i> Surat
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Sistem</div>
                <a href="logout.php" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div id="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="navbar-brand">
                <h4><?php echo $page_title ?? 'Dashboard'; ?></h4>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <span class="name"><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                    <span class="role"><?php echo ucfirst($_SESSION['role'] ?? 'user'); ?></span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php echo $content ?? ''; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <?php if(isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                    }
                });
            }
        });
    </script>
</body>
</html>
