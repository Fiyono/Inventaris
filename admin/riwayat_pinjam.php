<?php
include "koneksi.php";

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id_histpinjam = $_GET['hapus'];
    
    // Ambil data pinjaman sebelum dihapus untuk mengembalikan stok
    $query_data = mysqli_query($koneksi, "SELECT id_brg, jumlahbrg_pinjam FROM tbl_history_pinjam WHERE id_histpinjam = '$id_histpinjam'");
    $data_pinjam = mysqli_fetch_assoc($query_data);
    
    if ($data_pinjam) {
        $id_brg = $data_pinjam['id_brg'];
        $jumlah_pinjam = $data_pinjam['jumlahbrg_pinjam'];
        
        // Kembalikan stok barang
        mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg + $jumlah_pinjam WHERE id_brg = '$id_brg'");
    }
    
    $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_history_pinjam WHERE id_histpinjam = '$id_histpinjam'");
    if ($query_hapus) {
        echo "<script>alert('Data berhasil dihapus! Stok barang dikembalikan.'); window.location.href='?page=riwayat_pinjam';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data!');</script>";
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

/* Badge status */
.badge-pinjam {
    background-color: #17a2b8;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

.badge-kembali {
    background-color: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

.badge-belum {
    background-color: #dc3545;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
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

/* Tombol Edit & Hapus */
.btn-edit {
    background: linear-gradient(45deg, #ffc107, #ff9800);
    color: #fff;
    padding: 5px 12px;
    border-radius: 6px;
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

.btn-hapus {
    background: linear-gradient(45deg, #dc3545, #c82333);
    color: #fff;
    padding: 5px 12px;
    border-radius: 6px;
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
    .card-header .card-title,
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
    }
    
    /* Pagination lebih kecil */
    .dataTables_paginate .paginate_button {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }
    
    .dataTables_info {
        font-size: 11px;
    }
}

/* Tablet */
@media screen and (min-width: 769px) and (max-width: 1024px) {
    #example1 {
        font-size: 13px;
    }
    
    #example1 thead th {
        font-size: 12px;
        padding: 8px 5px;
    }
    
    #example1 tbody td {
        font-size: 11px;
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
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="mb-0" style="font-size:20px;">
                    <i class="fas fa-sign-out-alt"></i> RIWAYAT PINJAM BARANG
                </div>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-sm table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA</th>
                            <th>ID BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>TYPE</th>
                            <th>MERK</th>
                            <th>JUMLAH PINJAM</th>
                            <th>JUMLAH KEMBALI</th>
                            <th>TUJUAN</th>
                            <th>TANGGAL PINJAM</th>
                            <th>TANGGAL KEMBALI</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $sql = mysqli_query($koneksi, "
                            SELECT p.*, 
                                u.nama_lengkap, 
                                b.nama_brg, 
                                b.spesifikasi_brg, 
                                b.merk_brg,
                                b.id_brg as id_barang
                            FROM tbl_history_pinjam p
                            JOIN tb_user u ON p.id_user = u.id_user
                            JOIN tbl_barang b ON p.id_brg = b.id_brg
                            ORDER BY p.id_histpinjam DESC
                        ");
                        while ($row = mysqli_fetch_assoc($sql)) {
                            $tgl_kembali = ($row['tgl_kembali'] == "0000-00-00" || empty($row['tgl_kembali'])) 
                                ? '<span class="badge-belum"><i class="fas fa-clock"></i> Belum Kembali</span>' 
                                : date('d-m-Y', strtotime($row['tgl_kembali']));
                        ?>
                            <tr>
                                <td data-label="NO"><?= $no++; ?></td>
                                <td data-label="NAMA"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td data-label="ID BARANG"><?= htmlspecialchars($row['id_barang']); ?></td>
                                <td data-label="NAMA BARANG"><?= htmlspecialchars($row['nama_brg']); ?></td>
                                <td data-label="TYPE"><?= htmlspecialchars($row['spesifikasi_brg']); ?></td>
                                <td data-label="MERK"><?= htmlspecialchars($row['merk_brg']); ?></td>
                                <td data-label="JUMLAH PINJAM"><?= $row['jumlahbrg_pinjam']; ?> pcs</td>
                                <td data-label="JUMLAH KEMBALI"><?= $row['jumlahbrg_kembali'] ?? '0'; ?> pcs</td>
                                <td data-label="TUJUAN"><?= htmlspecialchars($row['tujuan_gunabarang'] ?? '-'); ?></td>
                                <td data-label="TANGGAL PINJAM">
                                    <?= !empty($row['tgl_pinjam']) && $row['tgl_pinjam'] != "0000-00-00"
                                        ? date('d-m-Y', strtotime($row['tgl_pinjam']))
                                        : '-' ?>
                                </td>
                                <td data-label="TANGGAL KEMBALI"><?= $tgl_kembali; ?></td>
                                <td data-label="AKSI">
                                    <a href="?page=edit_riwayat_pinjam&id=<?= $row['id_histpinjam']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn-hapus" onclick="confirmDelete(<?= $row['id_histpinjam']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
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
            <p>Apakah Anda yakin ingin menghapus data pinjaman ini?<br>Stok barang akan dikembalikan.</p>
            <div class="modal-buttons">
                <button class="btn-confirm-yes" onclick="deleteData()">Ya, Hapus</button>
                <button class="btn-confirm-no" onclick="closeModal()">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
let deleteId = null;

// Fungsi konfirmasi hapus
function confirmDelete(id) {
    deleteId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

// Fungsi hapus data
function deleteData() {
    if (deleteId) {
        window.location.href = '?page=riwayat_pinjam&hapus=' + deleteId;
    }
}

// Fungsi tutup modal
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
}

// Tutup modal jika klik di luar
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeModal();
    }
});

$(document).ready(function() {
    // fungsi buat tombol excel di sebelah search
    function tambahTombolExcel() {
        if ($('#btnExportExcel').length) return; // jangan duplikat
        
        const tombol = $('<a>', {
            href: 'export_riwayat_pinjam_excel.php',
            id: 'btnExportExcel',
            target: '_blank',
            class: 'btn-export',
            html: '<i class="fas fa-file-excel"></i> Export Excel'
        });
        
        $('#example1_filter').append(tombol);
    }

    // 🔹 1. Jika DataTable sudah aktif (dari template admin.php)
    if ($.fn.DataTable.isDataTable('#example1')) {
        tambahTombolExcel();
        return;
    }

    // 🔹 2. Jika DataTable belum aktif, tunggu sampai aktif
    $('#example1').on('init.dt', function() {
        tambahTombolExcel();
    });

    // 🔹 3. Fallback jika template aktifkan lewat delay
    const observer = new MutationObserver(function() {
        if ($.fn.DataTable.isDataTable('#example1')) {
            tambahTombolExcel();
            observer.disconnect();
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>