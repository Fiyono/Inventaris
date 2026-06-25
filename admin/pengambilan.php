<?php
// pengambilan.php - Form Pengambilan Barang Multiple Items (Menggunakan tbl_ambil)

// Proses simpan pengambilan
if (isset($_POST['simpan_ambil'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $tgl_brg_keluar = mysqli_real_escape_string($koneksi, $_POST['tgl_brg_keluar']);
    $alamat_ruang = mysqli_real_escape_string($koneksi, $_POST['alamat_ruang']);
    $tujuan_gunabarang = mysqli_real_escape_string($koneksi, $_POST['tujuan_gunabarang']);
    
    // Ambil data user
    $q_user = mysqli_query($koneksi, "SELECT nama_lengkap FROM tb_user WHERE id_user='$id_user'");
    $data_user = mysqli_fetch_assoc($q_user);
    $nama_pengambil = $data_user['nama_lengkap'] ?? '-';
    
    // Ambil data barang yang diambil
    $barang_ids = $_POST['barang_ids'] ?? [];
    $jumlah_ambil = $_POST['jumlah_ambil'] ?? [];
    
    $success_count = 0;
    $list_barang = [];
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        foreach ($barang_ids as $index => $id_brg) {
            $jml = (int)($jumlah_ambil[$index] ?? 0);
            
            if ($jml <= 0 || empty($id_brg)) {
                continue;
            }
            
            $brg_query = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_brg = '$id_brg' FOR UPDATE");
            $brg = mysqli_fetch_assoc($brg_query);
            
            if (!$brg) {
                throw new Exception("Barang dengan ID $id_brg tidak ditemukan!");
            }
            
            // Cek stok tersedia
            $stok_tersedia = (int) $brg['jumlah_brg'];
            
            if ($jml > $stok_tersedia) {
                throw new Exception("Stok tidak cukup untuk {$brg['nama_brg']}! Stok tersedia: $stok_tersedia pcs");
            }
            
            // Update stok barang (kurangi stok)
            $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg - $jml WHERE id_brg = '$id_brg'");
            
            if (!$update_stok) {
                throw new Exception("Gagal mengupdate stok untuk {$brg['nama_brg']}");
            }
            
            // Insert ke tbl_ambil
            $query = "INSERT INTO tbl_ambil 
                      (id_brg, id_user, tgl_brg_keluar, jumlah_brg, alamat_ruang, tujuan_gunabarang) 
                      VALUES 
                      ('$id_brg', '$id_user', '$tgl_brg_keluar', '$jml', '$alamat_ruang', '$tujuan_gunabarang')";
            
            if (!mysqli_query($koneksi, $query)) {
                throw new Exception("Gagal menyimpan data pengambilan: " . mysqli_error($koneksi));
            }
            
            $list_barang[] = [
                'nama' => $brg['nama_brg'],
                'spesifikasi' => $brg['spesifikasi_brg'],
                'merk' => $brg['merk_brg'],
                'jumlah' => $jml
            ];
            
            // Insert ke history
            $waktu_sekarang = date('H:i:s');
            $q_hist = "INSERT INTO tbl_history
                       (jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history, id_user)
                       VALUES
                       ('Ambil', '$id_brg', '{$brg['nama_brg']}', '$jml', '$tgl_brg_keluar', '$waktu_sekarang', '$id_user')";
            mysqli_query($koneksi, $q_hist);
            
            $success_count++;
        }
        
        mysqli_commit($koneksi);
        
        // Encode data untuk dikirim via URL
        $struk_data = base64_encode(json_encode([
            'nomor' => 'AMB/' . date('Ymd') . '/' . rand(1000, 9999),
            'tanggal' => $tgl_brg_keluar,
            'pengambil' => $nama_pengambil,
            'tujuan' => $tujuan_gunabarang,
            'alamat_ruang' => $alamat_ruang,
            'barang' => $list_barang,
            'total_barang' => $success_count,
            'total_unit' => array_sum(array_column($list_barang, 'jumlah'))
        ]));
        
        // Redirect ke halaman struk
        echo "<script>
            alert('Berhasil mengambil $success_count barang!');
            window.location.href = 'cetak_struk.php?jenis=ambil&data=" . urlencode($struk_data) . "&auto_print=1';
        </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>
            alert('Gagal: " . addslashes($e->getMessage()) . "');
            window.location.href = '?page=pengambilan';
        </script>";
        exit;
    }
}

// Ambil daftar user untuk dropdown
$user_query = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap");

// Ambil daftar barang yang TERSEDIA (stok fisik > 0)
$barang_query = mysqli_query($koneksi, "
    SELECT b.* 
    FROM tbl_barang b
    WHERE b.jumlah_brg > 0
    ORDER BY b.nama_brg ASC
");

// Siapkan data barang dalam bentuk array untuk JavaScript
$barang_list = [];
mysqli_data_seek($barang_query, 0);
while ($brg = mysqli_fetch_array($barang_query)) {
    $stok_tersedia = $brg['jumlah_brg'];
    $gambar = !empty($brg['gambar_brg']) ? $brg['gambar_brg'] : 'default.png';
    $barang_list[] = [
        'id' => $brg['id_brg'],
        'nama' => $brg['nama_brg'],
        'spesifikasi' => $brg['spesifikasi_brg'],
        'merk' => $brg['merk_brg'],
        'stok' => $stok_tersedia,
        'gambar' => $gambar
    ];
}
$barang_json = json_encode($barang_list);
?>

<style>
/* ========== STYLE PENGAMBILAN - RESPONSIF MOBILE ========== */

/* Style Desktop */
.barang-gambar {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: transform 0.2s;
}
.barang-gambar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.btn-group-custom {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 15px;
    flex-wrap: wrap;
}
.btn-group-custom button {
    padding: 8px 20px;
}
.btn-tambah-desktop { display: inline-block; }
.btn-tambah-mobile { display: none; }

/* Style Select2 */
.select2-container .select2-selection--single {
    height: 38px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
#tblBarang td .select2-container {
    width: 100% !important;
    min-width: 150px !important;
}

/* ========== RESPONSIF MOBILE (max 768px) ========== */
@media only screen and (max-width: 768px) {
    .card {
        margin: 5px;
        border-radius: 10px;
    }
    .card-header {
        padding: 10px 12px;
    }
    .card-header .card-title {
        font-size: 15px !important;
    }
    .card-body {
        padding: 12px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    .form-group label {
        font-size: 12px;
        margin-bottom: 4px;
    }
    .form-control {
        font-size: 13px;
        padding: 8px 10px;
        height: auto;
    }
    
    .btn-tambah-desktop { display: none !important; }
    .btn-tambah-mobile { 
        display: block; 
        width: 100%; 
        margin-bottom: 12px;
        font-size: 13px;
        padding: 10px;
    }
    
    /* Tabel mobile card view */
    .table-responsive {
        overflow-x: visible !important;
        border: none;
    }
    #tblBarang {
        width: 100%;
        border: none;
    }
    #tblBarang thead { display: none; }
    
    #tblBarang tbody tr {
        display: block;
        border: 1px solid #ddd;
        border-radius: 10px;
        margin-bottom: 12px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 8px;
        position: relative;
    }
    
    #tblBarang tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 8px 6px;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        font-size: 12px;
        gap: 8px;
    }
    #tblBarang tbody td:last-child { border-bottom: none; }
    
    /* Label mobile */
    #tblBarang tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #17a2b8;
        width: 35%;
        font-size: 11px;
        flex-shrink: 0;
    }
    
    /* Kolom No */
    #tblBarang tbody td:first-child {
        font-weight: bold;
        background: #f0f9ff;
        border-radius: 6px;
        margin-bottom: 4px;
        font-size: 13px;
    }
    #tblBarang tbody td:first-child:before {
        content: "No. Urut";
    }
    
    /* Kolom Gambar */
    #tblBarang tbody td:nth-child(2) { justify-content: flex-start; }
    #tblBarang tbody td:nth-child(2):before { content: "Gambar"; }
    .barang-gambar { width: 45px; height: 45px; }
    
    /* Kolom Nama Barang */
    #tblBarang tbody td:nth-child(3):before { content: "Nama Barang"; }
    #tblBarang tbody td:nth-child(3) .select2-container { width: 60% !important; }
    
    /* Kolom Spesifikasi */
    #tblBarang tbody td:nth-child(4):before { content: "Spesifikasi"; }
    #tblBarang tbody td:nth-child(4) { word-break: break-word; }
    
    /* Kolom Merk */
    #tblBarang tbody td:nth-child(5):before { content: "Merk"; }
    
    /* Kolom Stok */
    #tblBarang tbody td:nth-child(6):before { content: "Stok"; }
    
    /* Kolom Jumlah */
    #tblBarang tbody td:nth-child(7):before { content: "Jumlah Ambil"; }
    #tblBarang tbody td:nth-child(7) input { width: 60%; font-size: 12px; }
    
    /* Kolom Aksi */
    #tblBarang tbody td:last-child { justify-content: flex-end; }
    #tblBarang tbody td:last-child:before { content: "Aksi"; }
    #tblBarang tbody td:last-child button { 
        width: auto; 
        padding: 5px 12px; 
        font-size: 11px;
    }
    
    /* Tombol submit */
    .btn-group-custom {
        flex-direction: column;
        gap: 8px;
    }
    .btn-group-custom button {
        width: 100%;
        padding: 10px;
        font-size: 13px;
    }
    
    /* Modal */
    .modal-dialog {
        margin: 15px;
    }
    .modal-content {
        border-radius: 12px;
    }
    
    textarea.form-control {
        font-size: 13px;
        padding: 8px 10px;
    }
    
    .text-muted {
        font-size: 10px;
        margin-top: 8px;
    }
    
    .row {
        margin-right: -8px;
        margin-left: -8px;
    }
    .col-6, .col-md-3, .col-md-6, .col-12 {
        padding-right: 8px;
        padding-left: 8px;
    }
}

