<?php
include "../../koneksi.php";

$id   = $_POST['id_user'];
$nama = $_POST['nama'];
$user = $_POST['user'];
$email= $_POST['email'];
$hp   = $_POST['hp'];
$pass = $_POST['pass'];

if(!empty($pass)){
    mysqli_query($koneksi,"
        UPDATE tb_user SET
        nama_lengkap='$nama',
        user='$user',
        email='$email',
        no_whatsapp='$hp',
        pass='$pass'
        WHERE id_user='$id'
    ");
}else{
    mysqli_query($koneksi,"
        UPDATE tb_user SET
        nama_lengkap='$nama',
        user='$user',
        email='$email',
        no_whatsapp='$hp'
        WHERE id_user='$id'
    ");
}