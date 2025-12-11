<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

// Get receipt data
$stmt = $conn->prepare("SELECT r.*, c.name as customer_name, c.address as customer_address, 
                        c.phone as customer_phone, p.name as project_name
                        FROM receipts r
                        LEFT JOIN customers c ON r.customer_id = c.id
                        LEFT JOIN projects p ON r.project_id = p.id
                        WHERE r.id = :id");
$stmt->execute(['id' => $id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    die("Kwitansi tidak ditemukan");
}

// Simple HTML/CSS for print
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kwitansi - <?php echo $receipt['receipt_number']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24pt;
            color: #667eea;
        }
        .header p {
            margin: 5px 0;
        }
        .receipt-info {
            margin: 20px 0;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 5px 0;
        }
        .amount-box {
            border: 2px solid #000;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            background: #f9f9f9;
        }
        .amount-box .label {
            font-size: 14pt;
            font-weight: bold;
        }
        .amount-box .value {
            font-size: 20pt;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
        }
        .signature-box.right {
            float: right;
        }
        .signature-line {
            margin-top: 60px;
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
        <h1>Q-BERKAS</h1>
        <p>Sistem Manajemen Dokumen & Invoice</p>
        <p>Email: info@qberkas.com | Telp: (021) 1234-5678</p>
    </div>
    
    <h2 style="text-align: center; margin: 20px 0;">KWITANSI PEMBAYARAN</h2>
    
    <div class="receipt-info">
        <table>
            <tr>
                <td style="width: 150px;"><strong>No. Kwitansi</strong></td>
                <td>: <?php echo htmlspecialchars($receipt['receipt_number']); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>: <?php echo formatDate($receipt['receipt_date']); ?></td>
            </tr>
        </table>
    </div>
    
    <div style="margin: 20px 0;">
        <p><strong>Telah terima dari:</strong></p>
        <table style="margin-left: 20px;">
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: <?php echo htmlspecialchars($receipt['customer_name']); ?></td>
            </tr>
            <?php if ($receipt['customer_address']): ?>
            <tr>
                <td>Alamat</td>
                <td>: <?php echo htmlspecialchars($receipt['customer_address']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($receipt['project_name']): ?>
            <tr>
                <td>Project</td>
                <td>: <?php echo htmlspecialchars($receipt['project_name']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="amount-box">
        <div class="label">Jumlah Uang</div>
        <div class="value"><?php echo formatRupiah($receipt['amount']); ?></div>
        <div style="font-style: italic;">
            (<?php 
            // Convert number to Indonesian words (simplified)
            $words = "";
            echo "Terbilang: " . ucwords($words) . " Rupiah";
            ?>)
        </div>
    </div>
    
    <div style="margin: 20px 0;">
        <p><strong>Untuk Pembayaran:</strong></p>
        <p style="margin-left: 20px;"><?php echo nl2br(htmlspecialchars($receipt['description'])); ?></p>
    </div>
    
    <div style="margin: 20px 0;">
        <table>
            <tr>
                <td style="width: 150px;"><strong>Metode Pembayaran</strong></td>
                <td>: <?php echo htmlspecialchars($receipt['payment_method']); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="signature">
        <div class="signature-box">
            <p>Penyetor,</p>
            <div class="signature-line">
                <?php echo htmlspecialchars($receipt['customer_name']); ?>
            </div>
        </div>
        <div class="signature-box right">
            <p><?php echo date('d F Y', strtotime($receipt['receipt_date'])); ?></p>
            <p>Penerima,</p>
            <div class="signature-line">
                <?php echo htmlspecialchars($receipt['received_by']); ?>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    
    <div style="margin-top: 50px; font-size: 10pt; color: #666; text-align: center;">
        <p>Dokumen ini dibuat secara otomatis oleh sistem Q-Berkas</p>
    </div>
</body>
</html>
