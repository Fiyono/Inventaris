<?php
// get_data_pinjaman.php - Mengambil data peminjaman untuk dicetak
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pinjaman'])) {
    $id_pinjaman = (int) $_POST['id_pinjaman'];
    
    $query = mysqli_query($koneksi, "
        SELECT 
            p.id_pinjaman,
            p.tgl_pinjam,
            p.tgl_perkiraan_balik,
            p.jumlah_pinjam,
            p.tujuan_gunabarang,
            p.status,
            u.nama_lengkap,
            b.nama_brg,
            b.spesifikasi_brg,
            b.merk_brg,
            COALESCE(SUM(h.jumlahbrg_kembali), 0) AS total_kembali
        FROM tbl_pinjaman p
        JOIN tb_user u ON p.id_user = u.id_user
        JOIN tbl_barang b ON p.id_brg = b.id_brg
        LEFT JOIN tbl_history_pinjam h ON h.id_pinjaman = p.id_pinjaman
        WHERE p.id_pinjaman = '$id_pinjaman'
        GROUP BY p.id_pinjaman
    ");
    
    if ($row = mysqli_fetch_assoc($query)) {
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>