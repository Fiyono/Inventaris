<?php
// ========== AMBIL DATA USER YANG SEDANG LOGIN ==========
// Pastikan session sudah dimulai (di file utama sudah ada session_start())
// dan koneksi sudah tersedia

// Ambil id_user dari session (dari index.php atau admin.php)
if (!isset($id_user) && isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
}

// Query untuk mengambil data user berdasarkan id_user yang login
if (isset($id_user) && $id_user > 0) {
    $query_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user = '$id_user' LIMIT 1");
    $show = mysqli_fetch_assoc($query_user);
    
    // Jika data tidak ditemukan, redirect ke login
    if (!$show) {
        echo "<script>alert('Sesi tidak valid, silakan login kembali'); window.location.href='login.php';</script>";
        exit;
    }
} else {
    // Jika tidak ada id_user, redirect ke login
    echo "<script>alert('Silakan login terlebih dahulu'); window.location.href='login.php';</script>";
    exit;
}

// Set default values untuk kolom yang mungkin kosong
$show['position'] = $show['position'] ?? '-';
$show['img_profile'] = $show['img_profile'] ?? 'default.png';
$show['temp_lahir'] = $show['temp_lahir'] ?? '-';
$show['tgl_lahir'] = $show['tgl_lahir'] ?? '-';
$show['alamat_sekarang'] = $show['alamat_sekarang'] ?? '-';
$show['email'] = $show['email'] ?? '-';
$show['user'] = $show['user'] ?? '-';
$show['pass'] = $show['pass'] ?? '';
$show['nama_lengkap'] = $show['nama_lengkap'] ?? '-';
// ========== END AMBIL DATA USER ==========

// Memproses formulir jika disubmit
if (isset($_POST['savedata'])) {
    $id_user = $_POST['id_user'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $tempat = $_POST['tempat'];
    $tgllahir = $_POST['tgllahir'];
    $alamat = $_POST['alamat'];

    // Gunakan prepared statements untuk keamanan
    $sql = "UPDATE tb_user SET user = ?, pass = ?, email = ?, temp_lahir = ?, tgl_lahir = ?, alamat_sekarang = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssi", $username, $password, $email, $tempat, $tgllahir, $alamat, $id_user);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo "<script>alert('Data berhasil disimpan'); document.location.href = 'admin.php?page=profile';</script>";
    } else {
        echo "<script>alert('Data gagal disimpan'); document.location.href = 'admin.php?page=profile';</script>";
    }
} else if (isset($_POST['simpannama'])) {
    $id_user = $_POST['id_user'];
    $nama_lengkap = $_POST['nama_lengkap'];

    $sql = "UPDATE tb_user SET nama_lengkap = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nama_lengkap, $id_user);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo "<script>alert('Data Nama berhasil disimpan'); document.location.href = 'admin.php?page=profile';</script>";
    } else {
        echo "<script>alert('Data Nama gagal disimpan'); document.location.href = 'admin.php?page=profile';</script>";
    }
}
?>

<link rel="stylesheet" href="assets/css/style_profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* ========== RESET & BASE ========== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
}

/* ========== CONTAINER UTAMA ========== */
.profile-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

/* ========== PROFILE CARD ========== */
.profile-card {
    max-width: 550px;
    width: 100%;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.profile-card:hover {
    transform: translateY(-5px);
}

/* ========== HEADER DENGAN BACKGROUND GRADIENT ========== */
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px 20px;
    text-align: center;
    position: relative;
}

/* ========== FOTO PROFIL - BULAT DAN RAPI ========== */
.profile-image-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 15px;
}

.profile-image {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    background: white;
}

.edit-photo-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.edit-photo-btn i {
    color: #667eea;
    font-size: 14px;
}

.edit-photo-btn:hover {
    transform: scale(1.1);
    background: #667eea;
}

.edit-photo-btn:hover i {
    color: white;
}

/* ========== NAMA DAN POSISI ========== */
.profile-name-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.profile-name {
    font-size: 1.6rem;
    font-weight: 600;
    color: white;
    margin: 0;
}

.edit-name-btn {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-name-btn i {
    color: white;
    font-size: 14px;
}

.edit-name-btn:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.1);
}

.profile-position {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 5px;
}

/* ========== DETAIL PROFIL ========== */
.profile-details {
    padding: 25px;
    background: white;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-icon {
    width: 40px;
    flex-shrink: 0;
}

.detail-icon i {
    font-size: 20px;
    color: #667eea;
}

.detail-content {
    flex: 1;
}

.detail-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6c757d;
    margin-bottom: 5px;
    display: block;
}

.detail-value {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 500;
    word-break: break-word;
}

/* ========== TOMBOL EDIT PROFIL ========== */
.profile-actions {
    padding: 0 25px 25px 25px;
    background: white;
}

.btn-edit-profile {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-edit-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

/* ========== MODAL ========== */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    max-height: 85vh;
    overflow-y: auto;
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
}

