<?php
// Hapus session_start() jika sudah ada di file utama
require_once("koneksi.php");
?>

<style>
/* --- MOBILE FRIENDLY STYLE (tambahan) --- */
@media (max-width: 768px) {
    .row > .col-sm-6 {
        width: 100%;
        padding: 0 8px;
    }
    
    h4.mb-3 {
        font-size: 1.2rem;
        padding: 0 8px;
    }
    
    .card {
        margin-bottom: 15px;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .text-end {
        text-align: center !important;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .text-end button {
        width: 100%;
        margin: 0 !important;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    input.form-control,
    select.form-control,
    textarea.form-control {
        font-size: 16px;
    }
    
    label {
        font-size: 0.9rem;
    }
    
    #previewImage {
        max-height: 120px;
    }
    
    .container-fluid {
        padding-left: 12px;
        padding-right: 12px;
    }
    
    .card-header {
        padding: 10px 12px;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
}

@media (min-width: 769px) {
    .text-end {
        text-align: right;
    }
}

/* --- PERBAIKAN BOX KAMERA (HILANGKAN BOX HITAM & PUTIH BERGANDA) --- */
.camera-container {
    position: relative;
    width: 100%;
    min-height: 250px;
    background: #f0f2f5;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

#my_camera {
    width: 100% !important;
    min-height: 250px !important;
    background: #f0f2f5 !important;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Style untuk video element dari webcam */
#my_camera video,
.webcam-container video {
    width: 100% !important;
    height: auto !important;
    max-width: 100% !important;
    border-radius: 8px;
}

/* Pesan ketika kamera tidak tersedia */
.camera-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 250px;
    background: #f8f9fa;
    border: 2px dashed #6c757d;
    border-radius: 8px;
    color: #6c757d;
}

.camera-placeholder i {
    font-size: 48px;
    margin-bottom: 10px;
}

.camera-placeholder p {
    margin: 0;
    font-size: 14px;
}
</style>

<div class="container-fluid">
    <h4 class="mb-3"><i class="fas fa-ticket-alt"></i> INPUT BARANG</h4>
    
    <form action="admin/proses/proses_inputbarang.php" method="post" enctype="multipart/form-data" id="formInputBarang">
    <div class="row">
        <!-- Kamera & Upload -->
        <div class="col-sm-6">
            <div class="card shadow-lg border-0 mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-camera"></i> FOTO BARANG
                </div>
                <div class="card-body text-center">
                    
                    <!-- Kamera dengan container - TANPA box hitam -->
                    <div class="camera-container">
                        <div id="my_camera"></div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary mt-2" id="btnAmbilFoto" style="display: none;">
                        <i class="fas fa-camera"></i> Ambil Foto
                    </button>

                    <input type="hidden" name="image" class="image-tag">

                    <hr>

                    <!-- Upload File -->
                    <div class="form-group">
                        <label class="fw-bold"><i class="fas fa-upload"></i> Upload Foto</label>
                        <input type="file" class="form-control" name="upload_foto" accept="image/*" onchange="previewFile(this)">
                        <small class="text-muted">Format: JPG, PNG, JPEG. Maks 2MB</small>
                    </div>

                    <!-- Preview Hasil -->
                    <div id="results" class="p-2 border rounded bg-light mt-2 text-center">
                        <img src="dist/img/no-image.png" id="previewImage" class="img-fluid rounded shadow" style="max-height: 150px;">
                        <p class="text-muted mt-1 mb-0">📷 Preview foto akan muncul di sini...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Input Barang -->
        <div class="col-sm-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-box-open"></i> DATA BARANG</h5>
                </div>
                <div class="card-body">
                    <!-- ID BARANG -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-barcode"></i> ID BARANG</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
                            <input type="text" class="form-control" name="id_brg" 
                                   placeholder="Masukkan ID Barang" required autofocus
                                   onblur="cekBarang()" id="id_brg_input">
                        </div>
                        <small class="text-muted" id="status_barang"></small>
                    </div>

                    <!-- NAMA BARANG -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-tag"></i> NAMA BARANG</label>
                        <input type="text" class="form-control" name="nama_brg" 
                               placeholder="Masukkan Nama Barang" required id="nama_brg_input">
                    </div>

                    <!-- SPESIFIKASI -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-file-alt"></i> SPESIFIKASI</label>
                        <textarea class="form-control" name="spesifikasi_brg" 
                                  placeholder="Tulis spesifikasi/type barang..." 
                                  rows="2" required id="spesifikasi_input"></textarea>
                    </div>

                    <!-- MERK -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-industry"></i> MERK BARANG</label>
                        <input type="text" class="form-control" name="merk_brg" 
                               placeholder="Masukkan Merk Barang" required id="merk_input">
                    </div>

                    <!-- NO RAK -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-archive"></i> NO RAK</label>
                        <input type="text" class="form-control" name="norak_brg" 
                               placeholder="Nomor Rak Penyimpanan" required>
                        <small class="text-muted">Akan dikonversi ke angka</small>
                    </div>

                    <!-- TANGGAL MASUK -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-calendar-alt"></i> TGL MASUK BARANG</label>
                        <input type="date" class="form-control" name="tgl_masuk_brg" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <!-- KATEGORI -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-list"></i> KATEGORI</label>
                        <select class="form-control" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php 
                                $sql_catg = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY nama_kategori");
                                while ($rcatg = mysqli_fetch_array($sql_catg)) {
                                    echo "<option value='".$rcatg['id_kategori']."'>".$rcatg['id_kategori'].". ".$rcatg['nama_kategori']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <!-- JUMLAH -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-boxes"></i> JUMLAH</label>
                        <input type="number" class="form-control" name="jumlah_brg" 
                               placeholder="Jumlah Barang" min="1" required>
                    </div>

                    <!-- KETERANGAN -->
                    <div class="form-group mb-3">
                        <label><i class="fas fa-sticky-note"></i> KETERANGAN</label>
                        <input type="text" class="form-control" name="keterangan" 
                               placeholder="Contoh: Request Tiket #001, Pembelian dari Supplier, dll" 
                               value="Request Tiket" required>
                    </div>

                    <!-- TOMBOL -->
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">
                            <i class="fas fa-undo"></i> RESET
                        </button>
                        <button class="btn btn-success" name="simpan" type="submit">
                            <i class="fas fa-save"></i> SIMPAN BARANG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<!-- Script Kamera & Upload -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>
// Cek apakah browser mendukung webcam dan ada webcam
function cekDukunganKamera() {
    return navigator.mediaDevices && navigator.mediaDevices.enumerateDevices && navigator.mediaDevices.getUserMedia;
}

// Inisialisasi Webcam hanya jika ada kamera
if (cekDukunganKamera()) {
    // Cek apakah ada perangkat kamera
    navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
            var hasCamera = devices.some(function(device) {
                return device.kind === 'videoinput';
            });
            
            if (hasCamera) {
                // Ada kamera, inisialisasi webcam
                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });
                
                Webcam.attach('#my_camera');
                document.getElementById('btnAmbilFoto').style.display = 'inline-block';
            } else {
                // Tidak ada kamera, tampilkan pesan
                document.getElementById('my_camera').innerHTML = 
                    '<div class="camera-placeholder">' +
                    '<i class="fas fa-video-slash"></i>' +
                    '<p><strong>Kamera tidak tersedia</strong></p>' +
                    '<p class="small">Silakan upload foto menggunakan tombol di bawah</p>' +
                    '</div>';
                document.getElementById('btnAmbilFoto').style.display = 'none';
            }
        })
        .catch(function(err) {
            console.log("Error enumerasi device: ", err);
            document.getElementById('my_camera').innerHTML = 
                '<div class="camera-placeholder">' +
                '<i class="fas fa-exclamation-triangle"></i>' +
                '<p><strong>Tidak dapat mengakses kamera</strong></p>' +
                '<p class="small">Silakan upload foto menggunakan tombol di bawah</p>' +
                '</div>';
        });
} else {
    // Browser tidak mendukung webcam API
    document.getElementById('my_camera').innerHTML = 
        '<div class="camera-placeholder">' +
        '<i class="fas fa-microphone-slash"></i>' +
        '<p><strong>Browser tidak mendukung Webcam</strong></p>' +
        '<p class="small">Silakan upload foto menggunakan tombol di bawah</p>' +
        '</div>';
    document.getElementById('btnAmbilFoto').style.display = 'none';
}

