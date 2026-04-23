<?php
// cetak_struk.php - Halaman Cetak Struk Peminjaman (di root folder)

// Ambil data dari URL parameter
$data_json = isset($_GET['data']) ? base64_decode(urldecode($_GET['data'])) : '';

if (empty($data_json)) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <div class='error'>
            <h2>Error</h2>
            <p>Tidak ada data struk! Silakan lakukan peminjaman terlebih dahulu.</p>
            <a href='admin.php?page=peminjaman'>Kembali ke Form Peminjaman</a>
        </div>
    </body>
    </html>";
    exit;
}

$data = json_decode($data_json, true);
if (!$data) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <div class='error'>
            <h2>Error</h2>
            <p>Data struk tidak valid!</p>
            <a href='admin.php?page=peminjaman'>Kembali ke Form Peminjaman</a>
        </div>
    </body>
    </html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Peminjaman - <?= $data['nomor']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            background: #e0e0e0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 20px; 
        }
        .struk { 
            width: 80mm; 
            max-width: 100%; 
            background: white; 
            padding: 10px 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.2); 
            border-radius: 5px; 
        }
        .header { 
            text-align: center; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 8px; 
            margin-bottom: 8px; 
        }
        .header h2 { font-size: 14px; margin-bottom: 3px; }
        .header p { font-size: 10px; color: #666; }
        .info { margin-bottom: 10px; font-size: 11px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .info-label { font-weight: bold; }
        .info-value { text-align: right; }
        .table-barang { width: 100%; border-collapse: collapse; font-size: 10px; margin: 8px 0; }
        .table-barang th, .table-barang td { border-bottom: 1px dotted #ccc; padding: 5px 2px; text-align: left; }
        .table-barang th { text-align: center; background: #f0f0f0; }
        .table-barang td:last-child, .table-barang th:last-child { text-align: center; }
        .total { border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px; font-size: 11px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .footer { text-align: center; border-top: 1px dashed #000; padding-top: 8px; margin-top: 10px; font-size: 9px; color: #666; }
        .tgl-kembali { background: #fff3cd; padding: 6px; text-align: center; margin: 8px 0; font-size: 10px; border-radius: 4px; }
        .btn-print { display: flex; justify-content: center; margin-top: 15px; gap: 10px; }
        .btn-print button { padding: 8px 20px; font-size: 12px; cursor: pointer; border: none; border-radius: 5px; font-family: inherit; }
        .btn-cetak { background: #007bff; color: white; }
        .btn-tutup { background: #6c757d; color: white; }
        .btn-kembali { background: #28a745; color: white; }
        @media print { 
            body { background: white; padding: 0; margin: 0; } 
            .btn-print { display: none; } 
            .struk { box-shadow: none; padding: 0; width: 100%; } 
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="header">
            <h2>INVENTARIS PPLG</h2>
            <p>Jl. Raya Iser Petarukan</p>
            <p>Pemalang 52362</p>
            <p>Telp. (0284) 3279529</p>
        </div>
        
        <div class="info">
            <div class="info-row">
                <span class="info-label">No. Transaksi</span>
                <span class="info-value">: <?= htmlspecialchars($data['nomor']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pinjam</span>
                <span class="info-value">: <?= date('d-m-Y', strtotime($data['tanggal'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Peminjam</span>
                <span class="info-value">: <?= htmlspecialchars($data['peminjam']); ?></span>
            </div>
        </div>
        
        <div class="tgl-kembali">
            <strong>⚠️ Harus Dikembalikan Sebelum:</strong><br>
            <?= date('d-m-Y', strtotime($data['tgl_kembali'])); ?>
        </div>
        
        <table class="table-barang">
            <thead>
                <tr><th>No</th><th>Nama Barang</th><th>Jml</th></tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($data['barang'] as $item): ?>
                <tr>
                    <td style="text-align:center"><?= $no++; ?></td>
                    <td>
                        <?= htmlspecialchars($item['nama']); ?><br>
                        <small style="color:#666"><?= htmlspecialchars($item['spesifikasi']); ?> - <?= htmlspecialchars($item['merk']); ?></small>
                    </td>
                    <td style="text-align:center"><?= $item['jumlah']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total">
            <div class="total-row">
                <span>Total Jenis Barang</span>
                <span><strong><?= $data['total_barang']; ?></strong> jenis</span>
            </div>
            <div class="total-row">
                <span>Total Unit</span>
                <span><strong><?= $data['total_unit']; ?></strong> unit</span>
            </div>
        </div>
        
        <div class="info" style="margin-top: 8px;">
            <div class="info-row">
                <span class="info-label">Tujuan Penggunaan</span>
                <span class="info-value">: <?= htmlspecialchars($data['tujuan']); ?></span>
            </div>
        </div>
        
        <div class="footer">
            <p>Terima kasih, barang harus dikembalikan<br>dalam kondisi baik</p>
            <p>Petugas,</p>
            <br><br>
            <p>(_____________________)</p>
        </div>
    </div>
    
    <div class="btn-print">
        <button class="btn-cetak" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-tutup" onclick="window.location.href='admin.php?page=riwayat_pinjam'">✖️ Tutup</button>
    </div>
    
    <script>
        // Auto print setelah halaman selesai dimuat
        setTimeout(function() {
            window.print();
        }, 1000);
    </script>
</body>
</html>