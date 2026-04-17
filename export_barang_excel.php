<?php
// koneksi database
include "koneksi.php"; // sesuaikan path

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=barang_inventaris.xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang") or die(mysqli_error($koneksi));

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr style='background-color:#007bff; color:white; font-weight:bold; text-align:center;'>
        <th>ID BARANG</th>
        <th>NAMA BARANG</th>
        <th>SPESIFIKASI</th>
        <th>MERK</th>
        <th>NO RAK</th>
        <th>JUMLAH</th>
      </tr>";

while($row = mysqli_fetch_assoc($sql)){
    // Warna merah jika jumlah <= 5
    $warna_jumlah = ($row['jumlah_brg'] <= 5) ? "background-color:#ffcccc; font-weight:bold;" : "";
    
    echo "<tr>
            <td>{$row['id_brg']}</td>
            <td>{$row['nama_brg']}</td>
            <td>{$row['spesifikasi_brg']}</td>
            <td>{$row['merk_brg']}</td>
            <td>{$row['norak_brg']}</td>
            <td style='{$warna_jumlah}'>{$row['jumlah_brg']}</td>
          </tr>";
}
echo "</table>";
?>
