<?php
include "koneksi.php";

// Nama file yang akan diunduh
$filename = "riwayat_pinjam_" . date('Y-m-d') . ".xls";

// Set header agar langsung diunduh
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Judul kolom
echo "
<table border='1'>
    <tr style='background-color:#007bff; color:white; font-weight:bold; text-align:center;'>
        <th>NO</th>
        <th>NAMA PEMINJAM</th>
        <th>NAMA BARANG</th>
        <th>JUMLAH PINJAM</th>
        <th>JUMLAH KEMBALI</th>
        <th>TUJUAN PENGGUNAAN</th>
        <th>TANGGAL PINJAM</th>
        <th>TANGGAL KEMBALI</th>
    </tr>
";

// Ambil data dari database
$no = 1;
$query = mysqli_query($koneksi, "
    SELECT h.*, u.nama_lengkap, b.nama_brg
    FROM tbl_history_pinjam h
    LEFT JOIN tb_user u ON h.id_user = u.id_user
    LEFT JOIN tbl_barang b ON h.id_brg = b.id_brg
    ORDER BY h.id_histpinjam DESC
");

while ($row = mysqli_fetch_assoc($query)) {
    $tglKembali = ($row['tgl_kembali'] == "0000-00-00" || $row['tgl_kembali'] == "0000-00-00 00:00:00")
        ? "-" : $row['tgl_kembali'];

    echo "
    <tr>
        <td>{$no}</td>
        <td>{$row['nama_lengkap']}</td>
        <td>{$row['nama_brg']}</td>
        <td>{$row['jumlahbrg_pinjam']}</td>
        <td>{$row['jumlahbrg_kembali']}</td>
        <td>{$row['tujuan_gunabarang']}</td>
        <td>{$row['tgl_pinjam']}</td>
        <td>{$tglKembali}</td>
    </tr>
    ";
    $no++;
}

echo "</table>";
?>
