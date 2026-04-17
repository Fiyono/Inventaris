<?php
session_start();
include "koneksi.php";

/*
|--------------------------------------------------------------------------
| CEK SESSION LOGIN
|--------------------------------------------------------------------------
*/
if(!isset($_SESSION['id_user'])){
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/
$id_user = $_SESSION['id_user'];

$q = mysqli_query($koneksi,"
    SELECT id_user,id_level
    FROM tb_user
    WHERE id_user='$id_user'
    LIMIT 1
");

$user = mysqli_fetch_assoc($q);

if(!$user){
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REDIRECT SESUAI LEVEL
|--------------------------------------------------------------------------
*/
if($user['id_level'] == 'admin'){
    header("Location: admin.php");
    exit;
}elseif($user['id_level'] == 'user'){
    header("Location: anggota.php");
    exit;
}else{
    session_destroy();
    header("Location: login.php");
    exit;
}
?>