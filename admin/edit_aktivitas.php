<?php
include "koneksi.php";

// Ambil ID dari URL
$id_history = isset($_GET['id']) ? $_GET['id'] : 0;

// Proses update data
if (isset($_POST['update'])) {
    $tujuan = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $jumlah_baru = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    
    // Ambil data lama sebelum update
    $query_lama = mysqli_query($koneksi, "SELECT id_brg, jumlah_brg FROM tbl_history WHERE id_history = '$id_history'");
    $data_lama = mysqli_fetch_assoc($query_lama);
    $jumlah_lama = $data_lama['jumlah_brg'];
    $id_brg = $data_lama['id_brg'];
    
    // Hitung selisih jumlah
    $selisih = $jumlah_lama - $jumlah_baru;
    
    // Update data di tbl_history
    $query = mysqli_query($koneksi, "UPDATE tbl_history SET jumlah_brg = '$jumlah_baru', tgl_history = '$tanggal' WHERE id_history = '$id_history'");
    
    // Update stok di tbl_barang berdasarkan selisih
    if ($selisih > 0) {
        // Jika jumlah baru lebih kecil, stok bertambah
        $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg + $selisih WHERE id_brg = '$id_brg'");
    } elseif ($selisih < 0) {
        // Jika jumlah baru lebih besar, stok berkurang
        $stok_sekarang = mysqli_query($koneksi, "SELECT jumlah_brg FROM tbl_barang WHERE id_brg = '$id_brg'");
        $stok = mysqli_fetch_assoc($stok_sekarang);
        $stok_tersedia = $stok['jumlah_brg'];
        $kekurangan = abs($selisih);
        
        if ($stok_tersedia >= $kekurangan) {
            $update_stok = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg = jumlah_brg - $kekurangan WHERE id_brg = '$id_brg'");
        } else {
            echo "<script>alert('Stok tidak mencukupi! Stok tersedia: $stok_tersedia, kekurangan: $kekurangan'); window.location.href='?page=edit_aktivitas&id=$id_history';</script>";
            exit;
        }
    } else {
        $update_stok = true;
    }
    
    // Update tujuan di tbl_ambil
    $getData = mysqli_query($koneksi, "SELECT id_brg, id_user FROM tbl_history WHERE id_history = '$id_history'");
    $dataHistory = mysqli_fetch_assoc($getData);
    
    if ($dataHistory) {
        $query2 = mysqli_query($koneksi, "UPDATE tbl_ambil SET tujuan_gunabarang = '$tujuan' WHERE id_brg = '{$dataHistory['id_brg']}' AND id_user = '{$dataHistory['id_user']}'");
    } else {
        $query2 = true;
    }
    
    if ($query && $query2 && $update_stok) {
        echo "<script>alert('Data berhasil diupdate! Stok barang juga telah disesuaikan.'); window.location.href='?page=aktifitas';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data: " . mysqli_error($koneksi) . "');</script>";
    }
}

// Ambil data berdasarkan ID
$sql = mysqli_query($koneksi, "
    SELECT h.*, 
        u.nama_lengkap, 
        b.nama_brg, 
        b.spesifikasi_brg, 
        b.merk_brg,
        b.jumlah_brg as stok_sekarang,
        a.tujuan_gunabarang
    FROM tbl_history h
    JOIN tb_user u ON h.id_user = u.id_user
    JOIN tbl_barang b ON h.id_brg = b.id_brg
    LEFT JOIN tbl_ambil a ON h.id_brg = a.id_brg AND h.id_user = a.id_user
    WHERE h.id_history = '$id_history'
");
$data = mysqli_fetch_assoc($sql);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='?page=aktifitas';</script>";
    exit;
}

// Format tanggal untuk input (Y-m-d)
$tanggal_db = $data['tgl_history'];
$jumlah_ambil = $data['jumlah_brg'];
$stok_saat_ini = $data['stok_sekarang'];
$stok_setelah_update = $stok_saat_ini + $jumlah_ambil;
?>

<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* ========== STYLE FORM ========== */
* {
    box-sizing: border-box;
}

html, body {
    height: auto !important;
    overflow-x: hidden;
    background: #f4f6f9;
}

/* Container utama */
.edit-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 15px;
}

/* Card Style */
.card-custom {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px 25px;
}

.card-header-custom h4 {
    margin: 0;
    color: white;
    font-weight: bold;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body-custom {
    padding: 30px;
}

/* Info Box */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: bold;
    color: #6c757d;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    word-break: break-word;
}

.info-value strong {
    color: #007bff;
}

/* Stok Info */
.stok-info {
    background: #e3f2fd;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.stok-item {
    text-align: center;
    flex: 1;
}

.stok-label {
    font-size: 11px;
    color: #1565c0;
    font-weight: bold;
    text-transform: uppercase;
}

.stok-value {
    font-size: 18px;
    font-weight: bold;
    color: #0d47a1;
}

.stok-value.warning {
    color: #dc3545;
}

