<?php
// peminjaman.php - admin/peminjaman.php (HANYA BAGIAN simpan_cetak yang diubah)

// Proses simpan & cetak struk
if (isset($_POST['simpan_cetak'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $tgl_pinjam = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tgl_perkiraan_balik = mysqli_real_escape_string($koneksi, $_POST['tgl_perkiraan_balik']);
    $tujuan_gunabarang = mysqli_real_escape_string($koneksi, $_POST['tujuan_gunabarang']);
    
    // Ambil data user
    $q_user = mysqli_query($koneksi, "SELECT id_organisasi, nama_lengkap FROM tb_user WHERE id_user='$id_user'");
    $data_user = mysqli_fetch_assoc($q_user);
    $organisasi = $data_user['id_organisasi'] ?? '-';
    $nama_peminjam = $data_user['nama_lengkap'] ?? '-';
    
    // Ambil data barang yang dipinjam
    $barang_ids = $_POST['barang_ids'] ?? [];
    $jumlah_pinjam = $_POST['jumlah_pinjam'] ?? [];
    
    $success_count = 0;
    $list_barang = [];
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        foreach ($barang_ids as $index => $id_brg) {
            $jml = (int)($jumlah_pinjam[$index] ?? 0);
            
            if ($jml <= 0 || empty($id_brg)) {
                continue;
            }
            
            $brg_query = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_brg = '$id_brg' FOR UPDATE");
            $brg = mysqli_fetch_assoc($brg_query);
            
            if (!$brg) {
                throw new Exception("Barang dengan ID $id_brg tidak ditemukan!");
            }
            
            $pinjam_query = mysqli_query($koneksi, "
                SELECT COALESCE(SUM(jumlah_pinjam), 0) AS total_dipinjam 
                FROM tbl_pinjaman 
                WHERE id_brg = '$id_brg' AND status = 'Dipinjam'
            ");
            $pinjam_data = mysqli_fetch_assoc($pinjam_query);
            $total_dipinjam = $pinjam_data['total_dipinjam'];
            $stok_tersedia = $brg['jumlah_brg'] - $total_dipinjam;
            
            if ($jml > $stok_tersedia) {
                throw new Exception("Stok tidak cukup untuk {$brg['nama_brg']}!");
            }
            
            $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg - $jml WHERE id_brg = '$id_brg'");
            
            $query = "INSERT INTO tbl_pinjaman 
                      (id_brg, id_user, tgl_pinjam, tgl_perkiraan_balik, jumlah_pinjam, organisasi, tujuan_gunabarang, status) 
                      VALUES 
                      ('$id_brg', '$id_user', '$tgl_pinjam', '$tgl_perkiraan_balik', '$jml', '$organisasi', '$tujuan_gunabarang', 'Dipinjam')";
            mysqli_query($koneksi, $query);
            
            $list_barang[] = [
                'nama' => $brg['nama_brg'],
                'spesifikasi' => $brg['spesifikasi_brg'],
                'merk' => $brg['merk_brg'],
                'jumlah' => $jml
            ];
            
            $waktu_sekarang = date('H:i:s');
            $q_hist = "INSERT INTO tbl_history
                       (jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history, id_user)
                       VALUES
                       ('Pinjam', '$id_brg', '{$brg['nama_brg']}', '$jml', '$tgl_pinjam', '$waktu_sekarang', '$id_user')";
            mysqli_query($koneksi, $q_hist);
            
            $success_count++;
        }
        
        mysqli_commit($koneksi);
        
        // Encode data untuk dikirim via URL
        $struk_data = base64_encode(json_encode([
            'nomor' => 'INV/' . date('Ymd') . '/' . rand(1000, 9999),
            'tanggal' => $tgl_pinjam,
            'tgl_kembali' => $tgl_perkiraan_balik,
            'peminjam' => $nama_peminjam,
            'tujuan' => $tujuan_gunabarang,
            'barang' => $list_barang,
            'total_barang' => $success_count,
            'total_unit' => array_sum(array_column($list_barang, 'jumlah'))
        ]));
        
        // Redirect ke halaman struk (bukan tab baru)
        echo "<script>
            alert('Berhasil meminjam $success_count barang!');
            window.location.href = 'cetak_struk.php?data=" . urlencode($struk_data) . "';
        </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>
            alert('Gagal: " . addslashes($e->getMessage()) . "');
            window.location.href = '?page=peminjaman';
        </script>";
        exit;
    }
}

// ... kode selanjutnya sama seperti sebelumnya



// Ambil daftar user untuk dropdown
$user_query = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap");

// Ambil daftar barang yang TERSEDIA (stok fisik - dipinjam > 0)
$barang_query = mysqli_query($koneksi, "
    SELECT b.*, 
           COALESCE(SUM(CASE WHEN p.status = 'Dipinjam' THEN p.jumlah_pinjam ELSE 0 END), 0) AS dipinjam
    FROM tbl_barang b
    LEFT JOIN tbl_pinjaman p ON b.id_brg = p.id_brg
    GROUP BY b.id_brg
    HAVING (b.jumlah_brg - dipinjam) > 0
    ORDER BY b.nama_brg ASC
");
?>

<style>
/* ========== STYLE PEMINJAMAN - RESPONSIF MOBILE ========== */

/* Style Desktop (default) */
.barang-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

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

.barang-info {
    flex: 1;
    min-width: 200px;
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

/* Tombol tambah di desktop */
.btn-tambah-desktop {
    display: inline-block;
}

.btn-tambah-mobile {
    display: none;
}

/* ========== RESPONSIF MOBILE (tanpa mengubah logika) ========== */
@media only screen and (max-width: 768px) {
    /* Card & Container */
    .card {
        margin: 10px;
        border-radius: 10px;
    }
    
    .card-header {
        padding: 12px 15px;
    }
    
    .card-header .card-title {
        font-size: 16px !important;
    }
    
    .card-body {
        padding: 15px;
    }
    
    /* Form Group */
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        font-size: 13px;
        margin-bottom: 5px;
        display: block;
    }
    
    .form-control, .form-control-sm {
        font-size: 14px;
        padding: 8px 10px;
        height: auto;
    }
    
    select.form-control {
        font-size: 14px;
        padding: 8px 10px;
    }
    
    /* Sembunyikan tombol tambah di desktop, tampilkan di mobile */
    .btn-tambah-desktop {
        display: none;
    }
    
    .btn-tambah-mobile {
        display: block;
        width: 100%;
        margin-bottom: 15px;
    }
    
    /* Tabel Mobile View - Card Style */
    .table-responsive {
        overflow-x: visible !important;
        border: none;
    }
    
    #tblBarang {
        width: 100%;
        border: none;
    }
    
    /* Sembunyikan header tabel di mobile */
    #tblBarang thead {
        display: none;
    }
    
    #tblBarang tbody tr {
        display: block;
        border: 1px solid #ddd;
        border-radius: 10px;
        margin-bottom: 15px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 10px;
        position: relative;
    }
    
    #tblBarang tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 10px 8px;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
        gap: 10px;
    }
    
    #tblBarang tbody td:last-child {
        border-bottom: none;
    }
    
    /* Label untuk setiap kolom di mobile */
    #tblBarang tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #007bff;
        width: 35%;
        font-size: 12px;
        flex-shrink: 0;
    }
    
    /* Kolom Nomor */
    #tblBarang tbody td:first-child {
        font-weight: bold;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 5px;
    }
    
    #tblBarang tbody td:first-child:before {
        content: "No. Urut";
    }
    
    /* Kolom Gambar */
    #tblBarang tbody td:nth-child(2):before {
        content: "Gambar";
    }
    
    #tblBarang tbody td:nth-child(2) {
        justify-content: flex-start;
    }
    
    .barang-gambar {
        width: 50px;
        height: 50px;
    }
    
    /* Kolom Nama Barang (select) */
    #tblBarang tbody td:nth-child(3):before {
        content: "Nama Barang";
    }
    
    #tblBarang tbody td:nth-child(3) select {
        width: 65%;
        font-size: 13px;
    }
    
    /* Kolom Spesifikasi */
    #tblBarang tbody td:nth-child(4):before {
        content: "Spesifikasi";
    }
    
    #tblBarang tbody td:nth-child(4) {
        word-break: break-word;
    }
    
    /* Kolom Merk */
    #tblBarang tbody td:nth-child(5):before {
        content: "Merk";
    }
    
    /* Kolom Stok */
    #tblBarang tbody td:nth-child(6):before {
        content: "Stok Tersedia";
    }
    
    /* Kolom Jumlah */
    #tblBarang tbody td:nth-child(7):before {
        content: "Jumlah Pinjam";
    }
    
    #tblBarang tbody td:nth-child(7) input {
        width: 65%;
        font-size: 13px;
    }
    
    /* Kolom Aksi - khusus untuk tombol hapus */
    #tblBarang tbody td:last-child:before {
        content: "Aksi";
    }
    
    #tblBarang tbody td:last-child {
        justify-content: flex-end;
    }
    
    #tblBarang tbody td:last-child button {
        width: auto;
        padding: 5px 15px;
    }
    
    /* Tombol di mobile */
    .btn-group-custom {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-group-custom button {
        width: 100%;
        justify-content: center;
        padding: 10px;
        font-size: 14px;
    }
    
    /* Tombol tambah barang di mobile */
    .btn-tambah-bawah {
        display: block;
        width: 100%;
        margin-top: 15px;
        margin-bottom: 15px;
    }
    
    /* Modal Preview Gambar */
    .modal-dialog {
        margin: 20px;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    /* Textarea */
    textarea.form-control {
        font-size: 14px;
        padding: 8px 10px;
    }
    
    /* Small text */
    .text-muted {
        font-size: 11px;
        display: block;
        margin-top: 10px;
    }
    
    /* Row spacing */
    .row {
        margin-right: -10px;
        margin-left: -10px;
    }
    
    .col-6, .col-md-3, .col-md-6, .col-12 {
        padding-right: 10px;
        padding-left: 10px;
    }
}

/* Tablet mode (769px - 1024px) */
@media only screen and (min-width: 769px) and (max-width: 1024px) {
    #tblBarang {
        font-size: 13px;
    }
    
    .barang-gambar {
        width: 50px;
        height: 50px;
    }
    
    #tblBarang thead th {
        font-size: 12px;
        padding: 8px 5px;
    }
    
    #tblBarang tbody td {
        font-size: 12px;
        padding: 8px 5px;
    }
    
    .form-control-sm {
        font-size: 12px;
    }
    
    .btn-tambah-mobile {
        display: none;
    }
    
    .btn-tambah-desktop {
        display: inline-block;
    }
}

