<?php
// export_prioritas_stok_excel.php
include "koneksi.php";

$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql = "SELECT 
            b.id_brg,
            b.nama_brg,
            b.spesifikasi_brg,
            b.merk_brg,
            b.jumlah_brg,
            b.norak_brg,
            k.nama_kategori
        FROM tbl_barang b
        LEFT JOIN tbl_kategori k ON b.id_kategori = k.id_kategori
        WHERE b.jumlah_brg <= 5";

if ($filter_kategori > 0) {
    $sql .= " AND b.id_kategori = '$filter_kategori'";
}

$sql .= " ORDER BY b.jumlah_brg ASC, b.nama_brg ASC";
$result = mysqli_query($koneksi, $sql);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=prioritas_stok_opname_" . date('Ymd_His') . ".xls");
?>

<table border="1">
    <thead>
        <tr>
            <th>NO</th>
            <th>ID BARANG</th>
            <th>NAMA BARANG</th>
            <th>SPESIFIKASI</th>
            <th>MERK</th>
            <th>KATEGORI</th>
            <th>NO RAK</th>
            <th>JUMLAH STOK</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_array($result)) {
            $stok = $row['jumlah_brg'];
            $status = ($stok <= 2) ? 'Stok Kritis' : 'Stok Menipis';
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['id_brg']; ?></td>
            <td><?= $row['nama_brg']; ?></td>
            <td><?= $row['spesifikasi_brg']; ?></td>
            <td><?= $row['merk_brg']; ?></td>
            <td><?= $row['nama_kategori'] ?? 'Tidak Ada'; ?></td>
            <td><?= $row['norak_brg']; ?></td>
            <td><?= $stok; ?> pcs</td>
            <td><?= $status; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>