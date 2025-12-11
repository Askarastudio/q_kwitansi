<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

// Get delivery note data with items
$stmt = $conn->prepare("SELECT dn.*, c.name as customer_name, c.address as customer_address, 
                        c.phone as customer_phone, p.name as project_name
                        FROM delivery_notes dn
                        LEFT JOIN customers c ON dn.customer_id = c.id
                        LEFT JOIN projects p ON dn.project_id = p.id
                        WHERE dn.id = :id");
$stmt->execute(['id' => $id]);
$delivery_note = $stmt->fetch();

if (!$delivery_note) {
    die("Faktur barang tidak ditemukan");
}

// Get items
$stmt = $conn->prepare("SELECT dni.*, i.code as item_code
                        FROM delivery_note_items dni
                        LEFT JOIN items i ON dni.item_id = i.id
                        WHERE dni.delivery_note_id = :id");
$stmt->execute(['id' => $id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Faktur Barang - <?php echo $delivery_note['delivery_number']; ?></title>
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
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20pt;
            color: #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 0;
        }
        .items-table {
            margin: 20px 0;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .signature {
            margin-top: 40px;
        }
        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
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
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak</button>
    
    <div class="header">
        <h1>FAKTUR BARANG / SURAT JALAN</h1>
        <p>Q-BERKAS - Sistem Manajemen Dokumen</p>
    </div>
    
    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kepada:</strong><br>
                <?php echo htmlspecialchars($delivery_note['customer_name']); ?><br>
                <?php echo htmlspecialchars($delivery_note['customer_address']); ?><br>
                Telp: <?php echo htmlspecialchars($delivery_note['customer_phone']); ?>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <strong>No. Faktur:</strong> <?php echo htmlspecialchars($delivery_note['delivery_number']); ?><br>
                <strong>Tanggal:</strong> <?php echo formatDate($delivery_note['delivery_date']); ?><br>
                <?php if ($delivery_note['project_name']): ?>
                <strong>Project:</strong> <?php echo htmlspecialchars($delivery_note['project_name']); ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode</th>
                <th>Deskripsi Barang</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 10%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="5" style="text-align: center; font-style: italic;">Tidak ada item</td>
            </tr>
            <?php else: ?>
            <?php $no = 1; foreach ($items as $item): ?>
            <tr>
                <td style="text-align: center;"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['quantity'], 2); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($item['unit']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($delivery_note['notes']): ?>
    <div style="margin: 20px 0;">
        <strong>Catatan:</strong><br>
        <?php echo nl2br(htmlspecialchars($delivery_note['notes'])); ?>
    </div>
    <?php endif; ?>
    
    <div class="signature">
        <div class="signature-box">
            <p>Pengirim,</p>
            <div class="signature-line">
                <?php echo htmlspecialchars($delivery_note['delivered_by']); ?>
            </div>
        </div>
        <div class="signature-box" style="margin: 0 10%;">
            <p>Sopir/Kurir,</p>
            <div class="signature-line">
                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
            </div>
        </div>
        <div class="signature-box">
            <p>Penerima,</p>
            <div class="signature-line">
                <?php echo htmlspecialchars($delivery_note['received_by'] ?: '(________________)'); ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 50px; font-size: 10pt; color: #666; text-align: center;">
        <p>Dokumen ini dibuat secara otomatis oleh sistem Q-Berkas</p>
    </div>
</body>
</html>
