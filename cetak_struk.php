<?php
// cetak_struk.php - Halaman Cetak Struk Peminjaman & Pengambilan (di root folder)
include "koneksi.php";

// Cek jenis transaksi
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'pinjam'; // pinjam atau ambil
$id_pinjaman = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_ambil = isset($_GET['id_ambil']) ? (int)$_GET['id_ambil'] : 0;
$kode_pinjaman = isset($_GET['kode']) ? mysqli_real_escape_string($koneksi, $_GET['kode']) : '';
$data_json = isset($_GET['data']) ? base64_decode(urldecode($_GET['data'])) : '';

$data = null;
$title = "Struk Peminjaman";

// Debugging (hapus jika sudah berfungsi)
// echo "DEBUG: jenis=$jenis, kode=$kode_pinjaman, id=$id_pinjaman, data_json=" . (!empty($data_json) ? 'ada' : 'kosong');

// ==================== PEMINJAMAN ====================
if ($jenis == 'pinjam') {
    // Jika panggilan via KODE PINJAMAN (dari riwayat_pinjam - MULTIPLE BARANG)
    if (!empty($kode_pinjaman)) {
        $query = mysqli_query($koneksi, "
            SELECT 
                p.kode_pinjaman,
                p.tgl_pinjam,
                p.tgl_perkiraan_balik,
                p.jumlah_pinjam,
                p.tujuan_gunabarang,
                p.status,
                u.nama_lengkap,
                b.nama_brg,
                b.spesifikasi_brg,
                b.merk_brg
            FROM tbl_pinjaman p
            JOIN tb_user u ON p.id_user = u.id_user
            JOIN tbl_barang b ON p.id_brg = b.id_brg
            WHERE p.kode_pinjaman = '$kode_pinjaman'
        ");
        
        if (mysqli_num_rows($query) > 0) {
            $barang_list = [];
            $total_unit = 0;
            $total_barang = 0;
            $tanggal = '';
            $tgl_kembali = '';
            $nama_peminjam = '';
            $tujuan = '';
            
            while ($row = mysqli_fetch_assoc($query)) {
                $barang_list[] = [
                    'nama' => $row['nama_brg'],
                    'spesifikasi' => $row['spesifikasi_brg'],
                    'merk' => $row['merk_brg'],
                    'jumlah' => $row['jumlah_pinjam']
                ];
                $total_unit += $row['jumlah_pinjam'];
                $total_barang++;
                
                // Ambil info dari baris pertama
                if (empty($tanggal)) {
                    $tanggal = $row['tgl_pinjam'];
                    $tgl_kembali = $row['tgl_perkiraan_balik'];
                    $nama_peminjam = $row['nama_lengkap'];
                    $tujuan = $row['tujuan_gunabarang'];
                }
            }
            
            $data = [
                'nomor' => $kode_pinjaman,
                'tanggal' => $tanggal,
                'nama' => $nama_peminjam,
                'tgl_kembali' => $tgl_kembali,
                'tujuan' => $tujuan,
                'alamat_ruang' => '',
                'total_barang' => $total_barang,
                'total_unit' => $total_unit,
                'barang' => $barang_list,
                'jenis' => 'pinjam'
            ];
            $title = "Struk Peminjaman - " . $data['nomor'];
        }
    } 
    // Jika panggilan via ID (dari riwayat_pinjam - SINGLE BARANG)
    elseif ($id_pinjaman > 0) {
        $query = mysqli_query($koneksi, "
            SELECT 
                p.id_pinjaman,
                p.kode_pinjaman,
                p.tgl_pinjam,
                p.tgl_perkiraan_balik,
                p.jumlah_pinjam,
                p.tujuan_gunabarang,
                p.status,
                u.nama_lengkap,
                b.nama_brg,
                b.spesifikasi_brg,
                b.merk_brg
            FROM tbl_pinjaman p
            JOIN tb_user u ON p.id_user = u.id_user
            JOIN tbl_barang b ON p.id_brg = b.id_brg
            WHERE p.id_pinjaman = '$id_pinjaman'
        ");
        
        $row = mysqli_fetch_assoc($query);
        
        if ($row) {
            // Jika ada kode_pinjaman, ambil semua barang dengan kode yang sama
            if (!empty($row['kode_pinjaman'])) {
                // Redirect ke versi kode
                header("Location: cetak_struk.php?jenis=pinjam&kode=" . urlencode($row['kode_pinjaman']));
                exit;
            }
            
            // Fallback jika tidak ada kode
            $nomor = !empty($row['kode_pinjaman']) ? $row['kode_pinjaman'] : 'INV/' . str_pad($row['id_pinjaman'], 6, '0', STR_PAD_LEFT);
            
            $data = [
                'nomor' => $nomor,
                'tanggal' => $row['tgl_pinjam'],
                'nama' => $row['nama_lengkap'],
                'tgl_kembali' => $row['tgl_perkiraan_balik'],
                'tujuan' => $row['tujuan_gunabarang'],
                'alamat_ruang' => '',
                'total_barang' => 1,
                'total_unit' => $row['jumlah_pinjam'],
                'barang' => [
                    [
                        'nama' => $row['nama_brg'],
                        'spesifikasi' => $row['spesifikasi_brg'],
                        'merk' => $row['merk_brg'],
                        'jumlah' => $row['jumlah_pinjam']
                    ]
                ],
                'jenis' => 'pinjam'
            ];
            $title = "Struk Peminjaman - " . $data['nomor'];
        }
    } 
    // Jika panggilan via data JSON (dari peminjaman.php - MULTIPLE BARANG)
    else if (!empty($data_json)) {
        $data = json_decode($data_json, true);
        if ($data) {
            $data['jenis'] = 'pinjam';
            $data['nama'] = $data['peminjam'] ?? '';
            $data['alamat_ruang'] = '';
            $title = "Struk Peminjaman - " . ($data['nomor'] ?? '');
        }
    }
}

// ==================== PENGAMBILAN ====================
else if ($jenis == 'ambil') {
    if ($id_ambil > 0) {
        $query = mysqli_query($koneksi, "
            SELECT 
                a.id_ambil,
                a.tgl_brg_keluar,
                a.jumlah_brg,
                a.tujuan_gunabarang,
                a.alamat_ruang,
                u.nama_lengkap,
                b.nama_brg,
                b.spesifikasi_brg,
                b.merk_brg
            FROM tbl_ambil a
            JOIN tb_user u ON a.id_user = u.id_user
            JOIN tbl_barang b ON a.id_brg = b.id_brg
            WHERE a.id_ambil = '$id_ambil'
        ");
        
        $row = mysqli_fetch_assoc($query);
        
        if ($row) {
            $data = [
                'nomor' => 'AMB/' . str_pad($row['id_ambil'], 6, '0', STR_PAD_LEFT),
                'tanggal' => $row['tgl_brg_keluar'],
                'nama' => $row['nama_lengkap'],
                'alamat_ruang' => $row['alamat_ruang'],
                'tujuan' => $row['tujuan_gunabarang'],
                'tgl_kembali' => '',
                'total_barang' => 1,
                'total_unit' => $row['jumlah_brg'],
                'barang' => [
                    [
                        'nama' => $row['nama_brg'],
                        'spesifikasi' => $row['spesifikasi_brg'],
                        'merk' => $row['merk_brg'],
                        'jumlah' => $row['jumlah_brg']
                    ]
                ],
                'jenis' => 'ambil'
            ];
            $title = "Struk Pengambilan - " . $data['nomor'];
        }
    }
    else if (!empty($data_json)) {
        $data = json_decode($data_json, true);
        if ($data) {
            $data['jenis'] = 'ambil';
            $data['nama'] = $data['pengambil'] ?? $data['peminjam'] ?? '';
            $title = "Struk Pengambilan - " . ($data['nomor'] ?? '');
        }
    }
}

if (!$data || empty($data['barang'])) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error { color: red; }
            .debug { background: #f8f9fa; padding: 10px; margin: 10px; font-size: 12px; text-align: left; }
        </style>
    </head>
    <body>
        <div class='error'>
            <h2>Error</h2>
            <p>Tidak ada data struk! Silakan lakukan transaksi terlebih dahulu.</p>
            <div class='debug'>
                <strong>Debug Info:</strong><br>
                Jenis: <?= htmlspecialchars($jenis); ?><br>
                Kode: <?= htmlspecialchars($kode_pinjaman); ?><br>
                ID Pinjam: <?= $id_pinjaman; ?><br>
                ID Ambil: <?= $id_ambil; ?><br>
                Data JSON: <?= !empty($data_json) ? 'Ada' : 'Kosong'; ?>
            </div>
            <a href='admin.php?page=dashboard'>Kembali ke Dashboard</a>
        </div>
    </body>
    </html>";
    exit;
}

$warna_header = ($data['jenis'] == 'pinjam') ? '#007bff' : '#17a2b8';
$warna_button = ($data['jenis'] == 'pinjam') ? '#007bff' : '#17a2b8';
$judul = ($data['jenis'] == 'pinjam') ? 'STRUK PEMINJAMAN BARANG' : 'STRUK PENGAMBILAN BARANG';
$link_kembali = ($data['jenis'] == 'pinjam') ? 'admin.php?page=riwayat_pinjam' : 'admin.php?page=set_members';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
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
            flex-direction: column;
        }
        .struk { 
            width: 80mm; 
            max-width: 100%; 
            background: white; 
            padding: 12px 10px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.2); 
            border-radius: 5px; 
        }
        .header { 
            text-align: center; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 10px; 
            margin-bottom: 12px; 
        }
        .header h2 { 
            font-size: 16px; 
            margin-bottom: 5px; 
        }
        .header p { 
            font-size: 11px; 
            color: #666; 
            margin: 2px 0;
        }
        
        /* Style untuk semua baris informasi - RATA KIRI */
        .info-row { 
            display: flex; 
            margin-bottom: 8px; 
            font-size: 12px;
            line-height: 1.4;
        }
        .info-label { 
            font-weight: bold; 
            width: 105px; 
            min-width: 105px;
        }
        .info-value { 
            flex: 1;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .tgl-kembali, .info-ruang { 
            background: #fff3cd; 
            padding: 10px; 
            text-align: center; 
            margin: 12px 0; 
            font-size: 11px; 
            border-radius: 4px; 
        }
        .info-ruang { background: #e7f3ff; }
        
        .table-barang { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 11px; 
            margin: 12px 0; 
        }
        .table-barang th, .table-barang td { 
            border-bottom: 1px dotted #ccc; 
            padding: 6px 3px; 
            text-align: left; 
        }
        .table-barang th { 
            text-align: center; 
            background: #f0f0f0; 
            font-weight: bold;
        }
        .table-barang td:first-child, .table-barang th:first-child { text-align: center; width: 15%; }
        .table-barang td:last-child, .table-barang th:last-child { text-align: center; width: 15%; }
        
        /* Total section */
        .total-section {
            margin: 10px 0;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        .total-row {
            display: flex;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .total-label {
            font-weight: bold;
            width: 105px;
        }
        .total-value {
            flex: 1;
        }
        
        /* Tujuan section */
        .tujuan-row {
            display: flex;
            margin: 10px 0 8px 0;
            font-size: 12px;
        }
        .tujuan-label {
            font-weight: bold;
            width: 105px;
        }
        .tujuan-value {
            flex: 1;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .footer { 
            text-align: center; 
            border-top: 1px dashed #000; 
            padding-top: 10px; 
            margin-top: 12px; 
            font-size: 10px; 
            color: #666; 
        }
        .footer p {
            margin: 3px 0;
        }
        
        .btn-print { 
            display: flex; 
            justify-content: center; 
            margin-top: 15px; 
            gap: 10px; 
        }
        .btn-print button { 
            padding: 8px 20px; 
            font-size: 12px; 
            cursor: pointer; 
            border: none; 
            border-radius: 5px; 
            font-family: inherit; 
        }
        .btn-cetak { background: <?= $warna_button; ?>; color: white; }
        .btn-tutup { background: #6c757d; color: white; }
        
        @media print { 
            body { background: white; padding: 0; margin: 0; } 
            .btn-print { display: none; } 
            .struk { box-shadow: none; padding: 0; width: 100%; } 
        }
        
        @media (max-width: 480px) {
            .info-label, .total-label, .tujuan-label {
                width: 90px;
                min-width: 90px;
                font-size: 11px;
            }
            .info-value, .total-value, .tujuan-value {
                font-size: 11px;
            }
            .struk {
                padding: 8px 6px;
            }
            .info-row, .total-row, .tujuan-row {
                flex-wrap: wrap;
            }
            .info-label, .total-label, .tujuan-label {
                width: 100%;
                margin-bottom: 3px;
            }
            .info-value, .total-value, .tujuan-value {
                width: 100%;
                margin-left: 0;
            }
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
        
        <div style="text-align: center; background: <?= $warna_header; ?>; color: white; border-radius: 5px; margin-bottom: 12px; padding: 8px;">
            <strong style="font-size: 12px;"><?= $judul; ?></strong>
        </div>
        
        <!-- Info Transaksi -->
        <div class="info-row">
            <div class="info-label">No. Transaksi</div>
            <div class="info-value">: <?= htmlspecialchars($data['nomor']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div class="info-value">: <?= date('d-m-Y', strtotime($data['tanggal'])); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><?= ($data['jenis'] == 'pinjam') ? 'Peminjam' : 'Pengambil'; ?></div>
            <div class="info-value">: <?= htmlspecialchars($data['nama']); ?></div>
        </div>
        
        <!-- Info Khusus -->
        <?php if ($data['jenis'] == 'pinjam' && !empty($data['tgl_kembali'])): ?>
        <div class="tgl-kembali">
            <strong>⚠️ Harus Dikembalikan Sebelum:</strong><br>
            <?= date('d-m-Y', strtotime($data['tgl_kembali'])); ?>
        </div>
        <?php elseif ($data['jenis'] == 'ambil' && !empty($data['alamat_ruang'])): ?>
        <div class="info-ruang">
            <strong>📍 Alamat Ruang:</strong><br>
            <?= htmlspecialchars($data['alamat_ruang']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Tabel Barang -->
        <table class="table-barang">
            <thead>
                <tr><th>No</th><th>Nama Barang</th><th>Jml</th></tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($data['barang'] as $item): ?>
                <tr>
                    <td style="text-align:center; width:15%;"><?= $no++; ?></td>
                    <td style="width:70%;">
                        <?= htmlspecialchars($item['nama']); ?><br>
                        <small style="color:#666"><?= htmlspecialchars($item['spesifikasi']); ?> - <?= htmlspecialchars($item['merk']); ?></small>
                    </td>
                    <td style="text-align:center; width:15%;"><?= $item['jumlah']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Total -->
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">Total Item</div>
                <div class="total-value">: <?= $data['total_barang']; ?> Item</div>
            </div>
            <div class="total-row">
                <div class="total-label">Total Pcs</div>
                <div class="total-value">: <?= $data['total_unit']; ?> Pcs</div>
            </div>
        </div>
        
        <!-- Tujuan -->
        <div class="tujuan-row">
            <div class="tujuan-label">Tujuan Penggunaan</div>
            <div class="tujuan-value">: <?= htmlspecialchars($data['tujuan']); ?></div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih, barang harus <?= ($data['jenis'] == 'pinjam') ? 'dikembalikan' : 'dijaga'; ?></p>
            <p>dalam kondisi baik</p>
            <p>Petugas,</p>
            <br>
            <p>(_____________________)</p>
        </div>
    </div>
    
    <div class="btn-print">
        <button class="btn-cetak" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-tutup" onclick="window.location.href='<?= $link_kembali; ?>'">✖️ Tutup</button>
    </div>
    
    <script>
        <?php if (isset($_GET['auto_print']) && $_GET['auto_print'] == 1): ?>
        setTimeout(function() {
            window.print();
        }, 500);
        <?php endif; ?>
    </script>
</body>
</html>