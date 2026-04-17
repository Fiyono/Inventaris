<?php
require_once("../../koneksi.php");

header('Content-Type: application/json');

if(isset($_GET['id'])) {

    $id_brg = intval($_GET['id']); // karena ID angka

    $sql = "SELECT * FROM tbl_barang WHERE id_brg = $id_brg";
    $result = mysqli_query($koneksi, $sql);

    if($result){

        if(mysqli_num_rows($result) > 0){

            $data = mysqli_fetch_assoc($result);

            echo json_encode([
                'exists' => true,
                'nama_brg' => $data['nama_brg'],
                'jumlah_brg' => $data['jumlah_brg']
            ]);

        } else {
            echo json_encode(['exists' => false]);
        }

    } else {
        echo json_encode([
            'error' => mysqli_error($koneksi)
        ]);
    }

} else {
    echo json_encode(['error' => 'ID tidak ditemukan']);
}
?>
