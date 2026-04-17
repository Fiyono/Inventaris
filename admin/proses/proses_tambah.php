<?php
include "../../koneksi.php";

if (isset($_POST['simpantambah'])) {
    $id_brg          = $_POST['id_brg'];
    $barcode_brg     = $_POST['barcode_brg'];
    $nama_brg        = $_POST['nama_brg'];
    $spesifikasi_brg = $_POST['spesifikasi_brg'];
    $merk_brg        = $_POST['merk_brg'];
    $jumlah_tambah   = $_POST['jumlah_tambah'];
    $tgl_tambah      = $_POST['tgl_tambah'];
    $keterangan      = $_POST['keterangan'];

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Cek apakah barang ada
        $cek = mysqli_query($koneksi, "SELECT jumlah_brg FROM tbl_barang WHERE id_brg='$id_brg' FOR UPDATE");
        if (mysqli_num_rows($cek) == 0) {
            throw new Exception('Data barang tidak ditemukan!');
        }

        $data = mysqli_fetch_array($cek);
        $jumlah_baru = $data['jumlah_brg'] + $jumlah_tambah;

        // Update jumlah barang
        $update_barang = mysqli_query($koneksi, "UPDATE tbl_barang SET jumlah_brg='$jumlah_baru' WHERE id_brg='$id_brg'");
        
        if (!$update_barang) {
            throw new Exception('Gagal update jumlah barang!');
        }

        // Simpan riwayat penambahan
        $insert_riwayat = mysqli_query($koneksi, "
            INSERT INTO tbl_riwayat_tambah (id_brg, nama_brg, spesifikasi_brg, merk_brg, jumlah_tambah, tanggal, keterangan)
            VALUES ('$id_brg', '$nama_brg', '$spesifikasi_brg', '$merk_brg', '$jumlah_tambah', '$tgl_tambah', '$keterangan')
        ");
        
        if (!$insert_riwayat) {
            throw new Exception('Gagal menyimpan riwayat!');
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        // Arahkan otomatis ke halaman riwayat
        echo "<script>
            alert('Jumlah barang berhasil ditambahkan!');
            window.location.href = '../../admin.php?page=riwayat_tambah&id=$id_brg';
        </script>";

    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($koneksi);
        
        echo "<script>
            alert('" . addslashes($e->getMessage()) . "');
            window.location.href = '../admin.php?page=detailbarang&id=$id_brg';
        </script>";
    }
}
?>