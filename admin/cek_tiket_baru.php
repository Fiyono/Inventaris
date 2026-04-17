<?php
// Ganti dengan file koneksi database Anda
include 'koneksi.php'; 

// Query untuk menghitung tiket baru dengan status 'pending'
$query = "SELECT COUNT(*) AS total FROM tbl_tiket_masuk WHERE status_tiket = 'pending'";

$result = mysqli_query($koneksi, $query);
$row = mysqli_fetch_assoc($result);

// Mengirim jumlah total tiket sebagai respons JSON
header('Content-Type: application/json');
echo json_encode(['jumlah' => $row['total']]);

mysqli_close($koneksi);
?>