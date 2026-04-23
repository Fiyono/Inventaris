<?php
// prioritas_stok.php - Halaman Prioritas Stok Opname Barang
include "koneksi.php";

// Ambil parameter filter kategori (opsional)
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Jika tidak ada filter, cari ID kategori "MEMORI"
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

/*
Kategori default saat tidak memilih filter manual
Tambah kategori baru tinggal tambahkan di array ini
*/
$kategori_default = ['CABLE', 'MEMORI', 'MAINBOARD'];

$id_kategori_list = [];

if ($filter_kategori == 0) {
    foreach ($kategori_default as $nama) {
        $nama = mysqli_real_escape_string($koneksi, strtoupper($nama));

        $q = mysqli_query($koneksi, "
            SELECT id_kategori 
            FROM tbl_kategori
            WHERE UPPER(nama_kategori) LIKE '%$nama%'
        ");

        while ($r = mysqli_fetch_assoc($q)) {
            $id_kategori_list[] = $r['id_kategori'];
        }
    }

    $id_kategori_list = array_unique($id_kategori_list);
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

/* Tombol Filter */
.btn-filter {
    background: linear-gradient(45deg, #17a2b8, #138496);
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

.btn-filter:hover {
    background: linear-gradient(45deg, #138496, #0f6674);
    color: white;
    transform: translateY(-1px);
}

.btn-reset {
    background: linear-gradient(45deg, #6c757d, #5a6268);
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

.btn-reset:hover {
    background: linear-gradient(45deg, #5a6268, #4e555b);
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

/* Filter group */
.filter-group {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
}

.filter-group label {
    margin: 0;
    font-weight: 600;
    color: #495057;
}

.filter-group select {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    min-width: 200px;
}

/* Ringkasan */
.summary-card {
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 20px;
    color: white;
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.summary-card.active {
    box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
    transform: scale(1.02);
}

.summary-card h4 {
    margin: 0;
    font-size: 28px;
    font-weight: bold;
}

.summary-card p {
    margin: 5px 0 0;
    opacity: 0.9;
    font-size: 13px;
}

/* Stok badge */
.stok-habis {
    background-color: #6c757d;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

.stok-kritis {
    background-color: #dc3545;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

.stok-menipis {
    background-color: #ffc107;
    color: #333;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    display: inline-block;
}

/* Foto barang */
.img-barang {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Badge filter aktif */
.badge-filter {
    background-color: #17a2b8;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    margin-left: 10px;
}

/* ========== RESPONSIVE MOBILE ========== */
@media only screen and (max-width: 768px) {
    /* Sembunyikan header tabel */
    #example1 thead {
        display: none;
    }
    
    /* Setiap baris menjadi card */
    #example1 tbody tr {
        display: block;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 15px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 12px;
    }
    
    /* Setiap kolom menjadi flex row */
    #example1 tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 10px 8px;
        border-bottom: 1px solid #e9ecef;
        font-size: 12px;
        gap: 10px;
    }
    
    /* Hapus border bottom untuk kolom terakhir */
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
        flex-shrink: 0;
    }
    
    /* Tombol di mobile */
    .btn-edit, .btn-filter, .btn-reset {
        padding: 6px 12px;
        font-size: 11px;
        justify-content: center;
    }
    
    /* Header card */
    .card-header .card-title,
    .card-header .mb-0 {
        font-size: 16px !important;
    }
    
    /* Search box */
    .dataTables_filter {
        flex-direction: column;
        align-items: stretch;
        margin-bottom: 15px;
    }
    
    .dataTables_filter label {
        width: 100%;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .dataTables_filter input {
        flex: 1;
        margin-left: 10px !important;
        height: 36px;
        font-size: 14px;
    }
    
    /* Tombol export */
    .btn-export {
        justify-content: center;
        width: 100%;
        margin-left: 0;
        margin-top: 5px;
        padding: 8px;
    }
    
    /* Filter group */
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group select,
    .filter-group .btn-filter,
    .filter-group .btn-reset {
        width: 100%;
        text-align: center;
        justify-content: center;
    }
    
    /* Pagination */
    .dataTables_paginate {
        margin-top: 15px;
        text-align: center;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 6px 10px !important;
        font-size: 11px !important;
        margin: 2px !important;
    }
    
    .dataTables_info {
        font-size: 11px;
        text-align: center;
        margin-bottom: 10px;
    }
}

/* Tablet (769px - 1024px) */
@media only screen and (min-width: 769px) and (max-width: 1024px) {
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
    
    .btn-edit, .btn-filter, .btn-reset {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .dataTables_filter input {
        height: 30px;
        font-size: 12px;
    }
}

/* Layar sangat kecil (max-width: 480px) */
@media only screen and (max-width: 480px) {
    .card-body {
        padding: 10px;
    }
    
    #example1 tbody tr {
        padding: 8px;
    }
    
    #example1 tbody td {
        flex-wrap: wrap;
        padding: 8px 5px;
    }
    
    #example1 tbody td:before {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .btn-edit, .btn-filter, .btn-reset {
        width: 100%;
        margin: 2px 0;
    }
}

/* Desktop tetap tampil normal (min-width: 1025px) */
@media only screen and (min-width: 1025px) {
    #example1 {
        width: 100% !important;
    }
    
    /* Pastikan tabel normal di desktop */
    #example1 thead {
        display: table-header-group;
    }
    
    #example1 tbody tr {
        display: table-row;
        border: none;
        margin-bottom: 0;
        padding: 0;
        box-shadow: none;
    }
    
    #example1 tbody td {
        display: table-cell;
        border-bottom: 1px solid #dee2e6;
        text-align: center;
    }
    
    #example1 tbody td:before {
        display: none;
    }
    
    .btn-edit, .btn-filter, .btn-reset {
        width: auto;
    }
}

/* Style untuk print */
@media print {
    .btn-print, .btn-export, .dataTables_filter, .dataTables_paginate {
        display: none;
    }
    
    #example1 thead {
        display: table-header-group;
    }
    
    #example1 tbody td:before {
        display: none;
    }
}
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div class="mb-0" style="font-size:20px;">
                    <i class="fas fa-exclamation-triangle"></i> PRIORITAS STOK OPNAME
                    <?php if ($filter_status != ''): ?>
                        <span class="badge-filter">
                            <i class="fas fa-filter"></i> 
                            <?php 
                            if ($filter_status == 'habis') echo "Stok Habis";
                            elseif ($filter_status == 'kritis') echo "Stok Kritis";
                            elseif ($filter_status == 'menipis') echo "Stok Menipis";
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Kategori -->
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filter Kategori:</label>
                    <select id="filterKategori" class="form-control">
                        <option value="0">-- SEMUA KATEGORI --</option>
                        <?php
                        $kategori_query = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY nama_kategori");
                        while ($kat = mysqli_fetch_array($kategori_query)) {
                            $selected = ($filter_kategori == $kat['id_kategori']) ? 'selected' : '';
                            echo "<option value='{$kat['id_kategori']}' $selected>" . htmlspecialchars($kat['nama_kategori']) . "</option>";
                        }
                        ?>
                    </select>
                    <button type="button" class="btn-filter" onclick="applyFilter()">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                    <a href="?page=prioritas_stok" class="btn-reset">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>

                <?php
                // Query untuk barang dengan stok <= 5
                $sql = "SELECT 
                            b.id_brg,
                            b.nama_brg,
                            b.spesifikasi_brg,
                            b.merk_brg,
                            b.jumlah_brg,
                            b.gambar_brg,
                            b.norak_brg,
                            k.nama_kategori,
                            k.id_kategori
                        FROM tbl_barang b
                        LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
                        WHERE b.jumlah_brg <= 5";
                
                // Filter berdasarkan status
                if ($filter_status == 'habis') {
                    $sql .= " AND b.jumlah_brg = 0";
                } elseif ($filter_status == 'kritis') {
                    $sql .= " AND b.jumlah_brg BETWEEN 1 AND 2";
                } elseif ($filter_status == 'menipis') {
                    $sql .= " AND b.jumlah_brg BETWEEN 3 AND 5";
                }
                
                if ($filter_kategori > 0) {
                    $sql .= " AND b.id_kategori = '$filter_kategori'";
                } elseif (!empty($id_kategori_list)) {
                    $ids = implode(',', $id_kategori_list);
                    $sql .= " AND b.id_kategori IN ($ids)";
                }
                
                $sql .= " ORDER BY b.jumlah_brg ASC, b.nama_brg ASC";
                $result = mysqli_query($koneksi, $sql);
                
                $total_barang = mysqli_num_rows($result);
                $total_stok_habis = 0;
                $total_stok_kritis = 0;
                $total_stok_menipis = 0;
                
                // Hitung statistik (query terpisah untuk semua data tanpa filter status)
                $sql_stat = "SELECT jumlah_brg FROM tbl_barang b WHERE b.jumlah_brg <= 5";
                if ($filter_kategori > 0) {
                    $sql_stat .= " AND b.id_kategori = '$filter_kategori'";
                } elseif (!empty($id_kategori_list)) {
                    $ids = implode(',', $id_kategori_list);
                    $sql_stat .= " AND b.id_kategori IN ($ids)";
                }
                $stat_result = mysqli_query($koneksi, $sql_stat);
                while ($row = mysqli_fetch_array($stat_result)) {
                    if ($row['jumlah_brg'] == 0) {
                        $total_stok_habis++;
                    } elseif ($row['jumlah_brg'] <= 2) {
                        $total_stok_kritis++;
                    } else {
                        $total_stok_menipis++;
                    }
                }
                ?>
                
                <!-- Ringkasan (Card Klikable) -->
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-card" style="background: linear-gradient(135deg, #667eea, #764ba2);" onclick="filterStatus('all')">
                            <h4><?= $total_barang; ?></h4>
                            <p>Total Stok ≤ 5</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-card" style="background: linear-gradient(135deg, #6c757d, #5a6268);" onclick="filterStatus('habis')">
                            <h4><?= $total_stok_habis; ?></h4>
                            <p><i class="fas fa-times-circle"></i> Stok Habis (0)</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-card" style="background: linear-gradient(135deg, #dc3545, #c82333);" onclick="filterStatus('kritis')">
                            <h4><?= $total_stok_kritis; ?></h4>
                            <p><i class="fas fa-skull-crosswalk"></i> Stok Kritis (1-2)</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="summary-card" style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #333;" onclick="filterStatus('menipis')">
                            <h4><?= $total_stok_menipis; ?></h4>
                            <p><i class="fas fa-exclamation-triangle"></i> Stok Menipis (3-5)</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="example1" class="table table-sm table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>ID BARANG</th>
                                <th>FOTO</th>
                                <th>NAMA BARANG</th>
                                <th>SPESIFIKASI</th>
                                <th>MERK</th>
                                <th>KATEGORI</th>
                                <th>NO RAK</th>
                                <th>STOK</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_array($result)) { 
                                $stok = $row['jumlah_brg'];
                                if ($stok == 0) {
                                    $status = '<span class="stok-habis"><i class="fas fa-times-circle"></i> Stok Habis</span>';
                                    $stok_color = '#6c757d';
                                } elseif ($stok <= 2) {
                                    $status = '<span class="stok-kritis"><i class="fas fa-skull-crosswalk"></i> Stok Kritis</span>';
                                    $stok_color = '#dc3545';
                                } else {
                                    $status = '<span class="stok-menipis"><i class="fas fa-exclamation-triangle"></i> Stok Menipis</span>';
                                    $stok_color = '#ffc107';
                                }
                            ?>
                            <tr>
                                <td data-label="NO"><?= $no++; ?></td>
                                <td data-label="ID BARANG"><?= htmlspecialchars($row['id_brg']); ?></td>
                                <td data-label="FOTO">
                                    <img src="dist/upload_img/<?= $row['gambar_brg']; ?>" class="img-barang" onerror="this.src='dist/upload_img/default.png'">
                                </td>
                                <td data-label="NAMA BARANG" style="text-align: left;"><strong><?= htmlspecialchars($row['nama_brg']); ?></strong></td>
                                <td data-label="SPESIFIKASI" style="text-align: left;"><?= htmlspecialchars($row['spesifikasi_brg']); ?></td>
                                <td data-label="MERK"><?= htmlspecialchars($row['merk_brg']); ?></td>
                                <td data-label="KATEGORI">
                                    <span><?= htmlspecialchars($row['nama_kategori'] ?? 'Tidak Ada'); ?></span>
                                </td>
                                <td data-label="NO RAK"><?= htmlspecialchars($row['norak_brg']); ?></td>
                                <td data-label="STOK">
                                    <span>
                                        <?= $stok; ?> pcs
                                    </span>
                                </td>
                                <td data-label="STATUS"><?= $status; ?></td>
                            </tr>
                            <?php } ?>
                            
                            <?php if ($total_barang == 0): ?>
                            <tr>
                                <td colspan="11" data-label="INFO" style="text-align: center; padding: 50px;">
                                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                                    <h4 style="margin-top: 15px;">Semua Stok Barang Aman</h4>
                                    <p>Tidak ada barang dengan stok ≤ 5 pcs pada kategori ini</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
// Fungsi apply filter
function applyFilter() {
    var kategori = $('#filterKategori').val();
    var status = '<?= $filter_status ?>';
    if (kategori > 0) {
        window.location.href = '?page=prioritas_stok&kategori=' + kategori + (status ? '&status=' + status : '');
    } else {
        window.location.href = '?page=prioritas_stok' + (status ? '?status=' + status : '');
    }
}

// Fungsi filter berdasarkan status (klik card)
function filterStatus(status) {
    var kategori = $('#filterKategori').val();
    if (status == 'all') {
        if (kategori > 0) {
            window.location.href = '?page=prioritas_stok&kategori=' + kategori;
        } else {
            window.location.href = '?page=prioritas_stok';
        }
    } else {
        if (kategori > 0) {
            window.location.href = '?page=prioritas_stok&kategori=' + kategori + '&status=' + status;
        } else {
            window.location.href = '?page=prioritas_stok&status=' + status;
        }
    }
}

// Enter key pada select
$('#filterKategori').on('keypress', function(e) {
    if (e.which === 13) {
        applyFilter();
    }
});

// Highlight card yang aktif
<?php if ($filter_status == 'habis'): ?>
$('.summary-card').eq(1).addClass('active');
<?php elseif ($filter_status == 'kritis'): ?>
$('.summary-card').eq(2).addClass('active');
<?php elseif ($filter_status == 'menipis'): ?>
$('.summary-card').eq(3).addClass('active');
<?php endif; ?>

$(document).ready(function() {
    // fungsi buat tombol excel di sebelah search
    function tambahTombolExcel() {
        if ($('#btnExportExcel').length) return;
        
        var currentKategori = $('#filterKategori').val();
        var currentStatus = '<?= $filter_status ?>';
        var url = 'export_prioritas_stok_excel.php?kategori=' + currentKategori + '&status=' + currentStatus;
        
        var tombol = $('<a>', {
            href: url,
            id: 'btnExportExcel',
            target: '_blank',
            class: 'btn-export',
            html: '<i class="fas fa-file-excel"></i> Export Excel'
        });
        
        $('#example1_filter').append(tombol);
    }

    if ($.fn.DataTable.isDataTable('#example1')) {
        tambahTombolExcel();
        return;
    }

    $('#example1').on('init.dt', function() {
        tambahTombolExcel();
    });

    var observer = new MutationObserver(function() {
        if ($.fn.DataTable.isDataTable('#example1')) {
            tambahTombolExcel();
            observer.disconnect();
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>