<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_admin";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');

// cek apakah tabel tbl_pinjaman ada
$unik = 0;
$q = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_pinjaman'");
if (mysqli_num_rows($q) > 0) {
    $res = mysqli_query($koneksi, "SELECT MAX(id_pinjaman) AS kode FROM tbl_pinjaman");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $unik = (int)$row['kode'] + 1;
    }
}
// echo $unik;
?>
