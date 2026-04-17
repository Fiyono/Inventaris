<?php
include "../../koneksi.php"; // Sesuaikan path

// Set header JSON
header('Content-Type: application/json');

// Cek koneksi database
if (!$koneksi) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]);
    exit;
}

// Terima parameter ID
$id_user = isset($_POST['id']) ? mysqli_real_escape_string($koneksi, $_POST['id']) : '';

if (empty($id_user)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID User tidak ditemukan'
    ]);
    exit;
}

// Query ambil data user dengan JOIN yang benar
$query = "SELECT 
            u.id_user,
            u.nama_lengkap,
            u.user,
            u.email,
            u.no_whatsapp,
            u.id_level,
            u.id_organisasi,
            l.name_level,
            o.nama_organisasi
          FROM tb_user u
          LEFT JOIN leveluser l ON l.id_level = u.id_level
          LEFT JOIN tbl_organisasi o ON o.id_organisasi = u.id_organisasi
          WHERE u.id_user = '$id_user'";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . mysqli_error($koneksi)
    ]);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    
    // Format response
    $response = [
        'status' => 'success',
        'data' => [
            'id_user' => $data['id_user'],
            'nama_lengkap' => $data['nama_lengkap'],
            'user' => $data['user'],
            'email' => $data['email'],
            'no_whatsapp' => $data['no_whatsapp'],
            'id_level' => $data['name_level'], // Kirim nama level, bukan ID
            'id_organisasi' => $data['nama_organisasi'] // Kirim nama organisasi, bukan ID
        ]
    ];
    
    echo json_encode($response);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data user tidak ditemukan'
    ]);
}
?>