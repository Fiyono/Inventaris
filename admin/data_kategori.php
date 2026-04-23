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
include "koneksi.php";

// ===================
// PROSES TAMBAH DATA KATEGORI
// ===================
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    
    if (!empty($nama_kategori)) {
        $query = mysqli_query($koneksi, "INSERT INTO tbl_kategori (nama_kategori) VALUES ('$nama_kategori')");
        
        if ($query) {
            echo "<script>alert('Data kategori berhasil ditambahkan!'); window.location.href='?page=data_kategori';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan data kategori!');</script>";
        }
    } else {
        echo "<script>alert('Nama kategori tidak boleh kosong!');</script>";
    }
}

// ===================
// PROSES EDIT DATA KATEGORI
// ===================
if (isset($_POST['edit_kategori'])) {
    $id_kategori = (int)$_POST['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    
    if (!empty($nama_kategori)) {
        $query = mysqli_query($koneksi, "UPDATE tbl_kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = '$id_kategori'");
        
        if ($query) {
            echo "<script>alert('Data kategori berhasil diupdate!'); window.location.href='?page=data_kategori';</script>";
        } else {
            echo "<script>alert('Gagal mengupdate data kategori!');</script>";
        }
    } else {
        echo "<script>alert('Nama kategori tidak boleh kosong!');</script>";
    }
}

// ===================
// PROSES HAPUS DATA KATEGORI
// ===================
if (isset($_GET['hapus'])) {
    $id_kategori = (int)$_GET['hapus'];
    
    // Cek apakah kategori sedang digunakan di tbl_barang
    $cek = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_kategori = '$id_kategori'");
    
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kategori tidak dapat dihapus karena masih digunakan oleh beberapa barang!'); window.location.href='?page=data_kategori';</script>";
    } else {
        $query = mysqli_query($koneksi, "DELETE FROM tbl_kategori WHERE id_kategori = '$id_kategori'");
        
        if ($query) {
            echo "<script>alert('Data kategori berhasil dihapus!'); window.location.href='?page=data_kategori';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data kategori!');</script>";
        }
    }
}
?>

