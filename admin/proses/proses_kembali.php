<?php
include "../../koneksi.php";

if (isset($_POST['simpankembali'])) {

    $id_pinjaman        = $_POST['id_pinjaman'];
    $id_brg             = $_POST['id_brg'];
    $id_user            = $_POST['id_user'];
    $jumlah_kembali     = $_POST['jumlah_kembali'];
    $tgl_kembali        = $_POST['tgl_kembali']; // sudah bisa pilih tanggal
    $waktu_sekarang     = date('H:i:s');

    // Ambil data pinjaman lama
    $pinjam = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT * FROM tbl_pinjaman WHERE id_pinjaman = '$id_pinjaman'"
    ));

    // Ambil data barang
    $brg = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT * FROM tbl_barang WHERE id_brg = '$id_brg'"
    ));
    $nama_brg = $brg['nama_brg'];

    // ================================
    // 1. UPDATE STOK BARANG
    // ================================
    $stok_baru = $brg['jumlah_brg'] + $jumlah_kembali;

    mysqli_query($koneksi,
        "UPDATE tbl_barang SET jumlah_brg = '$stok_baru'
         WHERE id_brg = '$id_brg'"
    );

    // ================================
    // 2. UPDATE STATUS PINJAMAN
    // ================================
    mysqli_query($koneksi, 
        "UPDATE tbl_pinjaman 
         SET status_pinjaman = 'Dikembalikan', 
             jumlah_kembali = '$jumlah_kembali',
             tgl_kembali = '$tgl_kembali'
         WHERE id_pinjaman = '$id_pinjaman'"
    );

    // ================================
    // 3. CATAT KE TBL_HISTORY (WAJIB)
    // ================================
    mysqli_query($koneksi,
        "INSERT INTO tbl_history
        (jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history, id_user)
        VALUES
        ('Kembali', '$id_brg', '$nama_brg', '$jumlah_kembali', '$tgl_kembali', '$waktu_sekarang', '$id_user')"
    );

    // ================================
    // 4. CATAT KE TBL_HISTORY_PINJAM (opsional jika Anda pakai)
    // ================================
    mysqli_query($koneksi,
        "INSERT INTO tbl_history_pinjam
        (id_pinjaman, id_brg, nama_brg, jumlah_brg, tgl_kembali, id_user)
        VALUES
        ('$id_pinjaman', '$id_brg', '$nama_brg', '$jumlah_kembali', '$tgl_kembali', '$id_user')"
    );

    echo "<script>
        alert('Barang berhasil dikembalikan!');
        window.location='../?page=riwayat-pinjam';
    </script>";
}
?>
