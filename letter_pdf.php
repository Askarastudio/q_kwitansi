<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = $_GET['id'] ?? 0;

$db = new Database();
$conn = $db->getConnection();

// Get letter data
$stmt = $conn->prepare("SELECT l.*, c.name as customer_name, c.address as customer_address, 
                        p.name as project_name
                        FROM letters l
                        LEFT JOIN customers c ON l.customer_id = c.id
                        LEFT JOIN projects p ON l.project_id = p.id
                        WHERE l.id = :id");
$stmt->execute(['id' => $id]);
$letter = $stmt->fetch();

if (!$letter) {
    die("Surat tidak ditemukan");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat - <?php echo $letter['letter_number']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 25mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.8;
            margin: 0;
            padding: 20px;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
        }
        .letterhead h1 {
            margin: 0;
            font-size: 18pt;
            color: #667eea;
        }
        .letterhead p {
            margin: 2px 0;
            font-size: 10pt;
        }
        .letter-meta {
            margin: 20px 0;
        }
        .letter-meta table {
            width: 100%;
        }
        .letter-meta td {
            padding: 3px 0;
        }
        .letter-content {
            text-align: justify;
            margin: 30px 0;
        }
        .signature {
            margin-top: 50px;
            float: right;
            text-align: center;
            width: 200px;
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
    
    <div class="letterhead">
        <h1>Q-BERKAS</h1>
        <p>Sistem Manajemen Dokumen & Invoice</p>
        <p>Jl. Contoh No. 123, Jakarta 12345 | Telp: (021) 1234-5678 | Email: info@qberkas.com</p>
    </div>
    
    <div class="letter-meta">
        <table>
            <tr>
                <td style="width: 100px;">Nomor</td>
                <td style="width: 10px;">:</td>
                <td><?php echo htmlspecialchars($letter['letter_number']); ?></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong><?php echo htmlspecialchars($letter['subject']); ?></strong></td>
            </tr>
        </table>
    </div>
    
    <div style="margin: 30px 0;">
        <p style="margin: 0;">
            Jakarta, <?php echo date('d F Y', strtotime($letter['letter_date'])); ?>
        </p>
    </div>
    
    <div style="margin: 20px 0;">
        <p style="margin: 0;">Kepada Yth,</p>
        <p style="margin: 0;"><strong><?php echo htmlspecialchars($letter['recipient']); ?></strong></p>
        <?php if ($letter['customer_address']): ?>
        <p style="margin: 0;"><?php echo htmlspecialchars($letter['customer_address']); ?></p>
        <?php endif; ?>
    </div>
    
    <div style="margin: 30px 0;">
        <p>Dengan hormat,</p>
    </div>
    
    <div class="letter-content">
        <?php echo nl2br(htmlspecialchars($letter['content'])); ?>
    </div>
    
    <div style="margin: 30px 0;">
        <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    </div>
    
    <div class="signature">
        <p>Hormat kami,</p>
        <p><strong>Q-BERKAS</strong></p>
        <div class="signature-line">
            <strong>Direktur</strong>
        </div>
    </div>
    <div style="clear: both;"></div>
    
    <div style="margin-top: 50px; font-size: 9pt; color: #666;">
        <p><em>Catatan: <?php echo htmlspecialchars($letter['type']); ?></em></p>
        <?php if ($letter['project_name']): ?>
        <p><em>Project: <?php echo htmlspecialchars($letter['project_name']); ?></em></p>
        <?php endif; ?>
    </div>
</body>
</html>
