<?php
include "koneksi.php";

// Nama file Excel
$filename = "Riwayat_Tambah_" . date("Y-m-d_H-i-s") . ".xls";

// Header Excel agar browser langsung download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Mulai tabel HTML
echo "<table border='1' cellspacing='0' cellpadding='5'>";

// Header tabel dengan warna biru
echo "<tr style='background-color:#007bff;color:white;'>
        <th>NO</th>
        <th>ID BARANG</th>
        <th>NAMA BARANG</th>
        <th>SPESIFIKASI</th>
        <th>MERK</th>
        <th>JUMLAH TAMBAH</th>
        <th>TANGGAL</th>
        <th>KETERANGAN</th>
      </tr>";

// Ambil data dari database (ganti nama tabel sesuai punya kamu)
$no = 1;
$sql = mysqli_query($koneksi, "
    SELECT r.*, b.nama_brg, b.spesifikasi_brg, b.merk_brg
    FROM tbl_riwayat_tambah r
    JOIN tbl_barang b ON r.id_brg = b.id_brg
    ORDER BY r.tanggal DESC
");

// Tampilkan data
while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>
        <td>{$no}</td>
        <td>{$row['id_brg']}</td>
        <td>{$row['nama_brg']}</td>
        <td>{$row['spesifikasi_brg']}</td>
        <td>{$row['merk_brg']}</td>
        <td>{$row['jumlah_tambah']}</td>
        <td>" . date("d-m-Y", strtotime($row['tanggal'])) . "</td>
        <td>{$row['keterangan']}</td>
      </tr>";
    $no++;
}

echo "</table>";
exit;
?>
