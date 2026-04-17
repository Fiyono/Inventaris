<?php
include "koneksi.php";

// Nama file Excel
$filename = "Riwayat_Ambil_" . date("Y-m-d_H-i-s") . ".xls";

// Header download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Query data
$no = 1;
$sql = mysqli_query($koneksi, "
SELECT h.*,
       u.nama_lengkap,
       b.nama_brg,
       b.spesifikasi_brg,
       b.merk_brg,
       a.tujuan_gunabarang
FROM tbl_history h
JOIN tb_user u 
    ON h.id_user = u.id_user
JOIN tbl_barang b 
    ON h.id_brg = b.id_brg
LEFT JOIN tbl_ambil a 
    ON h.id_user = a.id_user
    AND h.id_brg = a.id_brg
    AND h.jumlah_brg = a.jumlah_brg
    AND h.tgl_history = a.tgl_brg_keluar
WHERE h.jenis_aktivitas = 'Ambil'
ORDER BY h.id_history DESC
");

// Tabel
echo "
<table border='1' cellspacing='0' cellpadding='5'>
<tr>
<th colspan='9' style='background:#007bff;color:white;font-size:16px;text-align:center;'>
DAFTAR AKTIVITAS PENGAMBILAN BARANG
</th>
</tr>

<tr style='background:#007bff;color:white;font-weight:bold;text-align:center;'>
<th>NO</th>
<th>NAMA</th>
<th>ID BARANG</th>
<th>NAMA BARANG</th>
<th>TYPE</th>
<th>MERK</th>
<th>JUMLAH</th>
<th>TUJUAN PENGGUNAAN</th>
<th>TANGGAL</th>
</tr>
";

while ($row = mysqli_fetch_assoc($sql)) {

    $tujuan  = !empty($row['tujuan_gunabarang']) ? $row['tujuan_gunabarang'] : "-";
    $tanggal = date("d-m-Y", strtotime($row['tgl_history']));

    echo "
    <tr>
        <td align='right'>$no</td>
        <td align='left'>{$row['nama_lengkap']}</td>
        <td align='center'>{$row['id_brg']}</td>
        <td align='left'>{$row['nama_brg']}</td>
        <td align='left'>{$row['spesifikasi_brg']}</td>
        <td align='left'>{$row['merk_brg']}</td>
        <td align='center'>{$row['jumlah_brg']}</td>
        <td align='left'>{$tujuan}</td>
        <td align='center'>{$tanggal}</td>
    </tr>
    ";

    $no++;
}

echo "</table>";
exit;
?>