<style>
    /* ========== STYLE KHUSUS DATA KATEGORI ========== */
    
    /* Card utama */
    .card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    
    /* Header card - tombol di pojok kanan */
    .card-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 25px;
        border-bottom: none;
    }
    
    .card-header h6 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .card-header h6 i {
        margin-right: 10px;
        font-size: 20px;
    }
    
    /* Tombol Tambah - di pojok kanan */
    .btn-tambah {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 14px;
        white-space: nowrap;
        margin-left: auto;
    }
    
    .btn-tambah:hover {
        background: linear-gradient(135deg, #218838, #1a6e2d);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        color: white;
    }
    
    /* Tombol Edit */
    .btn-edit-kategori {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #333;
        padding: 5px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        margin-right: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-edit-kategori:hover {
        background: linear-gradient(135deg, #e0a800, #c69500);
        transform: translateY(-1px);
        color: #333;
    }
    
    /* Tombol Hapus */
    .btn-hapus-kategori {
        background: linear-gradient(135deg, #dc3545, #b02a37);
        color: white;
        padding: 5px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-hapus-kategori:hover {
        background: linear-gradient(135deg, #c82333, #a71d2a);
        transform: translateY(-1px);
        color: white;
    }
    
    /* Card Body */
    .card-body {
        padding: 20px;
    }
    
    /* Tabel Kategori */
    #tblKategori {
        width: 100% !important;
        margin-bottom: 0;
    }
    
    #tblKategori thead th {
        background: #f8f9fa;
        color: #495057;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 10px;
        border-bottom: 2px solid #dee2e6;
    }
    
    #tblKategori tbody td {
        font-size: 13px;
        text-align: center;
        vertical-align: middle;
        padding: 12px 10px;
        color: #333;
    }
    
    #tblKategori tbody tr:hover {
        background-color: #f1f9ff !important;
    }
    
    #tblKategori tbody tr:nth-child(even) {
        background-color: #fafafa;
    }
    
    /* Badge ID */
    .badge-id {
        background: #e9ecef;
        color: #495057;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    
    /* DataTables wrapper */
    .dataTables_wrapper .dataTables_length {
        float: left;
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_length select {
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid #ddd;
        margin: 0 5px;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 6px 12px;
        margin-left: 8px;
        width: 250px;
    }
    
    .dataTables_wrapper .dataTables_info {
        float: left;
        padding-top: 10px;
        font-size: 13px;
        color: #666;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        padding-top: 10px;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 5px 12px;
        margin: 0 2px;
        border-radius: 6px;
        border: 1px solid #ddd;
        background: white;
        cursor: pointer;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white !important;
        border: none;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #007bff;
        color: white !important;
    }
    
    /* Modal */
    .modal-content {
        border-radius: 16px;
        overflow: hidden;
        border: none;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border: none;
        padding: 15px 20px;
    }
    
    .modal-header .modal-title {
        font-size: 18px;
        font-weight: 600;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.8;
        text-shadow: none;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 13px;
    }
    
    .form-group label i {
        margin-right: 6px;
        color: #007bff;
    }
    
    .form-control {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 10px 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    /* Modal Konfirmasi Hapus */
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
        border-radius: 16px;
        padding: 25px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .modal-content-confirm h4 {
        margin-bottom: 15px;
        color: #dc3545;
        font-weight: 600;
    }
    
    .modal-content-confirm p {
        margin-bottom: 20px;
        color: #555;
        line-height: 1.5;
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
        padding: 8px 25px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }
    
    .btn-confirm-yes:hover {
        background: #c82333;
    }
    
    .btn-confirm-no {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 25px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }
    
    .btn-confirm-no:hover {
        background: #5a6268;
    }
    
    /* Clearfix */
    .clearfix {
        clear: both;
    }
    
    /* ========== RESPONSIVE MOBILE ========== */
    @media screen and (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
            padding: 15px;
        }
        
        .card-header h6 {
            margin-bottom: 10px;
        }
        
        .btn-tambah {
            width: 100%;
            text-align: center;
            padding: 10px;
            white-space: normal;
            margin-left: 0;
        }
        
        .btn-edit-kategori, .btn-hapus-kategori {
            padding: 6px 12px;
            font-size: 11px;
            margin: 2px;
        }
        
        /* DataTables mobile */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none;
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            width: 100%;
            margin-left: 0;
            margin-top: 5px;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none;
            text-align: center;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px !important;
            font-size: 11px !important;
            margin: 2px !important;
        }
        
        /* Tabel mobile - card style */
        .table-responsive {
            overflow-x: visible;
        }
        
        #tblKategori thead {
            display: none;
        }
        
        #tblKategori tbody tr {
            display: block;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 0;
        }
        
        #tblKategori tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left !important;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        
        #tblKategori tbody td:last-child {
            border-bottom: none;
        }
        
        #tblKategori tbody td:before {
            content: attr(data-label);
            font-weight: 600;
            color: #007bff;
            width: 40%;
            font-size: 12px;
        }
        
        /* Modal mobile */
        .modal-dialog {
            margin: 15px;
            width: calc(100% - 30px);
            max-width: none;
        }
        
        .modal-footer {
            flex-direction: column;
            gap: 10px;
        }
        
        .modal-footer .btn {
            width: 100%;
            margin: 0;
            padding: 10px;
        }
        
        .modal-content-confirm {
            width: 90%;
            padding: 20px;
        }
        
        .modal-buttons {
            flex-direction: column;
        }
        
        .modal-buttons button {
            width: 100%;
            padding: 10px;
        }
    }
    
    /* Tablet */
    @media screen and (min-width: 769px) and (max-width: 1024px) {
        .btn-edit-kategori, .btn-hapus-kategori {
            padding: 4px 10px;
            font-size: 11px;
        }
        
        #tblKategori thead th {
            font-size: 12px;
            padding: 8px 5px;
        }
        
        #tblKategori tbody td {
            font-size: 12px;
            padding: 8px 5px;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header">
                <h6>
                    <i class="fas fa-tags"></i> DAFTAR KATEGORI BARANG
                </h6>
                <button type="button" class="btn-tambah" data-toggle="modal" data-target="#modalTambahKategori">
                    <i class="fas fa-plus-circle"></i> TAMBAH KATEGORI
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tblKategori" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="65%">NAMA KATEGORI</th>
                                <th width="30%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $sql = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY id_kategori DESC");
                            while ($row = mysqli_fetch_array($sql)) { ?>
                                <tr>
                                    <td data-label="NO"><?= $no++; ?></td>
                                    <td data-label="NAMA KATEGORI">
                                        <?= htmlspecialchars($row['nama_kategori']); ?>
                                    </td>
                                    <td data-label="AKSI">
                                        <button type="button" class="btn-edit-kategori" data-toggle="modal" data-target="#modalEditKategori<?= $row['id_kategori']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn-hapus-kategori" onclick="confirmDelete(<?= $row['id_kategori']; ?>, '<?= htmlspecialchars($row['nama_kategori']); ?>')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- MODAL EDIT KATEGORI -->
                                <div class="modal fade" id="modalEditKategori<?= $row['id_kategori']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-edit"></i> Edit Kategori
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                                                    <div class="form-group">
                                                        <label><i class="fas fa-tag"></i> Nama Kategori</label>
                                                        <input type="text" 
                                                               name="nama_kategori" 
                                                               class="form-control" 
                                                               value="<?= htmlspecialchars($row['nama_kategori']); ?>" 
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        <i class="fas fa-times"></i> Batal
                                                    </button>
                                                    <button type="submit" name="edit_kategori" class="btn btn-warning">
                                                        <i class="fas fa-save"></i> Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH KATEGORI -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Tambah Kategori Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nama Kategori</label>
                        <input type="text" 
                               name="nama_kategori" 
                               class="form-control" 
                               placeholder="Masukkan nama kategori baru" 
                               required>
                        <small class="text-muted">Contoh: Elektronik, Furniture, Alat Tulis, dll.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="simpan_kategori" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI HAPUS -->
<div id="deleteModal" style="display: none;">
    <div class="modal-confirm">
        <div class="modal-content-confirm">
            <h4><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h4>
            <p id="deleteMessage">Apakah Anda yakin ingin menghapus kategori ini?</p>
            <div class="modal-buttons">
                <button class="btn-confirm-yes" onclick="deleteData()">Ya, Hapus</button>
                <button class="btn-confirm-no" onclick="closeModal()">Batal</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
let deleteId = null;
let deleteNama = null;

// Fungsi konfirmasi hapus
function confirmDelete(id, nama) {
    deleteId = id;
    deleteNama = nama;
    document.getElementById('deleteMessage').innerHTML = `Apakah Anda yakin ingin menghapus kategori <strong>"${nama}"</strong>?<br><span style="color: #dc3545; font-size: 11px;">⚠️ Kategori yang sedang digunakan oleh barang TIDAK dapat dihapus!</span>`;
    document.getElementById('deleteModal').style.display = 'flex';
}

// Fungsi hapus data
function deleteData() {
    if (deleteId) {
        window.location.href = '?page=data_kategori&hapus=' + deleteId;
    }
}

// Fungsi tutup modal
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
    deleteNama = null;
}

// Tutup modal jika klik di luar
document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Inisialisasi DataTable
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tblKategori')) {
        $('#tblKategori').DataTable().destroy();
    }
    
    $('#tblKategori').DataTable({
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        language: {
            search: "<i class='fas fa-search'></i> Cari:",
            searchPlaceholder: "Cari kategori...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "« Pertama",
                last: "Terakhir »",
                next: "→",
                previous: "←"
            },
            zeroRecords: "Tidak ada data yang ditemukan"
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
});
</script>