/* Desktop tetap tampil normal */
@media only screen and (min-width: 1025px) {
    #tblBarang {
        width: 100% !important;
    }
    
    /* Pastikan tabel normal di desktop */
    #tblBarang thead {
        display: table-header-group;
    }
    
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
    
    #tblBarang tbody td:before {
        display: none;
    }
    
    .btn-tambah-mobile {
        display: none;
    }
    
    .btn-tambah-desktop {
        display: inline-block;
    }
}

/* Untuk layar sangat kecil (max 480px) */
@media only screen and (max-width: 480px) {
    .card-body {
        padding: 10px;
    }
    
    #tblBarang tbody td {
        flex-wrap: wrap;
        padding: 8px 5px;
    }
    
    #tblBarang tbody td:before {
        width: 100%;
        margin-bottom: 5px;
    }
    
    #tblBarang tbody td:nth-child(3) select,
    #tblBarang tbody td:nth-child(7) input {
        width: 100%;
    }
    
    #tblBarang tbody td:last-child {
        justify-content: center;
    }
    
    .btn-group-custom button {
        font-size: 13px;
        padding: 8px;
    }
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="card-title mb-0" style="font-size:18px; font-weight:bold;">
                    <i class="fas fa-boxes"></i> PEMINJAMAN BARANG
                </div>
                <div class="card-tools">
                    <a href="?page=master_data" class="btn btn-default btn-sm text-white" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form method="post" id="formMultiPinjam">
                    <!-- Data Peminjam -->
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> NAMA PEMINJAM <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_user" id="id_user" required>
                                    <option value="">-- PILIH PEMINJAM --</option>
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
                                <label><i class="fas fa-calendar"></i> TANGGAL PINJAM</label>
                                <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-check"></i> PERKIRAAN KEMBALI</label>
                                <input type="date" name="tgl_perkiraan_balik" id="tgl_perkiraan_balik" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> TUJUAN PENGGUNAAN BARANG <span class="text-danger">*</span></label>
                        <textarea name="tujuan_gunabarang" class="form-control" rows="2" required placeholder="Contoh: Praktikum PPLG, Kegiatan Lomba"></textarea>
                    </div>
                    
                    <hr>
                    
                    <!-- Daftar Barang -->
                    <div class="form-group">
                        <label><i class="fas fa-list"></i> DAFTAR BARANG YANG AKAN DIPINJAM</label>
                        
                        <!-- Tombol Tambah Barang untuk Mobile -->
                        <div class="btn-tambah-mobile">
                            <button type="button" class="btn btn-success btn-block" onclick="tambahBaris()">
                                <i class="fas fa-plus"></i> TAMBAH BARANG
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tblBarang">
                                <thead class="bg-secondary">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Gambar</th>
                                        <th width="25%">Nama Barang</th>
                                        <th width="20%">Spesifikasi</th>
                                        <th width="10%">Merk</th>
                                        <th width="5%">Stok</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="5%">
                                            <button type="button" class="btn btn-success btn-sm btn-tambah-desktop" onclick="tambahBaris()" title="Tambah Barang">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBarang">
                                    <!-- Baris 1 -->
                                    <tr id="baris1">
                                        <td class="text-center" data-label="No. Urut">1</td>
                                        <td class="text-center" data-label="Gambar">
                                            <img src="dist/upload_img/default.png" 
                                                 class="barang-gambar" 
                                                 id="gambar_1"
                                                 alt="Gambar Barang"
                                                 style="display:none;"
                                                 onclick="previewGambar(this)">
                                        </td>
                                        <td data-label="Nama Barang">
                                            <select name="barang_ids[]" class="form-control form-control-sm select-barang" id="select_1" onchange="updateBarang(this, 1)" required>
                                                <option value="">-- Pilih Barang --</option>
                                                <?php 
                                                // Reset pointer query
                                                $barang_query2 = mysqli_query($koneksi, "
                                                    SELECT b.*, 
                                                           COALESCE(SUM(CASE WHEN p.status = 'Dipinjam' THEN p.jumlah_pinjam ELSE 0 END), 0) AS dipinjam
                                                    FROM tbl_barang b
                                                    LEFT JOIN tbl_pinjaman p ON b.id_brg = p.id_brg
                                                    GROUP BY b.id_brg
                                                    HAVING (b.jumlah_brg - dipinjam) > 0
                                                    ORDER BY b.nama_brg ASC
                                                ");
                                                while ($brg = mysqli_fetch_array($barang_query2)): 
                                                    $stok_tersedia = $brg['jumlah_brg'] - $brg['dipinjam'];
                                                    $gambar_brg = !empty($brg['gambar_brg']) ? $brg['gambar_brg'] : 'default.png';
                                                ?>
                                                    <option value="<?= $brg['id_brg']; ?>" 
                                                            data-stok="<?= $stok_tersedia; ?>"
                                                            data-spesifikasi="<?= htmlspecialchars($brg['spesifikasi_brg']); ?>"
                                                            data-merk="<?= htmlspecialchars($brg['merk_brg']); ?>"
                                                            data-gambar="<?= $gambar_brg; ?>"
                                                            data-nama="<?= htmlspecialchars($brg['nama_brg']); ?>">
                                                        <?= htmlspecialchars($brg['nama_brg']); ?> (Stok: <?= $stok_tersedia; ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                        <td class="spesifikasi-cell" id="spesifikasi_1" data-label="Spesifikasi">-</td>
                                        <td class="merk-cell" id="merk_1" data-label="Merk">-</td>
                                        <td class="stok-cell text-center" id="stok_1" data-label="Stok Tersedia">-</td>
                                        <td data-label="Jumlah Pinjam">
                                            <input type="number" name="jumlah_pinjam[]" class="form-control form-control-sm jumlah-pinjam" min="1" value="1" onchange="validasiJumlah(this, 1)">
                                        </td>
                                        <td class="text-center" data-label="Aksi">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)" title="Hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Tombol Tambah Barang untuk Mobile (bawah tabel) -->
                        <div class="btn-tambah-mobile" style="margin-top: 10px;">
                            <button type="button" class="btn btn-success btn-block" onclick="tambahBaris()">
                                <i class="fas fa-plus"></i> TAMBAH BARANG LAGI
                            </button>
                        </div>
                        
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Klik gambar untuk memperbesar. Klik tombol <i class="fas fa-plus text-success"></i> untuk menambah barang.
                        </small>
                    </div>
                    
                    <div class="btn-group-custom">
                        <button type="reset" class="btn btn-default">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" name="simpan_cetak" class="btn btn-success">
                            <i class="fas fa-print"></i> SIMPAN & CETAK STRUK
                        </button>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-image"></i> Preview Gambar Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImg" src="" class="img-fluid rounded" style="max-height: 300px; max-width: 100%;">
                <p id="previewNama" class="mt-2 text-muted"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Data barang dari PHP
var dataBarang = {};

<?php 
$barang_query3 = mysqli_query($koneksi, "
    SELECT b.*, 
           COALESCE(SUM(CASE WHEN p.status = 'Dipinjam' THEN p.jumlah_pinjam ELSE 0 END), 0) AS dipinjam
    FROM tbl_barang b
    LEFT JOIN tbl_pinjaman p ON b.id_brg = p.id_brg
    GROUP BY b.id_brg
    HAVING (b.jumlah_brg - dipinjam) > 0
    ORDER BY b.nama_brg ASC
");
while ($brg = mysqli_fetch_array($barang_query3)): 
    $stok_tersedia = $brg['jumlah_brg'] - $brg['dipinjam'];
    $gambar = !empty($brg['gambar_brg']) ? $brg['gambar_brg'] : 'default.png';
?>
    dataBarang['<?= $brg['id_brg']; ?>'] = {
        stok: <?= $stok_tersedia; ?>,
        spesifikasi: '<?= addslashes($brg['spesifikasi_brg']); ?>',
        merk: '<?= addslashes($brg['merk_brg']); ?>',
        gambar: '<?= $gambar; ?>',
        nama: '<?= addslashes($brg['nama_brg']); ?>'
    };
<?php endwhile; ?>

var barisCount = 1;

// Fungsi preview gambar
function previewGambar(imgElement) {
    var gambarUrl = imgElement.src;
    var namaBarang = imgElement.getAttribute('data-nama') || 'Gambar Barang';
    document.getElementById('previewImg').src = gambarUrl;
    document.getElementById('previewNama').innerHTML = namaBarang;
    $('#modalPreviewGambar').modal('show');
}

// Update barang saat dipilih
function updateBarang(selectElement, rowId) {
    var idBarang = selectElement.value;
    var spesifikasiCell = document.getElementById('spesifikasi_' + rowId);
    var merkCell = document.getElementById('merk_' + rowId);
    var stokCell = document.getElementById('stok_' + rowId);
    var jumlahInput = document.querySelector('#baris' + rowId + ' .jumlah-pinjam');
    var gambarImg = document.getElementById('gambar_' + rowId);
    
    if (idBarang && dataBarang[idBarang]) {
        var data = dataBarang[idBarang];
        spesifikasiCell.innerHTML = data.spesifikasi || '-';
        merkCell.innerHTML = data.merk || '-';
        stokCell.innerHTML = '<span class="badge badge-success">' + data.stok + '</span>';
        jumlahInput.max = data.stok;
        
        // Set gambar
        var gambarPath = 'dist/upload_img/' + data.gambar;
        gambarImg.src = gambarPath;
        gambarImg.style.display = 'inline-block';
        gambarImg.setAttribute('data-nama', data.nama);
        gambarImg.style.cursor = 'pointer';
        
        // Validasi jumlah
        if (parseInt(jumlahInput.value) > data.stok) {
            jumlahInput.value = data.stok;
        }
        if (parseInt(jumlahInput.value) < 1) {
            jumlahInput.value = 1;
        }
    } else {
        spesifikasiCell.innerHTML = '-';
        merkCell.innerHTML = '-';
        stokCell.innerHTML = '-';
        gambarImg.style.display = 'none';
        gambarImg.src = 'dist/upload_img/default.png';
        jumlahInput.max = 9999;
    }
}

// Validasi jumlah
function validasiJumlah(inputElement, rowId) {
    var selectBarang = document.querySelector('#baris' + rowId + ' .select-barang');
    var idBarang = selectBarang.value;
    var jumlah = parseInt(inputElement.value);
    
    if (idBarang && dataBarang[idBarang]) {
        var maxStok = dataBarang[idBarang].stok;
        if (isNaN(jumlah) || jumlah > maxStok) {
            alert('Jumlah pinjam tidak boleh melebihi stok (' + maxStok + ')');
            inputElement.value = maxStok;
        }
        if (jumlah < 1 || isNaN(jumlah)) {
            inputElement.value = 1;
        }
    } else if (jumlah < 1 || isNaN(jumlah)) {
        inputElement.value = 1;
    }
}

// Tambah baris baru
function tambahBaris() {
    barisCount++;
    var tbody = document.getElementById('tbodyBarang');
    var newRow = document.createElement('tr');
    newRow.id = 'baris' + barisCount;
    
    // Buat option HTML
    var optionsHtml = '<option value="">-- Pilih Barang --</option>';
    <?php 
    $barang_query4 = mysqli_query($koneksi, "
        SELECT b.*, 
               COALESCE(SUM(CASE WHEN p.status = 'Dipinjam' THEN p.jumlah_pinjam ELSE 0 END), 0) AS dipinjam
        FROM tbl_barang b
        LEFT JOIN tbl_pinjaman p ON b.id_brg = p.id_brg
        GROUP BY b.id_brg
        HAVING (b.jumlah_brg - dipinjam) > 0
        ORDER BY b.nama_brg ASC
    ");
    while ($brg = mysqli_fetch_array($barang_query4)): 
        $stok_tersedia = $brg['jumlah_brg'] - $brg['dipinjam'];
        $gambar = !empty($brg['gambar_brg']) ? $brg['gambar_brg'] : 'default.png';
    ?>
        optionsHtml += '<option value="<?= $brg['id_brg']; ?>" data-stok="<?= $stok_tersedia; ?>" data-spesifikasi="<?= addslashes($brg['spesifikasi_brg']); ?>" data-merk="<?= addslashes($brg['merk_brg']); ?>" data-gambar="<?= $gambar; ?>" data-nama="<?= addslashes($brg['nama_brg']); ?>"><?= addslashes($brg['nama_brg']); ?> (Stok: <?= $stok_tersedia; ?>)</option>';
    <?php endwhile; ?>
    
    newRow.innerHTML = `
        <td class="text-center" data-label="No. Urut">${barisCount}</td>
        <td class="text-center" data-label="Gambar">
            <img src="dist/upload_img/default.png" 
                 class="barang-gambar" 
                 id="gambar_${barisCount}"
                 alt="Gambar Barang"
                 style="display:none;cursor:pointer;"
                 onclick="previewGambar(this)">
        </td>
        <td data-label="Nama Barang">
            <select name="barang_ids[]" class="form-control form-control-sm select-barang" id="select_${barisCount}" onchange="updateBarang(this, ${barisCount})" required>
                ${optionsHtml}
            </select>
        </td>
        <td class="spesifikasi-cell" id="spesifikasi_${barisCount}" data-label="Spesifikasi">-</td>
        <td class="merk-cell" id="merk_${barisCount}" data-label="Merk">-</td>
        <td class="stok-cell text-center" id="stok_${barisCount}" data-label="Stok Tersedia">-</td>
        <td data-label="Jumlah Pinjam">
            <input type="number" name="jumlah_pinjam[]" class="form-control form-control-sm jumlah-pinjam" min="1" value="1" onchange="validasiJumlah(this, ${barisCount})">
        </td>
        <td class="text-center" data-label="Aksi">
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)" title="Hapus">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Scroll ke baris baru
    newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Hapus baris
function hapusBaris(button) {
    var row = button.closest('tr');
    var tbody = document.getElementById('tbodyBarang');
    
    if (tbody.children.length > 1) {
        row.remove();
        // Update nomor urut
        var rows = tbody.children;
        for (var i = 0; i < rows.length; i++) {
            rows[i].cells[0].innerHTML = i + 1;
        }
    } else {
        alert('Minimal harus ada 1 barang yang dipinjam!');
    }
}

// Set tanggal perkiraan kembali otomatis
document.getElementById('tgl_pinjam').addEventListener('change', function() {
    var tglPinjam = this.value;
    if (tglPinjam) {
        var date = new Date(tglPinjam);
        date.setDate(date.getDate() + 7);
        var tahun = date.getFullYear();
        var bulan = String(date.getMonth() + 1).padStart(2, '0');
        var hari = String(date.getDate()).padStart(2, '0');
        document.getElementById('tgl_perkiraan_balik').value = tahun + '-' + bulan + '-' + hari;
    }
});

// Inisialisasi baris pertama saat halaman load
$(document).ready(function() {
    var firstSelect = document.getElementById('select_1');
    if (firstSelect && firstSelect.value) {
        updateBarang(firstSelect, 1);
    }
});

// Validasi sebelum submit
document.getElementById('formMultiPinjam').addEventListener('submit', function(e) {
    var rows = document.querySelectorAll('#tbodyBarang tr');
    var hasEmpty = false;
    
    for (var i = 0; i < rows.length; i++) {
        var select = rows[i].querySelector('.select-barang');
        if (!select.value) {
            e.preventDefault();
            hasEmpty = true;
            alert('Baris ' + (i + 1) + ': Silakan pilih barang terlebih dahulu!');
            break;
        }
    }
    
    // Cek apakah ada user terpilih
    var idUser = document.getElementById('id_user').value;
    if (!hasEmpty && !idUser) {
        e.preventDefault();
        alert('Silakan pilih peminjam terlebih dahulu!');
    }
});
</script>