/* ========== RESPONSIF TABLET (769px - 1024px) ========== */
@media only screen and (min-width: 769px) and (max-width: 1024px) {
    #tblBarang {
        font-size: 12px;
    }
    .barang-gambar { width: 45px; height: 45px; }
    #tblBarang thead th {
        font-size: 11px;
        padding: 6px 4px;
    }
    #tblBarang tbody td {
        font-size: 11px;
        padding: 6px 4px;
    }
    .form-control-sm { font-size: 11px; }
    .btn-tambah-mobile { display: none; }
    .btn-tambah-desktop { display: inline-block; }
    #tblBarang td .select2-container { min-width: 120px !important; }
}

/* ========== DESKTOP (min 1025px) ========== */
@media only screen and (min-width: 1025px) {
    #tblBarang { width: 100% !important; }
    #tblBarang thead { display: table-header-group; }
    #tblBarang tbody tr { 
        display: table-row; 
        border: none; 
        margin-bottom: 0; 
        padding: 0; 
        box-shadow: none; 
    }
    #tblBarang tbody td { 
        display: table-cell; 
        border-bottom: 1px solid #dee2e6; 
    }
    #tblBarang tbody td:before { display: none; }
    .btn-tambah-mobile { display: none; }
    .btn-tambah-desktop { display: inline-block; }
    #tblBarang td .select2-container { 
        width: 100% !important; 
        min-width: 180px !important; 
    }
}

