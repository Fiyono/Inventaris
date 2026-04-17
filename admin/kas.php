<?php
// kas.php - Logika Pemrosesan Pengembalian Barang (FIX: Stok Ditambah Kembali)

// Pastikan file koneksi.php sudah tersedia
include "koneksi.php";

// proses pengembalian
if (isset($_POST['simpankembali'])) {
    // 1. Ambil input dengan sanitasi dan casting
    $id_pinjaman    = isset($_POST['id_pinjaman']) ? (int) $_POST['id_pinjaman'] : 0;
    // id_brg dan id_user akan digunakan dalam prepared statement, 
    // namun kita masih memerlukan variabel ini. mysqli_real_escape_string tidak lagi krusial 
    // untuk id_brg jika kita pakai prepared statement, tapi kita jaga tipenya string
    $id_brg         = isset($_POST['id_brg']) ? $_POST['id_brg'] : ''; 
    $id_user        = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
    $jumlah_kembali = isset($_POST['jumlah_brg']) ? (int) $_POST['jumlah_brg'] : 0;
    $tgl_kembali = isset($_POST['tgl_kembali']) ? $_POST['tgl_kembali'] : '';
    if ($tgl_kembali === '') {
        echo "<script>alert('Tanggal pengembalian wajib diisi.');history.back();</script>";
        exit;
    }
    // 2. Validasi Kritis
    if ($id_pinjaman <= 0 || $id_brg === '' || $id_user <= 0 || $jumlah_kembali <= 0) {
        echo "<script>alert('Data pengembalian tidak valid.');history.back();</script>";
        exit;
    }

    // 3. Mulai Transaksi untuk memastikan atomicity
    mysqli_begin_transaction($koneksi);

    try {
        // --- Q0: Ambil data pinjaman lama dengan Prepared Statement & LOCK FOR UPDATE ---
        $stmt_getData = $koneksi->prepare("SELECT * FROM tbl_pinjaman WHERE id_pinjaman=? FOR UPDATE");
        if (!$stmt_getData) {
            throw new Exception('Error prepare getData: ' . $koneksi->error);
        }
        $stmt_getData->bind_param("i", $id_pinjaman);
        $stmt_getData->execute();
        $getData = $stmt_getData->get_result();
        $dataPinjam = $getData->fetch_assoc();
        $stmt_getData->close();
        
        if (!$dataPinjam || (isset($dataPinjam['status']) && strtolower($dataPinjam['status']) === 'dikembalikan')) {
            $message = !$dataPinjam ? 'Data pinjaman tidak ditemukan.' : 'Pinjaman ini sudah dikembalikan sebelumnya.';
            throw new Exception($message);
        }
        
        // Cek pengembalian parsial (Jika tidak diizinkan, aktifkan validasi)
        if ($jumlah_kembali != (int)$dataPinjam['jumlah_pinjam']) {
             // Jika pengembalian parsial DILARANG, aktifkan baris ini:
             // throw new Exception('Pengembalian harus dalam jumlah penuh: ' . $dataPinjam['jumlah_pinjam'] . ' unit.');
        }

        // --- Q1: Update status pinjaman di tbl_pinjaman (Prepared Statement) ---
        $q1 = "UPDATE tbl_pinjaman 
       SET status='dikembalikan', tgl_kembali=? 
       WHERE id_pinjaman=?";
        $stmt1 = $koneksi->prepare($q1);
        $stmt1->bind_param("si", $tgl_kembali, $id_pinjaman);
        $ok1 = $stmt1->execute();
        $stmt1->close();
        if (!$ok1) {
            throw new Exception('Error update pinjaman: ' . $koneksi->error);
        }

        // --- Q2: KRUSIAL! Update stok barang (kembalikan jumlah) (Prepared Statement) ---
        $q2 = "UPDATE tbl_barang SET jumlah_brg = jumlah_brg + ? WHERE id_brg=?";
        $stmt2 = $koneksi->prepare($q2);
        if (!$stmt2) {
            throw new Exception('Error prepare stmt2: ' . $koneksi->error);
        }
        $stmt2->bind_param("is", $jumlah_kembali, $id_brg);
        $ok2 = $stmt2->execute();
        $stmt2->close();
        if (!$ok2) {
            throw new Exception('Error update barang: ' . $koneksi->error);
        }

        // --- Q3: Update/Insert History (Menggunakan Prepared Statement) ---
        
        // 3.1 Cek History (Prepared Statement)
        $q_cek = "SELECT id_histpinjam FROM tbl_history_pinjam WHERE id_pinjaman=? LIMIT 1 FOR UPDATE";
        $stmt_cek = $koneksi->prepare($q_cek);
        $stmt_cek->bind_param("i", $id_pinjaman);
        $stmt_cek->execute();
        $cekHistory = $stmt_cek->get_result(); 
        $stmt_cek->close();

        if (mysqli_num_rows($cekHistory) == 0) {
            // 3.2 Jika BELUM ada history, lakukan INSERT
            $jml_pinjam_original = (int) $dataPinjam['jumlah_pinjam'];
            $tujuan = $dataPinjam['tujuan_gunabarang'];
            $tgl_pinjam = $dataPinjam['tgl_pinjam'];

            $q3_insert = "INSERT INTO tbl_history_pinjam
            (id_pinjaman, id_user, id_brg, jumlahbrg_pinjam, jumlahbrg_kembali, tujuan_gunabarang, tgl_pinjam, tgl_kembali)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt3_insert = $koneksi->prepare($q3_insert);
            if (!$stmt3_insert) {
                throw new Exception('Error prepare insert history: ' . $koneksi->error);
            }
            $stmt3_insert->bind_param(
                "iiisisss", 
                $id_pinjaman, $id_user, $id_brg, 
                $jml_pinjam_original,
                $jumlah_kembali,
                $tujuan,
                $tgl_pinjam,
                $tgl_kembali
            );
            
            $ok3 = $stmt3_insert->execute();
            $stmt3_insert->close();
            if (!$ok3) {
                throw new Exception('Error execute insert history: ' . $koneksi->error);
            }
        } else {
            // 3.3 Jika SUDAH ada history, lakukan UPDATE
            $q3_update = "UPDATE tbl_history_pinjam
              SET jumlahbrg_kembali=?, tgl_kembali=?
              WHERE id_pinjaman=?";
            $stmt3_update = $koneksi->prepare($q3_update);
            $stmt3_update->bind_param("isi", $jumlah_kembali, $tgl_kembali, $id_pinjaman);

            $ok3 = $stmt3_update->execute();
            $stmt3_update->close();
            if (!$ok3) {
                throw new Exception('Error execute update history: ' . $koneksi->error);
            }
        }

        mysqli_commit($koneksi);
        echo "<script>
                  alert('Barang berhasil dikembalikan & stok bertambah.');
                  window.location.href='admin.php?page=kas';
              </script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal memproses pengembalian: " . $e->getMessage() . "');history.back();</script>";
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
    font-size: 14px;
    padding: 10px;
    font-weight: bold;
}

/* Isi tabel */
#example1 tbody td {
    font-size: 13px;
    text-align: center;
    vertical-align: middle;
    transition: all 0.3s ease;
    font-weight: normal;
    color: black;
    padding: 10px 8px;
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
    
    .modal-dialog.modal-sm {
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
                            <th>FOTO BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>ID BARANG</th>
                            <th>TANGGAL PINJAM</th>
                            <th>JUMLAH PINJAM</th>
                            <th>TUJUAN PENGGUNAAN</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $root_path = $_SERVER['DOCUMENT_ROOT'] . '/inventaris';
                        $no = 1;
                        $sql = mysqli_query($koneksi, "
                            SELECT 
                                x.id_pinjaman, x.id_brg, x.id_user, x.tgl_pinjam, x.jumlah_pinjam, x.tujuan_gunabarang,
                                y.nama_brg, y.gambar_brg,
                                u.nama_lengkap
                            FROM tbl_pinjaman x
                            INNER JOIN tbl_barang y ON y.id_brg = x.id_brg
                            INNER JOIN tb_user u ON u.id_user = x.id_user
                            WHERE x.status != 'dikembalikan'
                            ORDER BY x.id_pinjaman DESC
                        ");
                        if (!$sql) {
                            echo "<td><td colspan='9'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else {
                            while ($row = mysqli_fetch_array($sql)) { 
                                $gambar_path = $root_path . '/dist/upload_img/' . $row['gambar_brg'];
                                $gambar_web = '/inventaris/dist/upload_img/' . $row['gambar_brg'];
                            ?>
                            <tr>
                                <td data-label="NO"><?= $no++; ?></td>
                                <td data-label="NAMA PEMINJAM"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td data-label="FOTO BARANG">
                                    <?php if(!empty($row['gambar_brg']) && file_exists($gambar_path)): ?>
                                        <img src="<?= $gambar_web ?>" class="img-barang" alt="Foto Barang">
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="NAMA BARANG"><?= htmlspecialchars($row['nama_brg']); ?></td>
                                <td data-label="ID BARANG"><?= htmlspecialchars($row['id_brg']); ?></td>
                                <td data-label="TANGGAL PINJAM"><?= date('d-m-Y', strtotime($row['tgl_pinjam'])); ?></td>
                                <td data-label="JUMLAH PINJAM"><?= htmlspecialchars($row['jumlah_pinjam']); ?> pcs</td>
                                <td data-label="TUJUAN"><?= htmlspecialchars($row['tujuan_gunabarang']); ?></td>
                                <td data-label="AKSI">
                                    <a href="#" class="btn-kembali" data-toggle="modal" data-target="#modal-success<?= $row['id_pinjaman']; ?>">
                                        <i class="fas fa-undo-alt"></i> Kembalikan
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade modal-success" id="modal-success<?= $row['id_pinjaman']; ?>">
                                <div class="modal-dialog modal-sm">
                                    <form action="" method="post">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header" style="background: linear-gradient(45deg, #28a745, #00c851); color: white;">
                                                <h5 class="modal-title font-weight-bold">
                                                    <i class="fas fa-check-circle"></i> Konfirmasi Pengembalian
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true" style="color: white;">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center mb-3">
                                                    <?php if(!empty($row['gambar_brg']) && file_exists($gambar_path)): ?>
                                                        <img src="<?= $gambar_web ?>" class="img-barang" alt="Foto Barang" style="width: 80px; height: 80px;">
                                                    <?php else: ?>
                                                        <div style="width: 80px; height: 80px; background: #f0f0f0; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image fa-2x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <p><strong>Barang:</strong> <?= htmlspecialchars($row['nama_brg']); ?></p>
                                                <p class="mb-3"><strong>Dipinjam oleh:</strong> <?= htmlspecialchars($row['nama_lengkap']); ?></p>

                                                <input type="hidden" name="id_pinjaman" value="<?= $row['id_pinjaman'] ?>">
                                                <input type="hidden" name="id_brg" value="<?= $row['id_brg']; ?>">
                                                <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
                                                
                                                <div class="form-group">
                                                    <label for="jumlah_brg_<?= $row['id_pinjaman']; ?>">
                                                        <i class="fas fa-boxes"></i> Jumlah yang Dikembalikan
                                                    </label>
                                                    <input type="number" name="jumlah_brg" class="form-control" 
                                                            min="1" 
                                                            max="<?= $row['jumlah_pinjam']; ?>" 
                                                            value="<?= $row['jumlah_pinjam']; ?>" 
                                                            required
                                                            id="jumlah_brg_<?= $row['id_pinjaman']; ?>">
                                                    <small class="text-muted">Maksimal: <?= $row['jumlah_pinjam']; ?> pcs</small>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="tgl_kembali_<?= $row['id_pinjaman']; ?>">
                                                        <i class="fas fa-calendar-alt"></i> Tanggal Pengembalian
                                                    </label>
                                                    <input type="date" 
                                                        name="tgl_kembali"
                                                        class="form-control"
                                                        id="tgl_kembali_<?= $row['id_pinjaman']; ?>"
                                                        value="<?= date('Y-m-d'); ?>"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                                    <i class="fas fa-times"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-success" name="simpankembali">
                                                    <i class="fas fa-check-circle"></i> Simpan Pengembalian
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php }
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- JS - HANYA TAMBAH TOMBOL EXPORT, TIDAK INISIALISASI ULANG DATATABLE -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Fungsi tambah tombol export Excel
    function tambahTombolExport() {
        if ($('#btnExportExcel').length) return;
        
        const tombol = $('<a>', {
            href: 'export_kas_excel.php',
            id: 'btnExportExcel',
            target: '_blank',
            class: 'btn-export',
            html: '<i class="fas fa-file-excel"></i> Export Excel'
        });
        
        // Cari container filter
        let filterContainer = $('#example1_filter');
        if (!filterContainer.length) {
            filterContainer = $('.dataTables_filter');
        }
        
        if (filterContainer.length) {
            filterContainer.append(tombol);
        } else {
            // Tunggu sampai DataTable selesai loading
            setTimeout(function() {
                let filterContainer2 = $('#example1_filter');
                if (!filterContainer2.length) {
                    filterContainer2 = $('.dataTables_filter');
                }
                if (filterContainer2.length && !$('#btnExportExcel').length) {
                    filterContainer2.append(tombol);
                }
            }, 500);
        }
    }
    
    // Cek apakah DataTable sudah ada (dari admin.php)
    // JANGAN inisialisasi ulang! Cukup tambah tombol export saja.
    if ($('#example1').length) {
        // Tunggu sebentar agar DataTable dari admin.php selesai loading
        setTimeout(function() {
            tambahTombolExport();
        }, 300);
    }
});
</script>