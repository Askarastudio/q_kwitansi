<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

// Get invoice data with items
$stmt = $conn->prepare("SELECT i.*, c.name as customer_name, c.address as customer_address, 
                        c.phone as customer_phone, c.email as customer_email,
                        p.name as project_name, u.full_name as creator_name
                        FROM invoices i
                        LEFT JOIN customers c ON i.customer_id = c.id
                        LEFT JOIN projects p ON i.project_id = p.id
                        LEFT JOIN users u ON i.created_by = u.id
                        WHERE i.id = :id");
$stmt->execute(['id' => $id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Invoice tidak ditemukan");
}

// Get invoice items
$stmt = $conn->prepare("SELECT ii.*, i.code as item_code
                        FROM invoice_items ii
                        LEFT JOIN items i ON ii.item_id = i.id
                        WHERE ii.invoice_id = :id");
$stmt->execute(['id' => $id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo $invoice['invoice_number']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 28pt;
            color: #667eea;
        }
        .header .company-info {
            font-size: 10pt;
            color: #666;
        }
        .invoice-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        table {
            width: 100%;
        }
        .info-table td {
            padding: 3px 0;
        }
        .items-table {
            margin: 20px 0;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .items-table th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .total-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .total-section table td {
            padding: 8px;
        }
        .total-section .grand-total {
            background: #667eea;
            color: white;
            font-weight: bold;
            font-size: 14pt;
        }
        .notes {
            margin-top: 40px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak</button>
    
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <h1>INVOICE</h1>
                    <div class="company-info">
                        <strong>Q-BERKAS</strong><br>
                        Jl. Contoh No. 123, Jakarta 12345<br>
                        Telp: (021) 1234-5678<br>
                        Email: info@qberkas.com
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <h2 style="margin: 0; color: #667eea;"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
                    <p style="margin: 5px 0;">
                        <strong>Tanggal:</strong> <?php echo formatDate($invoice['invoice_date']); ?><br>
                        <?php if ($invoice['due_date']): ?>
                        <strong>Jatuh Tempo:</strong> <?php echo formatDate($invoice['due_date']); ?><br>
                        <?php endif; ?>
                        <span class="badge" style="background: <?php 
                            echo $invoice['status'] == 'paid' ? '#28a745' : 
                                ($invoice['status'] == 'sent' ? '#17a2b8' : '#6c757d'); 
                        ?>; color: white; padding: 5px 10px; border-radius: 3px;">
                            <?php echo strtoupper($invoice['status']); ?>
                        </span>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="invoice-info">
        <table>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong style="font-size: 12pt;">TAGIHAN KEPADA:</strong><br>
                    <strong><?php echo htmlspecialchars($invoice['customer_name']); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?><br>
                    <?php if ($invoice['customer_phone']): ?>
                    Telp: <?php echo htmlspecialchars($invoice['customer_phone']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['customer_email']): ?>
                    Email: <?php echo htmlspecialchars($invoice['customer_email']); ?>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top;">
                    <?php if ($invoice['project_name']): ?>
                    <strong>Project:</strong><br>
                    <?php echo htmlspecialchars($invoice['project_name']); ?><br><br>
                    <?php endif; ?>
                    <strong>Dibuat oleh:</strong><br>
                    <?php echo htmlspecialchars($invoice['creator_name']); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Deskripsi</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="6" style="text-align: center; font-style: italic;">Tidak ada item</td>
            </tr>
            <?php else: ?>
            <?php $no = 1; foreach ($items as $item): ?>
            <tr>
                <td style="text-align: center;"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['quantity'], 2); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($item['unit']); ?></td>
                <td style="text-align: right;"><?php echo formatRupiah($item['price']); ?></td>
                <td style="text-align: right;"><?php echo formatRupiah($item['total']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="total-section">
        <table style="width: 100%; border: 1px solid #ddd;">
            <tr>
                <td><strong>Subtotal</strong></td>
                <td style="text-align: right;"><?php echo formatRupiah($invoice['subtotal']); ?></td>
            </tr>
            <?php if ($invoice['tax'] > 0): ?>
            <tr>
                <td>Pajak/Tax</td>
                <td style="text-align: right;"><?php echo formatRupiah($invoice['tax']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($invoice['discount'] > 0): ?>
            <tr>
                <td>Diskon</td>
                <td style="text-align: right;">-<?php echo formatRupiah($invoice['discount']); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="grand-total">
                <td><strong>TOTAL</strong></td>
                <td style="text-align: right;"><strong><?php echo formatRupiah($invoice['total']); ?></strong></td>
            </tr>
        </table>
    </div>
    <div style="clear: both;"></div>
    
    <?php if ($invoice['notes']): ?>
    <div class="notes">
        <strong>Catatan:</strong><br>
        <?php echo nl2br(htmlspecialchars($invoice['notes'])); ?>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p><strong>Terima kasih atas kepercayaan Anda!</strong></p>
        <p>Untuk informasi lebih lanjut, hubungi kami di info@qberkas.com</p>
        <p style="font-size: 9pt; color: #999;">Dokumen ini dibuat secara otomatis oleh sistem Q-Berkas</p>
    </div>
</body>
</html>
