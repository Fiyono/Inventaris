<?php
// export_kas_excel.php
// Export data pinjaman belum dikembalikan ke Excel

session_start();
include "koneksi.php";

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=daftar_pinjaman_belum_kembali_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query data
$sql = mysqli_query($koneksi, "
    SELECT 
        x.id_pinjaman, 
        x.id_brg, 
        x.tgl_pinjam, 
        x.jumlah_pinjam, 
        x.tujuan_gunabarang, 
        x.organisasi,
        y.nama_brg, 
        u.nama_lengkap
    FROM tbl_pinjaman x
    INNER JOIN tbl_barang y ON y.id_brg = x.id_brg
    INNER JOIN tb_user u ON u.id_user = x.id_user
    WHERE x.status != 'dikembalikan'
    ORDER BY x.id_pinjaman DESC
");

// Tampilkan dalam format tabel Excel
echo "<table border='1'>";
echo "<thead>";
echo "<tr>";
echo "<th>NO</th>";
echo "<th>NAMA PEMINJAM</th>";
echo "<th>NAMA BARANG</th>";
echo "<th>ID BARANG</th>";
echo "<th>TANGGAL PINJAM</th>";
echo "<th>JUMLAH PINJAM</th>";
echo "<th>TUJUAN PENGGUNAAN</th>";
echo "<th>ORGANISASI</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$no = 1;
while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_brg']) . "</td>";
    echo "<td>" . htmlspecialchars($row['id_brg']) . "</td>";
    echo "<td>" . date('d-m-Y', strtotime($row['tgl_pinjam'])) . "</td>";
    echo "<td>" . $row['jumlah_pinjam'] . "</td>";
    echo "<td>" . htmlspecialchars($row['tujuan_gunabarang']) . "</td>";
    echo "<td>" . strtoupper(htmlspecialchars($row['organisasi'])) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>