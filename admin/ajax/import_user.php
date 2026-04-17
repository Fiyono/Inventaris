<?php
include "../../koneksi.php";

// Set header JSON
header('Content-Type: application/json');

// Nonaktifkan error reporting untuk menghindari output HTML
error_reporting(0);
ini_set('display_errors', 0);

// Cek apakah ada file yang diupload
if(!isset($_FILES['file_excel'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Tidak ada file yang diupload'
    ]);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$nama_file = $_FILES['file_excel']['name'];
$error_file = $_FILES['file_excel']['error'];

// Cek error upload
if($error_file !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error upload file: ' . $error_file
    ]);
    exit;
}

// Cek ekstensi file
$ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
if($ekstensi != 'csv') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hanya file CSV yang didukung. Simpan file Excel Anda sebagai CSV.'
    ]);
    exit;
}

// Baca file CSV
$handle = fopen($file, "r");
if(!$handle) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membuka file'
    ]);
    exit;
}

$success_count = 0;
$error_count = 0;
$errors = [];
$row = 0;

while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $row++;
    
    // Skip header (baris pertama)
    if($row == 1) continue;
    
    // Validasi jumlah kolom
    if(count($data) < 7) {
        $error_count++;
        $errors[] = "Baris $row: Jumlah kolom tidak lengkap";
        continue;
    }
    
    // Bersihkan data
    $nama = mysqli_real_escape_string($koneksi, trim($data[0]));
    $username = mysqli_real_escape_string($koneksi, trim($data[1]));
    $password = trim($data[2]);
    $email = mysqli_real_escape_string($koneksi, trim($data[3]));
    $hp = mysqli_real_escape_string($koneksi, trim($data[4]));
    $level_name = mysqli_real_escape_string($koneksi, trim($data[5]));
    $organisasi_name = mysqli_real_escape_string($koneksi, trim($data[6]));
    
    // Validasi data tidak kosong
    if(empty($nama) || empty($username) || empty($password)) {
        $error_count++;
        $errors[] = "Baris $row: Nama, Username, dan Password wajib diisi";
        continue;
    }
    
    // Enkripsi password (sesuaikan dengan metode enkripsi Anda)
    $password_encrypt = md5($password);
    
    // Cari ID Level berdasarkan nama
    $q_level = mysqli_query($koneksi, "SELECT id_level FROM leveluser WHERE LOWER(name_level) = LOWER('$level_name')");
    if(mysqli_num_rows($q_level) == 0) {
        $error_count++;
        $errors[] = "Baris $row: Level '$level_name' tidak ditemukan di database";
        continue;
    }
    $level = mysqli_fetch_assoc($q_level);
    $id_level = $level['id_level'];
    
    // Cari ID Organisasi berdasarkan nama
    $q_org = mysqli_query($koneksi, "SELECT id_organisasi FROM tbl_organisasi WHERE LOWER(nama_organisasi) = LOWER('$organisasi_name')");
    if(mysqli_num_rows($q_org) == 0) {
        $error_count++;
        $errors[] = "Baris $row: Organisasi '$organisasi_name' tidak ditemukan di database";
        continue;
    }
    $org = mysqli_fetch_assoc($q_org);
    $id_organisasi = $org['id_organisasi'];
    
    // Cek username sudah ada?
    $cek = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE user = '$username'");
    if(mysqli_num_rows($cek) > 0) {
        $error_count++;
        $errors[] = "Baris $row: Username '$username' sudah ada di database";
        continue;
    }
    
    // Insert data
    $insert = mysqli_query($koneksi, "INSERT INTO tb_user (nama_lengkap, user, password, email, no_whatsapp, id_level, id_organisasi) VALUES ('$nama', '$username', '$password_encrypt', '$email', '$hp', '$id_level', '$id_organisasi')");
    
    if($insert) {
        $success_count++;
    } else {
        $error_count++;
        $errors[] = "Baris $row: Gagal insert - " . mysqli_error($koneksi);
    }
}

fclose($handle);

// Kirim response JSON
echo json_encode([
    'status' => 'success',
    'success' => $success_count,
    'error' => $error_count,
    'errors' => $errors
]);
?>