function take_snapshot() {
    Webcam.snap(function(data_uri) {
        document.querySelector(".image-tag").value = data_uri;
        document.getElementById('previewImage').src = data_uri;
        document.getElementById('previewImage').style.display = 'block';
    });
}

// Upload File
function previewFile(input){
    var file = input.files[0];
    if(file){
        // Validasi ukuran file (max 2MB)
        if(file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            input.value = '';
            return;
        }
        
        // Validasi tipe file
        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if(!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.');
            input.value = '';
            return;
        }
        
        var reader = new FileReader();
        reader.onload = function(e){
            document.querySelector(".image-tag").value = e.target.result;
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewImage').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

// Cek Ketersediaan ID Barang via AJAX
function cekBarang() {
    let id = document.getElementById("id_brg_input").value;
    let status = document.getElementById("status_barang");

    if(id === "") {
        status.innerHTML = "";
        return;
    }

    fetch("admin/ajax/cek_barang.php?id=" + id)
    .then(response => response.json())
    .then(data => {
        if(data.exists) {
            status.innerHTML = "❌ ID sudah digunakan. Stok sekarang: " + data.jumlah_brg;
            status.style.color = "red";
        } else {
            status.innerHTML = "✅ ID tersedia dan bisa digunakan";
            status.style.color = "green";
        }
    })
    .catch(error => {
        status.innerHTML = "Terjadi kesalahan!";
        status.style.color = "orange";
        console.log(error);
    });
}

// Clear form
function clearForm() {
    if(confirm('Yakin ingin mereset form?')) {
        document.getElementById('formInputBarang').reset();
        document.getElementById('previewImage').src = 'dist/img/no-image.png';
        document.getElementById('status_barang').innerHTML = '';
        document.querySelector(".image-tag").value = '';
        document.getElementById('id_brg_input').focus();
    }
}

// Tombol ambil foto
document.getElementById('btnAmbilFoto') && document.getElementById('btnAmbilFoto').addEventListener('click', function() {
    take_snapshot();
});

// Validasi sebelum submit
document.getElementById('formInputBarang').addEventListener('submit', function(e) {
    var id_brg = document.getElementById('id_brg_input').value;
    var tgl = document.querySelector('input[name="tgl_masuk_brg"]').value;
    var kategori = document.querySelector('select[name="id_kategori"]').value;
    var jumlah = document.querySelector('input[name="jumlah_brg"]').value;
    
    if(!id_brg) {
        e.preventDefault();
        alert('ID Barang harus diisi!');
        return false;
    }
    
    if(!tgl) {
        e.preventDefault();
        alert('Tanggal masuk harus diisi!');
        return false;
    }
    
    if(!kategori) {
        e.preventDefault();
        alert('Kategori harus dipilih!');
        return false;
    }
    
    if(jumlah < 1) {
        e.preventDefault();
        alert('Jumlah barang minimal 1!');
        return false;
    }
    
    return true;
});
</script>