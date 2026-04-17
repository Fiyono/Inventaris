<?php
include "../../koneksi.php"; // Sesuaikan path

header('Content-Type: application/json');

// Ambil data dari form
$nama = isset($_POST['nama']) ? mysqli_real_escape_string($koneksi, $_POST['nama']) : '';
$user = isset($_POST['user']) ? mysqli_real_escape_string($koneksi, $_POST['user']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : '';
$hp = isset($_POST['hp']) ? mysqli_real_escape_string($koneksi, $_POST['hp']) : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$id_organisasi = isset($_POST['id_organisasi']) ? mysqli_real_escape_string($koneksi, $_POST['id_organisasi']) : '';
$id_level = isset($_POST['id_level']) ? mysqli_real_escape_string($koneksi, $_POST['id_level']) : '';

// Validasi data
if (empty($nama) || empty($user) || empty($pass) || empty($id_organisasi) || empty($id_level)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap. Nama, Username, Password, Organisasi dan Level harus diisi'
    ]);
    exit;
}

// Cek apakah username sudah ada
$check = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE user = '$user'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username sudah digunakan'
    ]);
    exit;
}

// Enkripsi password
$pass_md5 = md5($pass);

// Insert data
$query = "INSERT INTO tb_user (nama_lengkap, user, email, no_whatsapp, password, id_organisasi, id_level) 
          VALUES ('$nama', '$user', '$email', '$hp', '$pass_md5', '$id_organisasi', '$id_level')";

$result = mysqli_query($koneksi, $query);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'User baru berhasil ditambahkan'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal tambah: ' . mysqli_error($koneksi)
    ]);
}
?>