/* ========== FORM GROUP ========== */
.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.show-password-container {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.show-password-container input {
    width: auto;
}

/* ========== TOMBOL MODAL ========== */
.btn-close {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: #5a6268;
}

.btn-save {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
}

/* ========== RESPONSIVE MOBILE ========== */
@media screen and (max-width: 768px) {
    .profile-container {
        padding: 15px;
    }
    
    .profile-card {
        border-radius: 16px;
    }
    
    .profile-header {
        padding: 20px 15px;
    }
    
    .profile-image {
        width: 100px;
        height: 100px;
    }
    
    .profile-name {
        font-size: 1.3rem;
    }
    
    .profile-details {
        padding: 20px;
    }
    
    .detail-item {
        margin-bottom: 15px;
        padding-bottom: 12px;
    }
    
    .detail-icon {
        width: 35px;
    }
    
    .detail-icon i {
        font-size: 18px;
    }
    
    .detail-label {
        font-size: 0.7rem;
    }
    
    .detail-value {
        font-size: 0.9rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 10px;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .modal-footer {
        padding: 12px 15px;
    }
    
    .modal-footer .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>

<main class="profile-container">
    <article class="profile-card">
        <!-- HEADER PROFIL -->
        <section class="profile-header">
            <div class="profile-image-wrapper">
                <img class="profile-image" src="dist/upload_img/<?= htmlspecialchars($show['img_profile']); ?>" alt="User Avatar" id="user-image-display">
                <div class="edit-photo-btn" data-modal="modal-profile">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            
            <div class="profile-name-section">
                <h6 class="profile-name"><?= htmlspecialchars($show['nama_lengkap']); ?></h6>
                <div class="edit-name-btn" data-modal="modal-name">
                    <i class="fas fa-pencil-alt"></i>
                </div>
            </div>
            <p class="profile-position"><?= htmlspecialchars($show['position']); ?></p>
        </section>

        <!-- DETAIL PROFIL -->
        <section class="profile-details">
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="detail-content">
                    <span class="detail-label">USERNAME</span>
                    <span class="detail-value"><?= htmlspecialchars($show['user']); ?></span>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="detail-content">
                    <span class="detail-label">PASSWORD</span>
                    <span class="detail-value">••••••••</span>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="detail-content">
                    <span class="detail-label">EMAIL</span>
                    <span class="detail-value"><?= htmlspecialchars($show['email']); ?></span>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="detail-content">
                    <span class="detail-label">TEMPAT & TANGGAL LAHIR</span>
                    <span class="detail-value"><?= htmlspecialchars($show['temp_lahir'] . ", " . $show['tgl_lahir']); ?></span>
                </div>
            </div>
            
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="detail-content">
                    <span class="detail-label">ALAMAT LENGKAP</span>
                    <span class="detail-value"><?= nl2br(htmlspecialchars($show['alamat_sekarang'])); ?></span>
                </div>
            </div>
        </section>

        <!-- TOMBOL EDIT -->
        <section class="profile-actions">
            <button type="button" class="btn-edit-profile" data-modal="modal-edit">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
        </section>
    </article>
</main>

<!-- MODAL FOTO PROFIL -->
<div id="modal-profile" class="modal">
    <div class="modal-content">
        <form action="admin/proses/proses_simpanfoto_profile.php" method="post" enctype="multipart/form-data">
            <div class="modal-header">
                <h3><i class="fas fa-camera"></i> Ubah Foto Profil</h3>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_user" value="<?= htmlspecialchars($show['id_user']); ?>">
                <div class="form-group">
                    <label for="gambar">Pilih Foto Baru</label>
                    <input type="file" name="gambar" id="gambar" accept="image/*" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" onclick="closeModal('modal-profile')">Batal</button>
                <button type="submit" name="simpanprofile" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL UBAH NAMA -->
<div id="modal-name" class="modal">
    <div class="modal-content">
        <form action="" method="post">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Ubah Nama</h3>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_user" value="<?= htmlspecialchars($show['id_user']); ?>">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($show['nama_lengkap']); ?>">
                </div>
                <div class="form-group">
                    <label for="position">Posisi</label>
                    <input type="text" id="position" name="position" class="form-control" value="<?= htmlspecialchars($show['position']); ?>" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" onclick="closeModal('modal-name')">Batal</button>
                <button type="submit" name="simpannama" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT DETAIL PROFIL -->
<div id="modal-edit" class="modal">
    <div class="modal-content">
        <form action="" method="post">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Detail Profil</h3>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_user" value="<?= htmlspecialchars($show['id_user']); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($show['user']); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" value="<?= htmlspecialchars($show['pass']); ?>">
                    <div class="show-password-container">
                        <input type="checkbox" id="show-pass-checkbox">
                        <label for="show-pass-checkbox">Tampilkan password</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($show['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="tempat">Tempat Lahir</label>
                    <input type="text" id="tempat" name="tempat" class="form-control" value="<?= htmlspecialchars($show['temp_lahir']); ?>">
                </div>
                <div class="form-group">
                    <label for="tgllahir">Tanggal Lahir</label>
                    <input type="date" id="tgllahir" name="tgllahir" class="form-control" value="<?= htmlspecialchars($show['tgl_lahir']); ?>">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="3"><?= htmlspecialchars($show['alamat_sekarang']); ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-close" onclick="closeModal('modal-edit')">Batal</button>
                <button type="submit" name="savedata" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fungsionalitas Modal
    document.addEventListener('DOMContentLoaded', () => {
        const modalTriggers = document.querySelectorAll('[data-modal]');
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modalId = trigger.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            });
        });
    });

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    window.addEventListener('click', (event) => {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });

    // Tampilkan/Sembunyikan Password
    const showPassCheckbox = document.getElementById('show-pass-checkbox');
    if (showPassCheckbox) {
        showPassCheckbox.addEventListener('change', (e) => {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.type = e.target.checked ? 'text' : 'password';
            }
        });
    }

    // Pratinjau Gambar
    const loadFile = function(event) {
        const output = document.getElementById('user-image-display');
        if (output && event.target.files && event.target.files[0]) {
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src);
            };
        }
    };
    
    const imageInput = document.getElementById('gambar');
    if (imageInput) {
        imageInput.addEventListener('change', loadFile);
    }
    
    // Tutup modal dengan ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        }
    });
</script>