/* Form Style */
.form-group {
    margin-bottom: 25px;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.form-group label i {
    color: #007bff;
    width: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
    border-color: #e0e0e0;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Button Group */
.button-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-submit {
    flex: 1;
    background: linear-gradient(45deg, #28a745, #00c851);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit:hover {
    background: linear-gradient(45deg, #218838, #00994d);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-back {
    flex: 1;
    background: linear-gradient(45deg, #6c757d, #5a6268);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-back:hover {
    background: linear-gradient(45deg, #5a6268, #4e555b);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

/* Alert */
.alert-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #1565c0;
}

.alert-info i {
    margin-right: 8px;
}

.alert-warning {
    background: #fff3e0;
    border-left: 4px solid #ff9800;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #e65100;
}

/* Responsive Mobile */
@media screen and (max-width: 768px) {
    .edit-container {
        padding: 10px;
    }
    
    .card-header-custom {
        padding: 15px 20px;
    }
    
    .card-header-custom h4 {
        font-size: 16px;
    }
    
    .card-body-custom {
        padding: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 12px;
        padding: 15px;
    }
    
    .info-label {
        font-size: 10px;
    }
    
    .info-value {
        font-size: 13px;
    }
    
    .stok-info {
        flex-direction: column;
        text-align: center;
    }
    
    .stok-value {
        font-size: 16px;
    }
    
    .form-group label {
        font-size: 13px;
    }
    
    .form-control {
        padding: 10px 12px;
        font-size: 13px;
    }
    
    .btn-submit, .btn-back {
        padding: 10px 20px;
        font-size: 13px;
    }
    
    .button-group {
        flex-direction: column;
        gap: 10px;
    }
}

/* Tablet */
@media screen and (min-width: 769px) and (max-width: 1024px) {
    .edit-container {
        max-width: 90%;
    }
    
    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="edit-container">
    <div class="card-custom">
        <div class="card-header-custom">
            <h4>
                <i class="fas fa-edit"></i> 
                Edit Riwayat Ambil Barang
            </h4>
        </div>
        <div class="card-body-custom">
            
            <!-- Alert Info -->
            <div class="alert-info">
                <i class="fas fa-info-circle"></i> 
                Jika Anda mengubah jumlah barang, stok barang akan otomatis menyesuaikan.
            </div>
            
            <!-- Alert Stok -->
            <div class="alert-warning">
                <i class="fas fa-boxes"></i> 
                <strong>Informasi Stok:</strong> Stok saat ini: <?= number_format($stok_saat_ini); ?> pcs | 
                Jika jumlah ambil diubah menjadi <span id="previewJumlah"><?= $jumlah_ambil; ?></span> pcs, 
                stok akan menjadi <span id="previewStok"><?= $stok_setelah_update; ?></span> pcs
            </div>
            
            <!-- Info Data -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-hashtag"></i> ID HISTORY</span>
                    <span class="info-value"><strong><?= $data['id_history']; ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-user"></i> NAMA USER</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama_lengkap']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-barcode"></i> ID BARANG</span>
                    <span class="info-value"><?= $data['id_brg']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-box"></i> NAMA BARANG</span>
                    <span class="info-value"><?= htmlspecialchars($data['nama_brg']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-microchip"></i> SPESIFIKASI</span>
                    <span class="info-value"><?= htmlspecialchars($data['spesifikasi_brg']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-tag"></i> MERK</span>
                    <span class="info-value"><?= htmlspecialchars($data['merk_brg']); ?></span>
                </div>
            </div>
            
            <!-- Form Edit -->
            <form method="POST" action="" id="editForm">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Tanggal Ambil</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= $tanggal_db; ?>">
                    <small style="color: #6c757d; font-size: 11px;">Anda dapat mengubah tanggal jika diperlukan</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-boxes"></i> Jumlah Barang (yang diambil)</label>
                    <input type="number" name="jumlah" id="jumlahBarang" class="form-control" value="<?= $jumlah_ambil; ?>" required min="1">
                    <small style="color: #6c757d; font-size: 11px;">Jumlah barang yang diambil</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-location-dot"></i> Tujuan Penggunaan</label>
                    <textarea name="tujuan" class="form-control" rows="3" required placeholder="Masukkan tujuan penggunaan barang..."><?= htmlspecialchars($data['tujuan_gunabarang'] ?? ''); ?></textarea>
                </div>
                
                <div class="button-group">
                    <button type="submit" name="update" class="btn-submit">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="?page=aktifitas" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview stok saat jumlah diubah
const jumlahInput = document.getElementById('jumlahBarang');
const previewJumlah = document.getElementById('previewJumlah');
const previewStok = document.getElementById('previewStok');

const stokSaatIni = <?= $stok_saat_ini; ?>;
const jumlahLama = <?= $jumlah_ambil; ?>;

function updatePreview() {
    let jumlahBaru = parseInt(jumlahInput.value) || 0;
    previewJumlah.textContent = jumlahBaru;
    
    // Hitung stok baru: stok saat ini + jumlah lama - jumlah baru
    let stokBaru = stokSaatIni + jumlahLama - jumlahBaru;
    previewStok.textContent = stokBaru;
    
    // Warning jika stok negatif
    if (stokBaru < 0) {
        previewStok.style.color = '#dc3545';
        previewStok.style.fontWeight = 'bold';
    } else {
        previewStok.style.color = '#0d47a1';
        previewStok.style.fontWeight = 'bold';
    }
}

jumlahInput.addEventListener('input', updatePreview);
updatePreview();

// Validasi form sebelum submit
document.getElementById('editForm').addEventListener('submit', function(e) {
    var jumlah = parseInt(jumlahInput.value);
    var tujuan = document.querySelector('textarea[name="tujuan"]').value;
    let stokBaru = stokSaatIni + jumlahLama - jumlah;
    
    if (jumlah <= 0) {
        e.preventDefault();
        alert('Jumlah barang harus lebih dari 0!');
        return false;
    }
    
    if (tujuan.trim() === '') {
        e.preventDefault();
        alert('Tujuan penggunaan harus diisi!');
        return false;
    }
    
    if (stokBaru < 0) {
        e.preventDefault();
        alert('Jumlah tidak valid! Stok akan menjadi negatif. Stok saat ini: ' + stokSaatIni + ', Jumlah ambil: ' + jumlah + ', Stok setelah: ' + stokBaru);
        return false;
    }
    
    return true;
});
</script>