<?php
// File: admin/ajax/import_excel.php

require '../../vendor/autoload.php';

include "../../koneksi.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Fungsi untuk membersihkan input
function clean($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim($data));
}

// Cek koneksi
if (!$koneksi) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal'
    ]);
    exit;
}

// Cek file
if (!isset($_FILES['file_excel'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Tidak ada file yang diupload'
    ]);
    exit;
}

if ($_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error upload file: ' . $_FILES['file_excel']['error']
    ]);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$nama_file = $_FILES['file_excel']['name'];
$ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

// Validasi ekstensi
$allowed = ['xls', 'xlsx', 'csv'];
if (!in_array($ekstensi, $allowed)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Format file harus .xls, .xlsx, atau .csv'
    ]);
    exit;
}

try {
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    $row_number = 0;
    
    if ($ekstensi == 'csv') {
        // Proses CSV
        $handle = fopen($file, "r");
        if (!$handle) {
            throw new Exception("Gagal membuka file CSV");
        }
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row_number++;
            if ($row_number == 1) continue; // Skip header
            
            if (count($data) < 7) {
                $error_count++;
                $errors[] = "Baris $row_number: Jumlah kolom tidak lengkap";
                continue;
            }
            
            // Ambil data dari Excel
            $nama = clean($data[0]);
            $username = clean($data[1]);
            $password = md5($data[2]); // Enkripsi password
            $email = clean($data[3]);
            $hp = clean($data[4]);
            $level = clean($data[5]); // Langsung 'admin' atau 'user'
            $organisasi = clean($data[6]); // Langsung 'guru' atau 'murid'
            
            // Validasi data wajib
            if (empty($nama) || empty($username) || empty($data[2])) {
                $error_count++;
                $errors[] = "Baris $row_number: Nama, Username, Password wajib diisi";
                continue;
            }
            
            // Validasi level
            if (!in_array($level, ['admin', 'user'])) {
                $error_count++;
                $errors[] = "Baris $row_number: Level harus 'admin' atau 'user'";
                continue;
            }
            
            // Validasi organisasi
            if (!in_array($organisasi, ['guru', 'murid'])) {
                $error_count++;
                $errors[] = "Baris $row_number: Organisasi harus 'guru' atau 'murid'";
                continue;
            }
            
            // Cek username sudah ada?
            $cek = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE user = '$username'");
            if (mysqli_num_rows($cek) > 0) {
                $error_count++;
                $errors[] = "Baris $row_number: Username '$username' sudah ada";
                continue;
            }
            
            // INSERT ke database - perhatikan kolomnya sesuai struktur
            $insert = mysqli_query($koneksi, "INSERT INTO tb_user (
                nama_lengkap, 
                user, 
                pass, 
                email, 
                no_whatsapp, 
                id_level, 
                id_organisasi,
                position,
                temp_lahir,
                tgl_lahir,
                alamat_sekarang,
                img_profile
            ) VALUES (
                '$nama', 
                '$username', 
                '$password', 
                '$email', 
                '$hp', 
                '$level', 
                '$organisasi',
                '-',
                '-',
                '2000-01-01',
                '-',
                'default.jpg'
            )");
            
            if ($insert) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris $row_number: Gagal insert - " . mysqli_error($koneksi);
            }
        }
        fclose($handle);
        
    } else {
        // Proses Excel
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        foreach ($rows as $index => $row) {
            $row_number = $index + 1;
            if ($row_number == 1) continue; // Skip header
            
            if (count($row) < 7) {
                $error_count++;
                $errors[] = "Baris $row_number: Jumlah kolom tidak lengkap";
                continue;
            }
            
            // Ambil data
            $nama = clean($row[0]);
            $username = clean($row[1]);
            $password = md5($row[2]);
            $email = clean($row[3]);
            $hp = clean($row[4]);
            $level = clean($row[5]);
            $organisasi = clean($row[6]);
            
            // Validasi
            if (empty($nama) || empty($username) || empty($row[2])) {
                $error_count++;
                $errors[] = "Baris $row_number: Nama, Username, Password wajib diisi";
                continue;
            }
            
            // Validasi level
            if (!in_array($level, ['admin', 'user'])) {
                $error_count++;
                $errors[] = "Baris $row_number: Level harus 'admin' atau 'user'";
                continue;
            }
            
            // Validasi organisasi
            if (!in_array($organisasi, ['guru', 'murid'])) {
                $error_count++;
                $errors[] = "Baris $row_number: Organisasi harus 'guru' atau 'murid'";
                continue;
            }
            
            // Cek username
            $cek = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE user = '$username'");
            if (mysqli_num_rows($cek) > 0) {
                $error_count++;
                $errors[] = "Baris $row_number: Username '$username' sudah ada";
                continue;
            }
            
            // INSERT
            $insert = mysqli_query($koneksi, "INSERT INTO tb_user (
                nama_lengkap, user, pass, email, no_whatsapp, id_level, id_organisasi,
                position, temp_lahir, tgl_lahir, alamat_sekarang, img_profile
            ) VALUES (
                '$nama', '$username', '$password', '$email', '$hp', '$level', '$organisasi',
                '-', '-', '2000-01-01', '-', 'default.jpg'
            )");
            
            if ($insert) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Baris $row_number: Gagal insert - " . mysqli_error($koneksi);
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'success' => $success_count,
        'error' => $error_count,
        'errors' => $errors,
        'total' => $success_count + $error_count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>