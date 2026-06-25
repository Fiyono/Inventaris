<?php
// kas.php - Pengembalian Barang (MULTIPLE BARANG dalam 1 transaksi)
include "koneksi.php";

// proses pengembalian
if (isset($_POST['simpankembali'])) {
    $kode_pinjaman = isset($_POST['kode_pinjaman']) ? mysqli_real_escape_string($koneksi, $_POST['kode_pinjaman']) : '';
    $tgl_kembali = isset($_POST['tgl_kembali']) ? $_POST['tgl_kembali'] : '';
    $jumlah_kembali_list = isset($_POST['jumlah_kembali']) ? $_POST['jumlah_kembali'] : [];
    $id_pinjaman_list = isset($_POST['id_pinjaman']) ? $_POST['id_pinjaman'] : [];
    $id_brg_list = isset($_POST['id_brg']) ? $_POST['id_brg'] : [];
    $id_user_list = isset($_POST['id_user']) ? $_POST['id_user'] : [];
    $sisa_list = isset($_POST['sisa_belum']) ? $_POST['sisa_belum'] : [];
    
    // Validasi
    if ($tgl_kembali === '' || empty($kode_pinjaman)) {
        echo "<script>alert('Data pengembalian tidak valid.');history.back();</script>";
        exit;
    }
    
    $total_dikembalikan = 0;
    $ada_pengembalian = false;
    
    // Mulai Transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        foreach ($id_pinjaman_list as $index => $id_pinjaman) {
            $id_pinjaman = (int)$id_pinjaman;
            $id_brg = isset($id_brg_list[$index]) ? $id_brg_list[$index] : '';
            $id_user = isset($id_user_list[$index]) ? (int)$id_user_list[$index] : 0;
            $jumlah_kembali = isset($jumlah_kembali_list[$index]) ? (int)$jumlah_kembali_list[$index] : 0;
            $sisa_sebelum = isset($sisa_list[$index]) ? (int)$sisa_list[$index] : 0;
            
            // Skip jika jumlah kembali 0
            if ($jumlah_kembali <= 0) {
                continue;
            }
            
            // Validasi jumlah tidak melebihi sisa
            if ($jumlah_kembali > $sisa_sebelum) {
                throw new Exception("Jumlah pengembalian ($jumlah_kembali) melebihi sisa ($sisa_sebelum) untuk ID pinjaman $id_pinjaman");
            }
            
            // Ambil data pinjaman dengan LOCK
            $stmt_getData = $koneksi->prepare("SELECT * FROM tbl_pinjaman WHERE id_pinjaman=? FOR UPDATE");
            $stmt_getData->bind_param("i", $id_pinjaman);
            $stmt_getData->execute();
            $getData = $stmt_getData->get_result();
            $dataPinjam = $getData->fetch_assoc();
            $stmt_getData->close();
            
            if (!$dataPinjam) {
                throw new Exception("Data pinjaman ID $id_pinjaman tidak ditemukan.");
            }
            
            // Ambil total sudah kembali dari history
            $stmt_history = $koneksi->prepare("SELECT COALESCE(SUM(jumlahbrg_kembali), 0) AS total_kembali FROM tbl_history_pinjam WHERE id_pinjaman=?");
            $stmt_history->bind_param("i", $id_pinjaman);
            $stmt_history->execute();
            $history_result = $stmt_history->get_result();
            $history_data = $history_result->fetch_assoc();
            $total_sudah_kembali = (int)($history_data['total_kembali'] ?? 0);
            $stmt_history->close();
            
            $jumlah_pinjam_awal = (int)$dataPinjam['jumlah_pinjam'];
            
            // Update stok barang
            $stmt_stok = $koneksi->prepare("UPDATE tbl_barang SET jumlah_brg = jumlah_brg + ? WHERE id_brg=?");
            $stmt_stok->bind_param("is", $jumlah_kembali, $id_brg);
            if (!$stmt_stok->execute()) {
                throw new Exception('Gagal update stok barang: ' . $stmt_stok->error);
            }
            $stmt_stok->close();
            
            // Cek status lunas
            $total_kembali_setelah = $total_sudah_kembali + $jumlah_kembali;
            $status_baru = ($total_kembali_setelah >= $jumlah_pinjam_awal) ? 'Dikembalikan' : 'Dipinjam';
            
            // Update status di tbl_pinjaman
            $stmt_update = $koneksi->prepare("UPDATE tbl_pinjaman SET status=?, tgl_kembali=? WHERE id_pinjaman=?");
            $stmt_update->bind_param("ssi", $status_baru, $tgl_kembali, $id_pinjaman);
            if (!$stmt_update->execute()) {
                throw new Exception('Gagal update status pinjaman: ' . $stmt_update->error);
            }
            $stmt_update->close();
            
            // Insert/Update history
            $stmt_cek = $koneksi->prepare("SELECT id_histpinjam FROM tbl_history_pinjam WHERE id_pinjaman=?");
            $stmt_cek->bind_param("i", $id_pinjaman);
            $stmt_cek->execute();
            $cekHistory = $stmt_cek->get_result();
            $stmt_cek->close();
            
            if (mysqli_num_rows($cekHistory) == 0) {
                // Insert history baru
                $tujuan = $dataPinjam['tujuan_gunabarang'];
                $tgl_pinjam = $dataPinjam['tgl_pinjam'];
                
                $stmt_insert = $koneksi->prepare("INSERT INTO tbl_history_pinjam
                    (id_pinjaman, id_user, id_brg, jumlahbrg_pinjam, jumlahbrg_kembali, tujuan_gunabarang, tgl_pinjam, tgl_kembali)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param(
                    "iiisisss",
                    $id_pinjaman, $id_user, $id_brg,
                    $jumlah_pinjam_awal,
                    $jumlah_kembali,
                    $tujuan,
                    $tgl_pinjam,
                    $tgl_kembali
                );
                if (!$stmt_insert->execute()) {
                    throw new Exception('Gagal insert history: ' . $stmt_insert->error);
                }
                $stmt_insert->close();
            } else {
                // Update history existing
                $stmt_update_hist = $koneksi->prepare("UPDATE tbl_history_pinjam 
                    SET jumlahbrg_kembali = jumlahbrg_kembali + ?, tgl_kembali = ?
                    WHERE id_pinjaman = ?");
                $stmt_update_hist->bind_param("isi", $jumlah_kembali, $tgl_kembali, $id_pinjaman);
                if (!$stmt_update_hist->execute()) {
                    throw new Exception('Gagal update history: ' . $stmt_update_hist->error);
                }
                $stmt_update_hist->close();
            }
            
            $total_dikembalikan += $jumlah_kembali;
            $ada_pengembalian = true;
        }
        
        if (!$ada_pengembalian) {
            throw new Exception('Tidak ada barang yang dikembalikan. Minimal isi 1 jumlah pengembalian.');
        }
        
        // Commit transaksi
        mysqli_commit($koneksi);
        
        echo "<script>
                  alert('Pengembalian berhasil! Total $total_dikembalikan pcs barang telah dikembalikan.');
                  window.location.href='admin.php?page=kas';
              </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal memproses pengembalian: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit;
    }
}
?>

