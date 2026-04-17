<?php
include "../../koneksi.php"; // Sesuaikan path

header('Content-Type: application/json');

// Ambil data dari form
$id_user = isset($_POST['id_user']) ? mysqli_real_escape_string($koneksi, $_POST['id_user']) : '';
$nama = isset($_POST['nama']) ? mysqli_real_escape_string($koneksi, $_POST['nama']) : '';
$user = isset($_POST['user']) ? mysqli_real_escape_string($koneksi, $_POST['user']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : '';
$hp = isset($_POST['hp']) ? mysqli_real_escape_string($koneksi, $_POST['hp']) : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$id_organisasi = isset($_POST['id_organisasi']) ? mysqli_real_escape_string($koneksi, $_POST['id_organisasi']) : '';
$id_level = isset($_POST['id_level']) ? mysqli_real_escape_string($koneksi, $_POST['id_level']) : '';

// Validasi data
if (empty($id_user) || empty($nama) || empty($user) || empty($id_organisasi) || empty($id_level)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

// Cek apakah username sudah dipakai user lain
$check = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE user = '$user' AND id_user != '$id_user'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username sudah digunakan'
    ]);
    exit;
}

// Query update data
if (!empty($pass)) {
    // Jika password diisi, update termasuk password
    $pass_md5 = md5($pass);
    $query = "UPDATE tb_user SET 
                nama_lengkap = '$nama',
                user = '$user',
                email = '$email',
                no_whatsapp = '$hp',
                password = '$pass_md5',
                id_organisasi = '$id_organisasi',
                id_level = '$id_level'
              WHERE id_user = '$id_user'";
} else {
    // Jika password kosong, update tanpa password
    $query = "UPDATE tb_user SET 
                nama_lengkap = '$nama',
                user = '$user',
                email = '$email',
                no_whatsapp = '$hp',
                id_organisasi = '$id_organisasi',
                id_level = '$id_level'
              WHERE id_user = '$id_user'";
}

$result = mysqli_query($koneksi, $query);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Data user berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal update: ' . mysqli_error($koneksi)
    ]);
}
?>