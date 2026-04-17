<?php
// export_set_members_excel.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$root_path = $_SERVER['DOCUMENT_ROOT'] . '/inventaris';
include $root_path . '/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=daftar_barang_diambil_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = mysqli_query($koneksi, "
    SELECT id_ambil, y.id_brg, tgl_brg_keluar, nama_brg, 
           y.jumlah_brg as diambil, x.jumlah_brg as sisa, 
           tujuan_gunabarang, alamat_ruang 
    FROM tbl_barang x 
    INNER JOIN tbl_ambil y ON y.id_brg = x.id_brg 
    ORDER BY id_brg DESC
");

echo "<table border='1'>";
echo "<thead><tr>";
echo "<th>ID BARANG</th><th>NAMA BARANG</th>";
echo "<th>JUMLAH DIAMBIL</th><th>SISA BARANG</th><th>TUJUAN PENGGUNAAN</th><th>ALAMAT RUANG</th><th>TANGGAL PENGAMBILAN</th>";
echo "</tr></thead><tbody>";

while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id_brg']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_brg']) . "</td>";
    echo "<td>" . $row['diambil'] . "</td>";
    echo "<td>" . $row['sisa'] . "</td>";
    echo "<td>" . htmlspecialchars($row['tujuan_gunabarang']) . "</td>";
    echo "<td>" . htmlspecialchars($row['alamat_ruang']) . "</td>";
    echo "<td>" . date('d-m-Y', strtotime($row['tgl_brg_keluar'])) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";
?>