<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* ========== STYLE UMUM ========== */
html, body {
    height: auto !important;
    min-height: auto !important;
    overflow-x: hidden;
}

.content-wrapper {
    min-height: auto !important;
}

.card {
    margin-bottom: 0 !important;
    border-radius: 12px;
    overflow: hidden;
}

/* Header tabel */
#example1 thead th {
    background: linear-gradient(45deg, #007bff, #00c6ff);
    color: white;
    text-align: center;
    font-size: 13px;
    padding: 10px;
    font-weight: bold;
}

/* Isi tabel */
#example1 tbody td {
    font-size: 12px;
    text-align: center;
    vertical-align: middle;
    transition: all 0.3s ease;
    font-weight: normal;
    color: black;
    padding: 10px 6px;
}

#example1 tbody tr:hover {
    background-color: #f1f9ff !important;
    transform: scale(1.01);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

#example1 tbody tr:nth-child(even) {
    background-color: #fafafa;
}

/* Foto di tabel */
.img-barang {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Tombol Export Excel */
.btn-export {
    background: linear-gradient(45deg, #28a745, #00c851);
    color: #fff;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    text-decoration: none;
    margin-left: 10px;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-export:hover {
    background: linear-gradient(45deg, #218838, #00994d);
    color: white;
    transform: translateY(-1px);
}

/* Tombol Kembalikan */
.btn-kembali {
    background: linear-gradient(45deg, #28a745, #00c851);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: none;
    cursor: pointer;
}

.btn-kembali:hover {
    background: linear-gradient(45deg, #218838, #00994d);
    color: white;
    transform: translateY(-1px);
}

/* Search & tombol sejajar */
.dataTables_filter {
    display: flex !important;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.dataTables_filter label {
    margin-bottom: 0 !important;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dataTables_filter input {
    margin-left: 5px !important;
    height: 32px;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.card-title {
    font-size: 20px !important;
    font-weight: bold;
}

.card-header {
    padding: 12px 15px;
}

/* Modal */
.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.modal-footer .btn-outline-secondary,
.modal-footer .btn-success {
    border-radius: 20px;
}

/* Info sisa pinjaman */
.info-sisa {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 8px 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 13px;
}

/* Tabel barang dalam modal */
.table-barang-kembali {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-bottom: 15px;
}

.table-barang-kembali th {
    background: #f8f9fa;
    padding: 8px;
    border: 1px solid #dee2e6;
    text-align: center;
    font-size: 11px;
}

.table-barang-kembali td {
    padding: 8px;
    border: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
}

.table-barang-kembali input[type="number"] {
    width: 80px;
    text-align: center;
    padding: 4px;
    border-radius: 4px;
    border: 1px solid #ced4da;
}

.barang-nama {
    text-align: left !important;
    font-weight: bold;
}

/* ========== RESPONSIVE MOBILE ========== */
@media screen and (max-width: 768px) {
    #example1 thead {
        display: none;
    }
    
    #example1 tbody tr {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 15px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 10px;
    }
    
    #example1 tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        font-size: 12px;
    }
    
    #example1 tbody td:last-child {
        border-bottom: none;
    }
    
    #example1 tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #007bff;
        width: 40%;
        font-size: 11px;
    }
    
    #example1 tbody td[data-label="AKSI"] {
        display: flex;
        justify-content: flex-start;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    #example1 tbody td[data-label="AKSI"]:before {
        content: "AKSI";
        font-weight: bold;
        color: #007bff;
        width: 40%;
        font-size: 11px;
    }
    
    .btn-kembali {
        padding: 5px 10px;
        font-size: 11px;
    }
    
    .img-barang {
        width: 40px;
        height: 40px;
    }
    
    .card-header .card-title {
        font-size: 16px !important;
    }
    
    .dataTables_filter {
        flex-direction: column;
        align-items: stretch;
    }
    
    .dataTables_filter label {
        width: 100%;
        justify-content: space-between;
    }
    
    .dataTables_filter input {
        flex: 1;
    }
    
    .btn-export {
        justify-content: center;
        width: 100%;
        margin-left: 0;
        margin-top: 8px;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }
    
    .dataTables_info {
        font-size: 11px;
    }
    
    .modal-dialog {
        margin: 10px;
        width: calc(100% - 20px);
        max-width: none;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 8px;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin: 0;
    }
    
    .table-barang-kembali {
        font-size: 10px;
    }
    
    .table-barang-kembali input[type="number"] {
        width: 60px;
        font-size: 11px;
    }
}

/* Tablet */
@media screen and (min-width: 769px) and (max-width: 1024px) {
    #example1 {
        font-size: 13px;
    }
    
    #example1 thead th {
        font-size: 13px;
        padding: 8px 5px;
    }
    
    #example1 tbody td {
        font-size: 12px;
        padding: 8px 5px;
    }
    
    .img-barang {
        width: 45px;
        height: 45px;
    }
}
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header" style="background: linear-gradient(45deg, #007bff, #00c6ff); color: white;">
                <div class="card-title mb-0" style="font-size:20px; font-weight:bold;">
                    <i class="fas fa-list-alt"></i> DAFTAR PINJAMAN BELUM DIKEMBALIKAN
                </div>
            </div>
            <div class="card-body">

                <table id="example1" class="table table-sm table-striped table-hover table-bordered table-valign-middle">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA PEMINJAM</th>
                            <th>NAMA BARANG</th>
                            <th>ID BARANG</th>
                            <th>JUMLAH PINJAM</th>
                            <th>SUDAH KEMBALI</th>
                            <th>SISA</th>
                            <th>TGL PINJAM</th>
                            <th>TUJUAN</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $root_path = $_SERVER['DOCUMENT_ROOT'] . '/inventaris';
                        $no = 1;
                        
                        // GROUP BY kode_pinjaman (1 transaksi = 1 baris)
                        $sql = mysqli_query($koneksi, "
                            SELECT 
                                p.kode_pinjaman,
                                p.id_user,
                                p.tgl_pinjam,
                                p.tujuan_gunabarang,
                                u.nama_lengkap,
                                COUNT(DISTINCT p.id_brg) AS total_barang,
                                SUM(p.jumlah_pinjam) AS total_pinjam,
                                GROUP_CONCAT(
                                    CONCAT(b.nama_brg, ' (', p.jumlah_pinjam, ' pcs)')
                                    ORDER BY p.id_pinjaman
                                    SEPARATOR '<br>'
                                ) AS detail_barang,
                                GROUP_CONCAT(DISTINCT b.id_brg ORDER BY p.id_pinjaman SEPARATOR ', ') AS id_barang_list,
                                GROUP_CONCAT(DISTINCT b.gambar_brg SEPARATOR ',') AS gambar_list,
                                COALESCE(SUM(h.jumlahbrg_kembali), 0) AS total_kembali
                            FROM tbl_pinjaman p
                            JOIN tb_user u ON p.id_user = u.id_user
                            JOIN tbl_barang b ON p.id_brg = b.id_brg
                            LEFT JOIN tbl_history_pinjam h ON h.id_pinjaman = p.id_pinjaman
                            WHERE p.status = 'Dipinjam'
                            GROUP BY p.kode_pinjaman, p.id_user, p.tgl_pinjam, p.tujuan_gunabarang, u.nama_lengkap
                            HAVING SUM(p.jumlah_pinjam) - COALESCE(SUM(h.jumlahbrg_kembali), 0) > 0
                            ORDER BY MAX(p.id_pinjaman) DESC
                        ");
                        
                        if (!$sql) {
                            echo "<tr><td colspan='10'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else {
                            while ($row = mysqli_fetch_array($sql)) { 
                                $total_pinjam = (int)($row['total_pinjam'] ?? 0);
                                $total_kembali = (int)($row['total_kembali'] ?? 0);
                                $sisa_belum_kembali = $total_pinjam - $total_kembali;
                                $id_barang_list = $row['id_barang_list'] ?? '-';
                                
                                // Ambil gambar pertama
                                $gambar_list = explode(',', $row['gambar_list'] ?? '');
                                $gambar_pertama = !empty($gambar_list[0]) ? $gambar_list[0] : 'default.png';
                                $gambar_path = $root_path . '/dist/upload_img/' . $gambar_pertama;
                                $gambar_web = '/inventaris/dist/upload_img/' . $gambar_pertama;
                            ?>
                            <tr>
                                <td data-label="NO"><?= $no++; ?></td>
                                <td data-label="NAMA PEMINJAM"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td data-label="NAMA BARANG" style="text-align:center;"><?= $row['detail_barang']; ?></td>
                                <td data-label="ID BARANG"><?= htmlspecialchars($id_barang_list); ?></td>
                                <td data-label="JUMLAH PINJAM"><?= $total_pinjam; ?> pcs<br><small>(<?= $row['total_barang']; ?> item)</small></td>
                                <td data-label="SUDAH KEMBALI"><?= $total_kembali; ?> pcs</td>
                                <td data-label="SISA">
                                    <span style="color: red; font-weight: bold;"><?= $sisa_belum_kembali; ?> pcs</span>
                                </td>
                                <td data-label="TGL PINJAM">
                                    <?= !empty($row['tgl_pinjam']) && $row['tgl_pinjam'] != "0000-00-00"
                                        ? date('d-m-Y', strtotime($row['tgl_pinjam']))
                                        : '-' ?>
                                </td>
                                <td data-label="TUJUAN"><?= htmlspecialchars($row['tujuan_gunabarang'] ?? '-'); ?></td>
                                <td data-label="AKSI">
                                    <button type="button" class="btn-kembali" onclick="showModalKembali('<?= $row['kode_pinjaman']; ?>')">
                                        <i class="fas fa-undo-alt"></i> Kembalikan
                                    </button>
                                </td>
                            </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- Modal Pengembalian (Dibuat dinamis via JS) -->
<div id="modalKembaliContainer"></div>

<!-- JS -->
<script>
// Data pinjaman untuk modal (diisi dari PHP)
var dataPinjaman = {};
<?php
// Ambil data detail untuk modal
$sql_modal = mysqli_query($koneksi, "
    SELECT 
        p.id_pinjaman,
        p.kode_pinjaman,
        p.id_brg,
        p.id_user,
        p.jumlah_pinjam,
        p.tujuan_gunabarang,
        u.nama_lengkap,
        b.nama_brg,
        b.gambar_brg,
        COALESCE(SUM(h.jumlahbrg_kembali), 0) AS total_kembali
    FROM tbl_pinjaman p
    JOIN tb_user u ON p.id_user = u.id_user
    JOIN tbl_barang b ON p.id_brg = b.id_brg
    LEFT JOIN tbl_history_pinjam h ON h.id_pinjaman = p.id_pinjaman
    WHERE p.status = 'Dipinjam'
    GROUP BY p.id_pinjaman
    HAVING p.jumlah_pinjam - COALESCE(SUM(h.jumlahbrg_kembali), 0) > 0
    ORDER BY p.kode_pinjaman, p.id_pinjaman
");
while ($row_modal = mysqli_fetch_array($sql_modal)) {
    $kode = $row_modal['kode_pinjaman'];
    if (!isset($dataPinjaman[$kode])) {
        $dataPinjaman[$kode] = [
            'kode' => $kode,
            'nama_lengkap' => $row_modal['nama_lengkap'],
            'tujuan' => $row_modal['tujuan_gunabarang'],
            'barang' => []
        ];
    }
    $dataPinjaman[$kode]['barang'][] = [
        'id_pinjaman' => $row_modal['id_pinjaman'],
        'id_brg' => $row_modal['id_brg'],
        'id_user' => $row_modal['id_user'],
        'nama_brg' => $row_modal['nama_brg'],
        'gambar_brg' => $row_modal['gambar_brg'],
        'jumlah_pinjam' => (int)$row_modal['jumlah_pinjam'],
        'total_kembali' => (int)$row_modal['total_kembali'],
        'sisa' => (int)$row_modal['jumlah_pinjam'] - (int)$row_modal['total_kembali']
    ];
}
?>
dataPinjaman = <?= json_encode($dataPinjaman); ?>;

function showModalKembali(kode) {
    var data = dataPinjaman[kode];
    if (!data) {
        alert('Data tidak ditemukan!');
        return;
    }
    
    var html = `
    <div id="modalKembali" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;justify-content:center;align-items:center;" onclick="if(event.target===this)closeModalKembali()">
        <div style="background:white;border-radius:12px;width:90%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 5px 30px rgba(0,0,0,0.3);">
            <form action="" method="post" style="margin:0;">
                <div style="background:linear-gradient(45deg,#28a745,#00c851);color:white;padding:15px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
                    <h5 style="margin:0;font-weight:bold;">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pengembalian
                    </h5>
                    <button type="button" onclick="closeModalKembali()" style="background:none;border:none;color:white;font-size:24px;cursor:pointer;">&times;</button>
                </div>
                <div style="padding:20px;">
                    <table>
                        <tr>
                            <td><strong>KODE TRANSAKSI</strong></td>
                            <td>:</td>
                            <td>${data.kode}</td>
                        </tr>
                        <tr>
                            <td><strong>NAMA PEMINJAM</strong></td>
                            <td>:</td>
                            <td>${data.nama_lengkap}</td>
                        </tr>
                        <tr>
                            <td><strong>TUJUAN</strong></td>
                            <td>:</td>
                            <td>${data.tujuan || '-'}</td>
                        </tr>
                    </table>
                    
                    <div class="info-sisa">
                        <i class="fas fa-info-circle"></i> 
                        Silakan isi jumlah pengembalian untuk masing-masing barang di bawah ini.
                    </div>
                    
                    <input type="hidden" name="kode_pinjaman" value="${data.kode}">
                    <input type="hidden" name="tgl_kembali" id="tgl_kembali_input" value="<?= date('Y-m-d'); ?>">
                    
                    <table class="table-barang-kembali">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Barang</th>
                                <th>Nama Barang</th>
                                <th>Dipinjam</th>
                                <th>Sudah Kembali</th>
                                <th>Sisa</th>
                                <th>Jumlah Dikembalikan</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    data.barang.forEach(function(item, index) {
        html += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.id_brg}</strong></td>
                <td class="barang-nama">${item.nama_brg}</td>
                <td>${item.jumlah_pinjam} pcs</td>
                <td>${item.total_kembali} pcs</td>
                <td style="color: red; font-weight: bold;">${item.sisa} pcs</td>
                <td>
                    <input type="hidden" name="id_pinjaman[]" value="${item.id_pinjaman}">
                    <input type="hidden" name="id_brg[]" value="${item.id_brg}">
                    <input type="hidden" name="id_user[]" value="${item.id_user}">
                    <input type="hidden" name="sisa_belum[]" value="${item.sisa}">
                    <input type="number" 
                           name="jumlah_kembali[]" 
                           class="form-control form-control-sm" 
                           min="0" 
                           max="${item.sisa}" 
                           value="0"
                           style="width: 80px; display: inline-block; text-align: center;">
                </td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                    
                    <div class="form-group" style="margin-bottom:15px;">
                        <label><i class="fas fa-calendar-alt"></i> Tanggal Pengembalian</label>
                        <input type="date" 
                               class="form-control" 
                               id="tgl_kembali_display"
                               value="<?= date('Y-m-d'); ?>"
                               onchange="document.getElementById('tgl_kembali_input').value = this.value;"
                               required
                               style="width:100%;padding:8px;border:1px solid #ced4da;border-radius:6px;">
                    </div>
                </div>
                <div style="padding:15px 20px;border-top:1px solid #dee2e6;display:flex;justify-content:space-between;">
                    <button type="button" class="btn btn-secondary" onclick="closeModalKembali()" style="border-radius:20px;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success" name="simpankembali" style="border-radius:20px;">
                        <i class="fas fa-check-circle"></i> Simpan Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
    `;
    
    document.getElementById('modalKembaliContainer').innerHTML = html;
    
    // Sync tanggal
    var tglDisplay = document.getElementById('tgl_kembali_display');
    if (tglDisplay) {
        tglDisplay.addEventListener('change', function() {
            document.getElementById('tgl_kembali_input').value = this.value;
        });
    }
}

function closeModalKembali() {
    document.getElementById('modalKembaliContainer').innerHTML = '';
}

// Tambah tombol export
function tambahTombolExport() {
    if (document.getElementById('btnExportExcel')) return;
    
    var filterContainer = document.querySelector('#example1_filter') || document.querySelector('.dataTables_filter');
    if (filterContainer) {
        var tombol = document.createElement('a');
        tombol.href = 'export_kas_excel.php';
        tombol.id = 'btnExportExcel';
        tombol.target = '_blank';
        tombol.className = 'btn-export';
        tombol.innerHTML = '<i class="fas fa-file-excel"></i> Export Excel';
        filterContainer.appendChild(tombol);
    }
}

// Jalankan setelah halaman selesai
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(tambahTombolExport, 500);
    });
} else {
    setTimeout(tambahTombolExport, 500);
}
</script>