/* ========== LAYAR SANGAT KECIL (max 480px) ========== */
@media only screen and (max-width: 480px) {
    .card-body { padding: 8px; }
    
    #tblBarang tbody td {
        flex-wrap: wrap;
        padding: 6px 4px;
    }
    #tblBarang tbody td:before {
        width: 100%;
        margin-bottom: 4px;
    }
    #tblBarang tbody td:nth-child(3) .select2-container { width: 100% !important; }
    #tblBarang tbody td:nth-child(7) input { width: 100%; }
    #tblBarang tbody td:last-child { justify-content: center; }
    
    .btn-group-custom button {
        font-size: 12px;
        padding: 8px;
    }
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <div class="card-title mb-0" style="font-size:18px; font-weight:bold;">
                    <i class="fas fa-level-down-alt"></i> FORM PENGAMBILAN BARANG
                </div>
                <div class="card-tools">
                    <a href="?page=master_data" class="btn btn-default btn-sm text-white" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" id="formMultiAmbil">
                    <!-- Data Pengambil -->
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> NAMA PENGAMBIL <span class="text-danger">*</span></label>
                                <select class="form-control select2bs4" name="id_user" id="id_user" required style="width:100%;">
                                    <option value="">-- PILIH PENGAMBIL --</option>
                                    <?php while ($user = mysqli_fetch_array($user_query)): ?>
                                        <option value="<?= htmlspecialchars($user['id_user']); ?>">
                                            <?= htmlspecialchars($user['nama_lengkap']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> TANGGAL</label>
                                <input type="date" name="tgl_brg_keluar" id="tgl_brg_keluar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> ALAMAT RUANG</label>
                                <select class="form-control" name="alamat_ruang" id="alamat_ruang" required>
                                    <option value="">-- PILIH RUANG --</option>
                                    <option>RUANG LAB C1</option>
                                    <option>RUANG LAB C2</option>
                                    <option>RUANG LAB C3</option>
                                    <option>RUANG LAB C4</option>
                                    <option>RUANG LAB C5</option>
                                    <option>RUANG AULA</option>
                                    <option>RUANG GURU</option>
                                    <option>RUANG BK</option>
                                    <option>RUANG INSTRUKTUR PPLG</option>
                                    <option>SARPRAS</option>
                                    <option>SAPRA MART</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> TUJUAN PENGGUNAAN BARANG <span class="text-danger">*</span></label>
                        <textarea name="tujuan_gunabarang" class="form-control" rows="2" required placeholder="Contoh: Praktikum PPLG, Kegiatan Lomba, Acara Sekolah"></textarea>
                    </div>
                    
                    <hr>
                    
                    <!-- Daftar Barang -->
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> DAFTAR BARANG YANG AKAN DIAMBIL</label>
                        
                        <div class="btn-tambah-mobile">
                            <button type="button" class="btn btn-info btn-block" onclick="tambahBaris()">
                                <i class="fas fa-plus"></i> TAMBAH BARANG
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tblBarang">
                                <thead class="bg-info text-white">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Gambar</th>
                                        <th width="25%">Nama Barang</th>
                                        <th width="20%">Spesifikasi</th>
                                        <th width="10%">Merk</th>
                                        <th width="10%">Stok</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="10%">
                                            <button type="button" class="btn btn-success btn-sm btn-tambah-desktop" onclick="tambahBaris()">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBarang">
                                    <tr id="baris1">
                                        <td class="text-center" data-label="No. Urut">1</td>
                                        <td class="text-center" data-label="Gambar">
                                            <img src="dist/upload_img/default.png" class="barang-gambar" id="gambar_1" alt="Gambar" style="display:none;" onclick="previewGambar(this)">
                                        </td>
                                        <td data-label="Nama Barang">
                                            <select name="barang_ids[]" class="form-control select2-barang" id="select_1" onchange="updateBarang(this, 1)" required style="width:100%;">
                                                <option value="">-- Cari & Pilih Barang --</option>
                                            </select>
                                        </td>
                                        <td class="spesifikasi-cell" id="spesifikasi_1" data-label="Spesifikasi">-</td>
                                        <td class="merk-cell" id="merk_1" data-label="Merk">-</td>
                                        <td class="stok-cell text-center" id="stok_1" data-label="Stok Tersedia">-</td>
                                        <td data-label="Jumlah Ambil">
                                            <input type="number" name="jumlah_ambil[]" class="form-control form-control-sm jumlah-ambil" min="1" value="1" onchange="validasiJumlah(this, 1)">
                                        </td>
                                        <td class="text-center" data-label="Aksi">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="btn-tambah-mobile" style="margin-top: 10px;">
                            <button type="button" class="btn btn-info btn-block" onclick="tambahBaris()">
                                <i class="fas fa-plus"></i> TAMBAH BARANG LAGI
                            </button>
                        </div>
                        
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Klik gambar untuk memperbesar. Ketik nama barang di dropdown untuk mencari.
                        </small>
                    </div>
                    
                    <div class="btn-group-custom">
                        <button type="reset" class="btn btn-default"><i class="fas fa-undo"></i> Reset</button>
                        <button type="submit" name="simpan_ambil" class="btn btn-info"><i class="fas fa-save"></i> SIMPAN PENGAMBILAN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Gambar -->
<div class="modal fade" id="modalPreviewGambar" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-image"></i> Preview Gambar</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImg" src="" class="img-fluid rounded" style="max-height: 300px;">
                <p id="previewNama" class="mt-2 text-muted"></p>
            </div>
        </div>
    </div>
</div>

<script>
// Data barang dari PHP
var dataBarang = <?= $barang_json; ?>;
var barisCount = 1;

// Konversi dataBarang array ke object untuk akses cepat
var dataBarangObj = {};
dataBarang.forEach(function(item) {
    dataBarangObj[item.id] = item;
});

// Fungsi untuk mengisi option dropdown
function getOptionsHTML(selectedId) {
    var html = '<option value="">-- Cari & Pilih Barang --</option>';
    dataBarang.forEach(function(item) {
        var selected = (item.id == selectedId) ? ' selected' : '';
        html += '<option value="' + item.id + '" data-stok="' + item.stok + '" data-spesifikasi="' + item.spesifikasi + '" data-merk="' + item.merk + '" data-gambar="' + item.gambar + '" data-nama="' + item.nama + '"' + selected + '>' + item.nama + ' (Stok: ' + item.stok + ')</option>';
    });
    return html;
}

// Isi dropdown baris pertama
document.getElementById('select_1').innerHTML = getOptionsHTML();

// Preview gambar
function previewGambar(img) {
    document.getElementById('previewImg').src = img.src;
    document.getElementById('previewNama').innerHTML = img.getAttribute('data-nama') || 'Gambar Barang';
    $('#modalPreviewGambar').modal('show');
}

// Update barang saat dipilih
function updateBarang(select, rowId) {
    var id = select.value;
    var data = dataBarangObj[id];
    
    document.getElementById('spesifikasi_' + rowId).innerHTML = data ? (data.spesifikasi || '-') : '-';
    document.getElementById('merk_' + rowId).innerHTML = data ? (data.merk || '-') : '-';
    document.getElementById('stok_' + rowId).innerHTML = data ? '<span class="badge badge-info">' + data.stok + '</span>' : '-';
    
    var inputJumlah = document.querySelector('#baris' + rowId + ' .jumlah-ambil');
    var img = document.getElementById('gambar_' + rowId);
    
    if (data) {
        inputJumlah.max = data.stok;
        img.src = 'dist/upload_img/' + data.gambar;
        img.style.display = 'inline-block';
        img.setAttribute('data-nama', data.nama);
        if (parseInt(inputJumlah.value) > data.stok) inputJumlah.value = data.stok;
        if (parseInt(inputJumlah.value) < 1) inputJumlah.value = 1;
    } else {
        img.style.display = 'none';
        img.src = 'dist/upload_img/default.png';
        inputJumlah.max = 9999;
    }
}

// Validasi jumlah
function validasiJumlah(input, rowId) {
    var select = document.querySelector('#baris' + rowId + ' .select2-barang');
    var data = dataBarangObj[select.value];
    var jumlah = parseInt(input.value);
    
    if (data) {
        if (isNaN(jumlah) || jumlah > data.stok) {
            alert('Jumlah pengambilan tidak boleh melebihi stok (' + data.stok + ')');
            input.value = data.stok;
        }
        if (jumlah < 1 || isNaN(jumlah)) input.value = 1;
    } else if (jumlah < 1 || isNaN(jumlah)) {
        input.value = 1;
    }
}

// Tambah baris
function tambahBaris() {
    barisCount++;
    var row = document.createElement('tr');
    row.id = 'baris' + barisCount;
    
    row.innerHTML = `
        <td class="text-center" data-label="No. Urut">${barisCount}</td>
        <td class="text-center" data-label="Gambar">
            <img src="dist/upload_img/default.png" class="barang-gambar" id="gambar_${barisCount}" style="display:none;cursor:pointer;" onclick="previewGambar(this)">
        </td>
        <td data-label="Nama Barang">
            <select name="barang_ids[]" class="form-control select2-barang" id="select_${barisCount}" onchange="updateBarang(this, ${barisCount})" required style="width:100%;">
                ${getOptionsHTML()}
            </select>
        </td>
        <td class="spesifikasi-cell" id="spesifikasi_${barisCount}" data-label="Spesifikasi">-</td>
        <td class="merk-cell" id="merk_${barisCount}" data-label="Merk">-</td>
        <td class="stok-cell text-center" id="stok_${barisCount}" data-label="Stok Tersedia">-</td>
        <td data-label="Jumlah Ambil">
            <input type="number" name="jumlah_ambil[]" class="form-control form-control-sm jumlah-ambil" min="1" value="1" onchange="validasiJumlah(this, ${barisCount})">
        </td>
        <td class="text-center" data-label="Aksi">
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </td>
    `;
    
    document.getElementById('tbodyBarang').appendChild(row);
    
    // Inisialisasi Select2 untuk baris baru
    $('#select_' + barisCount).select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- Cari & Pilih Barang --',
        allowClear: true
    });
    
    // Scroll ke baris baru (hanya di mobile)
    if (window.innerWidth < 768) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Hapus baris
