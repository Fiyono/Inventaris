<?php
// =============================
// CEK SESSION SEBELUM START
// =============================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =============================
// KONEKSI DATABASE
// =============================
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/inventaris';
include $root_path . '/koneksi.php';

// ===================
// PROSES EDIT DATA
// ===================
if (isset($_POST['simpan_edit'])) {
    $id_ambil = (int)$_POST['id_ambil'];
    $id_brg = (int)$_POST['id_brg'];
    $jumlah_lama = (int)$_POST['jumlah_lama'];
    $jumlah_baru = (int)$_POST['jumlah_baru'];
    $tujuan_gunabarang = mysqli_real_escape_string($koneksi, $_POST['tujuan_gunabarang']);
    $alamat_ruang = mysqli_real_escape_string($koneksi, $_POST['alamat_ruang']);
    
    // Hitung selisih jumlah
    $selisih = $jumlah_lama - $jumlah_baru;
    
    if ($selisih > 0) {
        // Jika jumlah dikurangi, stok bertambah
        $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg + $selisih WHERE id_brg = '$id_brg'");
    } elseif ($selisih < 0) {
        // Jika jumlah ditambah, cek stok dulu
        $selisih_abs = abs($selisih);
        $cek_stok = mysqli_query($koneksi, "SELECT jumlah_brg FROM tbl_barang WHERE id_brg = '$id_brg'");
        $stok = mysqli_fetch_assoc($cek_stok);
        
        if ($stok['jumlah_brg'] < $selisih_abs) {
            echo "<script>alert('Stok tidak mencukupi! Stok tersedia: {$stok['jumlah_brg']} pcs'); window.history.back();</script>";
            exit;
        }
        
        $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg - $selisih_abs WHERE id_brg = '$id_brg'");
    } else {
        $update_stok = true;
    }
    
    if ($update_stok) {
        $query_update = mysqli_query($koneksi, "
            UPDATE tbl_ambil 
            SET jumlah_brg = '$jumlah_baru', 
                tujuan_gunabarang = '$tujuan_gunabarang', 
                alamat_ruang = '$alamat_ruang' 
            WHERE id_ambil = '$id_ambil'
        ");
        
        if ($query_update) {
            echo "<script>alert('Data berhasil diupdate!'); window.location.href=window.location.href;</script>";
        } else {
            echo "<script>alert('Gagal mengupdate data!');</script>";
        }
    }
}

// ===================
// PROSES HAPUS DATA (LANGSUNG HAPUS + KEMBALIKAN STOK)
// ===================
if (isset($_GET['hapus'])) {
    $id_ambil = (int)$_GET['hapus'];
    
    // Ambil data sebelum dihapus
    $query_data = mysqli_query($koneksi, "SELECT id_brg, jumlah_brg FROM tbl_ambil WHERE id_ambil = '$id_ambil'");
    $data = mysqli_fetch_assoc($query_data);
    
    if ($data) {
        $id_brg = $data['id_brg'];
        $jumlah_ambil = $data['jumlah_brg'];
        
        // Kembalikan stok barang (tambah stok)
        $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg + $jumlah_ambil WHERE id_brg = '$id_brg'");
        
        if ($update_stok) {
            // Hapus data dari tbl_ambil
            $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_ambil WHERE id_ambil = '$id_ambil'");
            
            if ($query_hapus) {
                echo "<script>alert('Data berhasil dihapus! Stok barang bertambah $jumlah_ambil pcs.'); window.location.href='?page=set_members';</script>";
            } else {
                echo "<script>alert('Gagal menghapus data!');</script>";
            }
        } else {
            echo "<script>alert('Gagal mengembalikan stok barang!');</script>";
        }
    }
}
?>

<link rel="stylesheet" href="<?= $root_path ?>/assets/css/custom.css">
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

/* Tombol Edit */
.btn-edit {
    background: linear-gradient(45deg, #ffc107, #ff9800);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    text-decoration: none;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-right: 5px;
    border: none;
    cursor: pointer;
}

.btn-edit:hover {
    background: linear-gradient(45deg, #e0a800, #e68900);
    color: white;
    transform: translateY(-1px);
}

/* Tombol Hapus */
.btn-hapus {
    background: linear-gradient(45deg, #dc3545, #c82333);
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

.btn-hapus:hover {
    background: linear-gradient(45deg, #c82333, #bd2130);
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

/* Foto di tabel */
.img-barang {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal */
.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

/* Modal Hapus */
.modal-confirm {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content-confirm {
    background: white;
    border-radius: 12px;
    padding: 25px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal-content-confirm h4 {
    margin-bottom: 15px;
    color: #dc3545;
}

.modal-content-confirm p {
    margin-bottom: 20px;
    color: #555;
}

.modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-confirm-yes {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-confirm-no {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    cursor: pointer;
}

/* ========== RESPONSIVE MOBILE - TAMPILAN KARTU ========== */
@media screen and (max-width: 768px) {
    
    /* Sembunyikan header tabel di mobile */
    #example1 thead {
        display: none;
    }
    
    /* Setiap baris menjadi kartu */
    #example1 tbody tr {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 15px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 10px;
    }
    
    /* Setiap sel menjadi baris horizontal */
    #example1 tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        font-size: 12px;
    }
    
    /* Hapus border-bottom untuk sel terakhir */
    #example1 tbody td:last-child {
        border-bottom: none;
    }
    
    /* Label untuk setiap kolom */
    #example1 tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #007bff;
        width: 40%;
        font-size: 11px;
    }
    
    /* Tombol aksi di mobile */
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
    
    .btn-edit, .btn-hapus {
        padding: 5px 10px;
        font-size: 11px;
    }
    
    /* Judul card lebih kecil */
    .card-header .mb-0 {
        font-size: 16px !important;
    }
    
    /* Search dan export full width */
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
    
    /* Pagination lebih kecil */
    .dataTables_paginate .paginate_button {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }
    
    .dataTables_info {
        font-size: 11px;
    }
    
    /* Modal di mobile */
    .modal-dialog {
        margin: 10px;
        width: calc(100% - 20px);
        max-width: none;
    }
    
    .modal-body .form-group {
        margin-bottom: 12px;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 8px;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin: 0;
    }
    
    /* Foto di mobile */
    .img-barang {
        width: 40px;
        height: 40px;
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
}

/* Desktop tetap tampil normal */
@media screen and (min-width: 769px) {
    #example1 {
        width: 100% !important;
    }
}
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header" style="background: linear-gradient(45deg, #007bff, #00c6ff); color: white;">
                <div class="card-title mb-0" style="font-size:20px; font-weight:bold;">
                    <i class="fas fa-sign-out-alt"></i> DAFTAR BARANG DIAMBIL
                </div>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-sm table-striped table-hover table-bordered table-valign-middle">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA</th>
                            <th>ID BARANG</th>
                            <th>FOTO BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>JUMLAH DIAMBIL</th>
                            <th>SISA BARANG</th>
                            <th>TUJUAN PENGGUNAAN</th>
                            <th>ALAMAT RUANG</th>
                            <th>TANGGAL PENGAMBILAN</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; // Inisialisasi nomor urut
                        
                        // QUERY YANG DIPERBAIKI - MENAMBAHKAN JOIN DENGAN tb_user
                        $sql = mysqli_query($koneksi, "
                            SELECT 
                                a.id_ambil, 
                                a.id_brg, 
                                a.id_user,
                                a.tgl_brg_keluar, 
                                a.jumlah_brg as diambil,
                                a.tujuan_gunabarang, 
                                a.alamat_ruang,
                                b.gambar_brg, 
                                b.barcode_brg, 
                                b.nama_brg, 
                                b.jumlah_brg as sisa,
                                u.nama_lengkap
                            FROM tbl_ambil a
                            INNER JOIN tbl_barang b ON a.id_brg = b.id_brg
                            INNER JOIN tb_user u ON a.id_user = u.id_user
                            ORDER BY a.id_ambil DESC
                        ");
                        
                        // Cek apakah query berhasil
                        if (!$sql) {
                            echo "<tr><td colspan='11'>Error Query: " . mysqli_error($koneksi) . "</td></tr>";
                        }
                        
                        while ($row = mysqli_fetch_array($sql)) { 
                            $gambar_path = $root_path . '/dist/upload_img/' . $row['gambar_brg'];
                            $gambar_web = '/inventaris/dist/upload_img/' . $row['gambar_brg'];
                        ?>
                            <tr>
                                <td data-label="NO"><?= $no++; ?></td>
                                <td data-label="NAMA">
                                    <?= htmlspecialchars($row['nama_lengkap'] ?? '-'); ?>
                                </td>
                                <td data-label="ID BARANG">
                                    <?= htmlspecialchars($row['id_brg']); ?>
                                </td>
                                <td data-label="FOTO BARANG">
                                    <?php if(!empty($row['gambar_brg']) && file_exists($gambar_path)): ?>
                                        <img src="<?= $gambar_web ?>" class="img-barang" alt="Foto Barang">
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="NAMA BARANG">
                                    <?= htmlspecialchars($row['nama_brg']); ?>
                                </td>
                                <td data-label="JUMLAH DIAMBIL">
                                    <?= $row['diambil']; ?> pcs
                                </td>
                                <td data-label="SISA BARANG">
                                    <?= $row['sisa']; ?> pcs
                                </td>
                                <td data-label="TUJUAN PENGGUNAAN">
                                    <?= htmlspecialchars($row['tujuan_gunabarang'] ?? '-'); ?>
                                </td>
                                <td data-label="ALAMAT RUANG">
                                    <?= htmlspecialchars($row['alamat_ruang'] ?? '-'); ?>
                                </td>
                                <td data-label="TANGGAL PENGAMBILAN">
                                    <?= date('d-m-Y', strtotime($row['tgl_brg_keluar'])); ?>
                                </td>
                                <td data-label="AKSI">
                                    <button type="button" class="btn-edit" data-toggle="modal" data-target="#modal-edit-<?= $row['id_ambil'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn-hapus" onclick="confirmDelete(<?= $row['id_ambil']; ?>, <?= $row['diambil']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL EDIT -->
                            <div class="modal fade" id="modal-edit-<?= $row['id_ambil'] ?>">
                                <div class="modal-dialog">
                                    <form action="" method="post">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header" style="background: linear-gradient(45deg, #ffc107, #ff9800); color: white;">
                                                <h4 class="modal-title">
                                                    <i class="fas fa-edit"></i> Edit Data Pengambilan
                                                </h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true" style="color: white;">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Pengambil:</strong> <?= htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></p>
                                                <p><strong>Barang:</strong> <?= htmlspecialchars($row['nama_brg']); ?></p>
                                                
                                                <input type="hidden" name="id_ambil" value="<?= $row['id_ambil'] ?>">
                                                <input type="hidden" name="id_brg" value="<?= $row['id_brg'] ?>">
                                                <input type="hidden" name="jumlah_lama" value="<?= $row['diambil'] ?>">
                                                
                                                <div class="form-group">
                                                    <label for="jumlah_baru_<?= $row['id_ambil']; ?>">
                                                        <i class="fas fa-boxes"></i> Jumlah Barang Diambil
                                                    </label>
                                                    <input type="number" 
                                                           name="jumlah_baru" 
                                                           class="form-control" 
                                                           id="jumlah_baru_<?= $row['id_ambil']; ?>"
                                                           value="<?= $row['diambil'] ?>" 
                                                           min="1" 
                                                           required>
                                                    <small class="text-muted">Stok tersedia: <?= $row['sisa'] + $row['diambil']; ?> pcs</small>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="tujuan_<?= $row['id_ambil']; ?>">
                                                        <i class="fas fa-bullseye"></i> Tujuan Penggunaan
                                                    </label>
                                                    <input type="text" 
                                                           name="tujuan_gunabarang" 
                                                           class="form-control" 
                                                           id="tujuan_<?= $row['id_ambil']; ?>"
                                                           value="<?= htmlspecialchars($row['tujuan_gunabarang'] ?? ''); ?>" 
                                                           required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="alamat_<?= $row['id_ambil']; ?>">
                                                        <i class="fas fa-map-marker-alt"></i> Alamat Ruang
                                                    </label>
                                                    <input type="text" 
                                                           name="alamat_ruang" 
                                                           class="form-control" 
                                                           id="alamat_<?= $row['id_ambil']; ?>"
                                                           value="<?= htmlspecialchars($row['alamat_ruang'] ?? ''); ?>" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                                    <i class="fas fa-times"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-warning" name="simpan_edit" style="color: white;">
                                                    <i class="fas fa-save"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" style="display: none;">
    <div class="modal-confirm">
        <div class="modal-content-confirm">
            <h4><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h4>
            <p id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="modal-buttons">
                <button class="btn-confirm-yes" onclick="deleteData()">Ya, Hapus</button>
                <button class="btn-confirm-no" onclick="closeModal()">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let deleteId = null;
let deleteJumlah = null;

// Fungsi konfirmasi hapus dengan informasi jumlah
function confirmDelete(id, jumlah) {
    deleteId = id;
    deleteJumlah = jumlah;
    document.getElementById('deleteMessage').innerHTML = `Apakah Anda yakin ingin menghapus data ini?<br><strong>Stok barang akan bertambah ${jumlah} pcs.</strong>`;
    document.getElementById('deleteModal').style.display = 'flex';
}

// Fungsi hapus data
function deleteData() {
    if (deleteId) {
        window.location.href = '?page=set_members&hapus=' + deleteId;
    }
}

// Fungsi tutup modal
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
    deleteJumlah = null;
}

// Tutup modal jika klik di luar
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeModal();
    }
});

$(document).ready(function() {
    // Fungsi tambah tombol export Excel
    function tambahTombolExport() {
        if ($('#btnExportExcel').length) return;
        
        const tombol = $('<a>', {
            href: 'export_set_members_excel.php',
            id: 'btnExportExcel',
            target: '_blank',
            class: 'btn-export',
            html: '<i class="fas fa-file-excel"></i> Export Excel'
        });
        
        let filterContainer = $('#example1_filter');
        if (!filterContainer.length) {
            filterContainer = $('.dataTables_filter');
        }
        
        if (filterContainer.length) {
            filterContainer.append(tombol);
        } else {
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
    
    if ($('#example1').length) {
        setTimeout(function() {
            tambahTombolExport();
        }, 300);
    }
});
</script>