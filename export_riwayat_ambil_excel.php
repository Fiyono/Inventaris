<?php
include "koneksi.php";

// Nama file Excel
$filename = "Daftar_Aktivitas_" . date("Y-m-d_H-i-s") . ".xls";

// Header download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Query data final
$no = 1;
$sql = mysqli_query($koneksi, "
SELECT h.*,
       b.spesifikasi_brg,
       b.merk_brg,
       a.tujuan_gunabarang
FROM tbl_history h
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

// Mulai tabel
echo "
<table border='1' cellspacing='0' cellpadding='5'>
    <tr>
        <th colspan='9' style='background:#007bff;color:white;font-size:16px;height:30px;'>
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
        <th>WAKTU</th>
    </tr>
";

// Isi data
while ($row = mysqli_fetch_assoc($sql)) {

    $jumlah_style = "";
    if ($row['jumlah_brg'] <= 5) {
        $jumlah_style = "background:#ffcccc;font-weight:bold;";
    }

    $tujuan = !empty($row['tujuan_gunabarang']) 
        ? $row['tujuan_gunabarang'] 
        : "-";

    $waktu = date("d-m-Y H:i:s", strtotime($row['tgl_history'] . " " . $row['waktu_history']));

    echo "
    <tr>
        <td align='center'>$no</td>
        <td>{$row['nama_lengkap']}</td>
        <td align='center'>{$row['id_brg']}</td>
        <td>{$row['nama_brg']}</td>
        <td>{$row['spesifikasi_brg']}</td>
        <td>{$row['merk_brg']}</td>
        <td align='center' style='$jumlah_style'>{$row['jumlah_brg']}</td>
        <td>{$tujuan}</td>
        <td align='center'>{$waktu}</td>
    </tr>
    ";

    $no++;
}

// Tutup tabel
echo "</table>";
exit;
?>