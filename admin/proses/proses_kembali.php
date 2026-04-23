<?php
// proses_kembali.php (DIPERBAIKI)
include "../../koneksi.php";

if (isset($_POST['simpankembali'])) {

    $id_pinjaman        = mysqli_real_escape_string($koneksi, $_POST['id_pinjaman']);
    $id_brg             = mysqli_real_escape_string($koneksi, $_POST['id_brg']);
    $id_user            = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $jumlah_kembali     = (int) $_POST['jumlah_kembali'];
    $tgl_kembali        = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);
    $waktu_sekarang     = date('H:i:s');

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Ambil data pinjaman lama (cek apakah status masih Dipinjam)
        $query_pinjam = mysqli_query($koneksi,
            "SELECT * FROM tbl_pinjaman WHERE id_pinjaman = '$id_pinjaman' AND status = 'Dipinjam'"
        );
        
        if (mysqli_num_rows($query_pinjam) == 0) {
            throw new Exception("Data peminjaman tidak ditemukan atau sudah dikembalikan!");
        }
        
        $pinjam = mysqli_fetch_assoc($query_pinjam);
        
        // Validasi jumlah kembali tidak melebihi jumlah pinjam
        if ($jumlah_kembali > $pinjam['jumlah_pinjam']) {
            throw new Exception("Jumlah kembali tidak boleh melebihi jumlah pinjam!");
        }

        // Ambil data barang dan LOCK baris
        $query_brg = mysqli_query($koneksi,
            "SELECT * FROM tbl_barang WHERE id_brg = '$id_brg' FOR UPDATE"
        );
        
        if (mysqli_num_rows($query_brg) == 0) {
            throw new Exception("Data barang tidak ditemukan!");
        }
        
        $brg = mysqli_fetch_assoc($query_brg);
        $nama_brg = $brg['nama_brg'];

        // ================================
        // 1. UPDATE STOK BARANG (TAMBAH KEMBALI)
        // ================================
        $stok_baru = $brg['jumlah_brg'] + $jumlah_kembali;
        
        $update_stok = mysqli_query($koneksi,
            "UPDATE tbl_barang SET jumlah_brg = '$stok_baru'
             WHERE id_brg = '$id_brg'"
        );
        
        if (!$update_stok) {
            throw new Exception("Gagal mengupdate stok barang!");
        }

        // ================================
        // 2. UPDATE STATUS PINJAMAN
        // ================================
        $update_pinjam = mysqli_query($koneksi, 
            "UPDATE tbl_pinjaman 
             SET status = 'Dikembalikan', 
                 jumlah_kembali = '$jumlah_kembali',
                 tgl_kembali = '$tgl_kembali'
             WHERE id_pinjaman = '$id_pinjaman'"
        );
        
        if (!$update_pinjam) {
            throw new Exception("Gagal mengupdate status peminjaman!");
        }

        // ================================
        // 3. CATAT KE TBL_HISTORY
        // ================================
        $insert_history = mysqli_query($koneksi,
            "INSERT INTO tbl_history
            (jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history, id_user)
            VALUES
            ('Kembali', '$id_brg', '$nama_brg', '$jumlah_kembali', '$tgl_kembali', '$waktu_sekarang', '$id_user')"
        );
        
        if (!$insert_history) {
            throw new Exception("Gagal mencatat history pengembalian!");
        }

        // ================================
        // 4. CATAT KE TBL_HISTORY_PINJAM
        // ================================
        $insert_history_pinjam = mysqli_query($koneksi,
            "INSERT INTO tbl_history_pinjam
            (id_pinjaman, id_brg, nama_brg, jumlah_brg, tgl_kembali, id_user)
            VALUES
            ('$id_pinjaman', '$id_brg', '$nama_brg', '$jumlah_kembali', '$tgl_kembali', '$id_user')"
        );
        
        if (!$insert_history_pinjam) {
            throw new Exception("Gagal mencatat history pinjaman!");
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        echo "<script>
            alert('Barang berhasil dikembalikan! Stok telah ditambahkan kembali.');
            window.location.href='../?page=riwayat_pinjam';
        </script>";
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($koneksi);
        $message = addslashes($e->getMessage());
        echo "<script>
            alert('Gagal mengembalikan barang: $message');
            window.location.href='../?page=riwayat_pinjam';
        </script>";
    }
}
?>