<?php 
// proses_pinjam.php (DIPERBAIKI)
// File ini mengurangi tbl_barang.jumlah_brg, sehingga proses pengembalian HARUS menambahnya kembali.

include "../../koneksi.php";

if (isset($_POST['simpanpinjam'])) {
    
    // 1. Ambil & Sanitasi Data
    $id_brg = mysqli_real_escape_string($koneksi, $_POST['id_brg']);
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $tgl_pinjam = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tgl_perkiraan_balik = mysqli_real_escape_string($koneksi, $_POST['tgl_perkiraan_balik']);
    $jumlah_brg = (int) $_POST['jumlah_brg']; 
    $tujuan_gunabarang = mysqli_real_escape_string($koneksi, $_POST['tujuan_gunabarang']);

    // Ambil organisasi dari user
    $q_user = mysqli_query($koneksi, "SELECT id_organisasi FROM tb_user WHERE id_user='$id_user'");
    $data_user = mysqli_fetch_assoc($q_user);
    $organisasi = $data_user['id_organisasi'] ?? '-';
    
    // Periksa jumlah pinjam
    if ($jumlah_brg <= 0) {
        echo "<script>alert('Jumlah pinjam tidak valid (harus lebih dari 0).');history.back();</script>";
        exit;
    }

    // 2. Mulai Transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Ambil data barang dan LOCK baris
        $brg_query = mysqli_query($koneksi,
            "SELECT * FROM tbl_barang WHERE id_brg='$id_brg' FOR UPDATE"
        );
        if (!$brg_query) {
             throw new Exception("Gagal mengunci data barang.");
        }
        $brg = mysqli_fetch_array($brg_query);
        
        if (!$brg) {
            throw new Exception("Data barang tidak ditemukan.");
        }
        
        // Hitung stok yang sedang dipinjam (AKTIF)
        $pinjam_query = mysqli_query($koneksi,
            "SELECT COALESCE(SUM(jumlah_pinjam), 0) AS total_dipinjam 
             FROM tbl_pinjaman 
             WHERE id_brg='$id_brg' AND status='Dipinjam'"
        );
        $pinjam_data = mysqli_fetch_assoc($pinjam_query);
        $total_dipinjam = $pinjam_data['total_dipinjam'];
        
        // Stok tersedia = stok fisik - stok yang sedang dipinjam
        $stok_tersedia = $brg['jumlah_brg'] - $total_dipinjam;
        
        // Periksa ketersediaan barang
        if ($stok_tersedia < $jumlah_brg) {
            throw new Exception("Stok barang tidak mencukupi! Tersedia: $stok_tersedia, Diminta: $jumlah_brg");
        }

        // Q1: Update Stok Barang (Pengurangan Stok Fisik)
        // CATATAN: Stok fisik berkurang, tapi nanti saat kembali akan ditambah lagi
        $update_stok = mysqli_query($koneksi,
            "UPDATE tbl_barang SET jumlah_brg = jumlah_brg - $jumlah_brg WHERE id_brg='$id_brg'"
        );
        if (!$update_stok) {
            throw new Exception("Gagal mengurangi stok barang.");
        }

        // Q2: Simpan pinjaman ke tbl_pinjaman
        $sql = "INSERT INTO tbl_pinjaman
            (id_brg, id_user, tgl_pinjam, tgl_perkiraan_balik, jumlah_pinjam, organisasi, tujuan_gunabarang, status)
            VALUES
            ('$id_brg', '$id_user', '$tgl_pinjam', '$tgl_perkiraan_balik', '$jumlah_brg', '$organisasi', '$tujuan_gunabarang', 'Dipinjam')";
        
        $ok2 = mysqli_query($koneksi, $sql);
        $id_pinjaman_baru = mysqli_insert_id($koneksi); 

        if (!$ok2 || $id_pinjaman_baru <= 0) {
             throw new Exception("Gagal menyimpan data pinjaman ke tabel pinjaman.");
        }

        // Q3: History aktivitas (tbl_history)
        $jenis_aktivitas = "Pinjam";
        $waktu_sekarang = date('H:i:s');
        $hist_brg_name = $brg['nama_brg'] ?? 'Barang tidak diketahui'; 
        
        $q_hist = "INSERT INTO tbl_history
            (jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history, id_user)
            VALUES
            ('$jenis_aktivitas', '$id_brg', '$hist_brg_name', '$jumlah_brg', '$tgl_pinjam', '$waktu_sekarang', '$id_user')";
        
        $ok3 = mysqli_query($koneksi, $q_hist);
        if (!$ok3) {
            throw new Exception("Gagal mencatat history aktivitas.");
        }
        
        // Q4: History pinjam (tbl_history_pinjam)
        $q_hist_pinjam = "INSERT INTO tbl_history_pinjam
            (id_pinjaman, id_brg, id_user, jumlahbrg_pinjam, tujuan_gunabarang, tgl_pinjam, tgl_perkiraan_balik)
            VALUES
            ('$id_pinjaman_baru', '$id_brg', '$id_user', '$jumlah_brg', '$tujuan_gunabarang', '$tgl_pinjam', '$tgl_perkiraan_balik')";
            
        $ok4 = mysqli_query($koneksi, $q_hist_pinjam);
        if (!$ok4) {
            throw new Exception("Gagal mencatat history pinjaman.");
        }

        // 3. Selesaikan transaksi
        mysqli_commit($koneksi);
        
        echo "<script>
            alert('Data pinjaman berhasil disimpan! Stok telah dikurangi.');
            document.location.href='../../admin.php?page=detailbarang&id=$id_brg';
        </script>";

    } catch (Exception $e) {
        // Jika terjadi kegagalan, Rollback perubahan
        mysqli_rollback($koneksi);
        $message = addslashes($e->getMessage());
        echo "<script>
            alert('Gagal memproses pinjaman: $message');
            document.location.href='../../admin.php?page=detailbarang&id=$id_brg';
        </script>";
    }
}
?>