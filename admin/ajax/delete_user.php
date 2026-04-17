<?php
include "../../koneksi.php";

header('Content-Type: application/json');

if(empty($_POST['id'])){
    echo json_encode(["error"=>"ID kosong"]);
    exit;
}

$id = mysqli_real_escape_string($koneksi,$_POST['id']);

/* OPTIONAL SAFETY
   cegah admin utama terhapus */
$cek = mysqli_query($koneksi,"SELECT id_level FROM tb_user WHERE id_user='$id'");
$data = mysqli_fetch_assoc($cek);

if($data['id_level'] == 1){
    echo json_encode(["error"=>"Admin utama tidak bisa dihapus"]);
    exit;
}

mysqli_query($koneksi,"DELETE FROM tb_user WHERE id_user='$id'");

echo json_encode(["success"=>true]);