function hapusBaris(btn) {
    var tbody = document.getElementById('tbodyBarang');
    if (tbody.children.length > 1) {
        var row = btn.closest('tr');
        $(row).find('.select2-barang').select2('destroy');
        row.remove();
        
        Array.from(tbody.children).forEach(function(r, i) {
            r.cells[0].innerHTML = i + 1;
        });
        barisCount = tbody.children.length;
    } else {
        alert('Minimal harus ada 1 barang yang diambil!');
    }
}

// Validasi submit
document.getElementById('formMultiAmbil').addEventListener('submit', function(e) {
    var rows = document.querySelectorAll('#tbodyBarang .select2-barang');
    for (var i = 0; i < rows.length; i++) {
        if (!rows[i].value) {
            e.preventDefault();
            alert('Baris ' + (i + 1) + ': Pilih barang terlebih dahulu!');
            return;
        }
    }
    if (!document.getElementById('id_user').value) {
        e.preventDefault();
        alert('Pilih pengambil terlebih dahulu!');
        return;
    }
    if (!document.getElementById('alamat_ruang').value) {
        e.preventDefault();
        alert('Pilih alamat ruang terlebih dahulu!');
        return;
    }
});

console.log('Pengambilan - Data barang loaded:', dataBarang.length, 'items');
</script>