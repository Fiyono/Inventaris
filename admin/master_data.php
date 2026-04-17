<?php
include "koneksi.php"; // pastikan path sesuai
?>
<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* ========== STYLE UMUM (TETAP SAMA UNTUK DESKTOP) ========== */
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

/* ========== RESPONSIVE MOBILE - TAMPILAN KARTU (TANPA SCROLL) ========== */
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
    
    /* Penyesuaian gambar */
    #example1 tbody td img {
        width: 50px !important;
        height: 50px !important;
        object-fit: cover;
    }
    
    /* Tombol edit */
    #example1 tbody td .btn.bg-cyan {
        padding: 5px 12px;
        font-size: 12px;
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
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="card-title mb-0" style="font-size:20px; font-weight:bold;">
                    <i class="fas fa-boxes"></i> DAFTAR BARANG INVENTARIS
                </div>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>ID BARANG</th>
                            <th>FOTO BARANG</th>
                            <th>NAMA</th>
                            <th>SPESIFIKASI</th>
                            <th>MERK</th>
                            <th>NO RAK</th>
                            <th>JUMLAH</th>
                            <th>EDIT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang ORDER BY id_brg DESC");
                        while ($row = mysqli_fetch_array($sql)) { 
                            $stok_warning = ($row['jumlah_brg'] <= 5) ? 'style="font-weight:bold;color:red;"' : '';
                        ?>
                        <tr>
                            <td data-label="NO"><?php echo $no++; ?></td>
                            <td data-label="ID BARANG"><?php echo htmlspecialchars($row['id_brg']); ?></td>
                            <td data-label="FOTO BARANG"><img src="dist/upload_img/<?php echo $row['gambar_brg']; ?>" width="80" height="80" class="rounded" style="object-fit: cover;" onerror="this.src='dist/upload_img/default.jpg'"></td>
                            <td data-label="NAMA"><?php echo htmlspecialchars($row['nama_brg']); ?></td>
                            <td data-label="SPESIFIKASI"><?php echo htmlspecialchars($row['spesifikasi_brg']); ?></td>
                            <td data-label="MERK"><?php echo htmlspecialchars($row['merk_brg']); ?></td>
                            <td data-label="NO RAK"><?php echo htmlspecialchars($row['norak_brg']); ?></td>
                            <td data-label="JUMLAH" <?php echo $stok_warning; ?>>
                                <?php echo $row['jumlah_brg']; ?> pcs
                            </td>
                            <td data-label="EDIT">
                                <a href="?page=editmaster_data&id=<?php echo $row['id_brg']; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn-edit">Edit</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // fungsi buat tombol excel di sebelah search
    function tambahTombolExcel() {
        if ($('#btnExportExcel').length) return; // jangan duplikat
        
        var tombol = $('<a>', {
            href: 'export_barang_excel.php',
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
    var observer = new MutationObserver(function() {
        if ($.fn.DataTable.isDataTable('#example1')) {
            tambahTombolExcel();
            observer.disconnect();
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>