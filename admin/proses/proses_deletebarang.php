<?php
include "../../koneksi.php"; // sesuaikan path koneksi Anda

if (isset($_GET['delete'])) {
    $id_brg   = $_GET['delete'];
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../../admin.php?page=master_data';

    // ambil data untuk hapus gambar kalau ada
    $cek = mysqli_query($koneksi, "SELECT gambar_brg FROM tbl_barang WHERE id_brg = '".$id_brg."'");
    $row = mysqli_fetch_array($cek);

    if ($row && $row['gambar_brg'] != "" && file_exists("../../dist/upload_img/".$row['gambar_brg'])) {
        unlink("../../dist/upload_img/".$row['gambar_brg']); // hapus file gambar
    }

    // hapus data barang
    $sql = mysqli_query($koneksi, "DELETE FROM tbl_barang WHERE id_brg = '".$id_brg."'");

    if ($sql) {
        echo "<script>
        alert('Data berhasil dihapus');
        document.location.href = '".$redirect."';
        </script>";
    } else {
        echo "<script>
        alert('Data gagal dihapus');
        document.location.href = '".$redirect."';
        </script>";
    }
} else {
    // kalau akses langsung tanpa parameter
    echo "<script>
    alert('Tidak ada data yang dipilih untuk dihapus');
    document.location.href = '../../admin.php?page=master_data';
    </script>";
}
?>
