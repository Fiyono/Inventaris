<?php 
include "../../koneksi.php";

if (isset($_POST['simpanambil'])) {
    $id_brg = $_POST['id_brg'];
    $id_user = $_POST['id_user'];
    $tgl_brg_keluar = $_POST['tgl_brg_keluar'];
    $jumlah_brg = $_POST['jumlah_brg'];
    $alamat_ruang = $_POST['alamat_ruang'];
    $tujuan_gunabarang = $_POST['tujuan_gunabarang'];

    // ambil data barang
    $brg = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_brg = '".$id_brg."'"));

    if ($jumlah_brg <= $brg['jumlah_brg']) {
        // insert ke tabel ambil
        $sql = mysqli_query($koneksi, "
            INSERT INTO tbl_ambil(id_brg, id_user, tgl_brg_keluar, jumlah_brg, alamat_ruang, tujuan_gunabarang) 
            VALUES('".$id_brg."', '".$id_user."', '".$tgl_brg_keluar."', '".$jumlah_brg."', '".$alamat_ruang."', '".$tujuan_gunabarang."')
        ");

        // catat ke history
        $jenis_aktivitas = "Ambil";
        $waktu_sekarang = date('H:i:s');
        $hist = mysqli_query($koneksi, "
            INSERT INTO tbl_history(id_user, jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history) 
            VALUES('".$id_user."', '".$jenis_aktivitas."', '".$id_brg."', '".$brg['nama_brg']."', '".$jumlah_brg."', '".$tgl_brg_keluar."', '".$waktu_sekarang."')
        ");

        // update stok barang
        $update = mysqli_query($koneksi, "
            UPDATE tbl_barang SET jumlah_brg = jumlah_brg - '".$jumlah_brg."' WHERE id_brg = '".$id_brg."'
        ");

        if ($sql && $hist && $update) {
            header("Location: ../../admin.php?page=detailbarang&id=".$id_brg."&status=success");
            exit;
        } else {
            header("Location: ../../admin.php?page=detailbarang&id=".$id_brg."&status=error");
            exit;
        }
    } else {
        header("Location: ../../admin.php?page=detailbarang&id=".$id_brg."&status=stok_kurang");
        exit;
    }
}
?>
