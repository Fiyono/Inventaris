<?php
// Session aman
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['agent']) || !isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

include "koneksi.php";

// Ambil data user
$id_user = $_SESSION['id_user'];
$agent_name = $_SESSION['agent'];

// Query user tunggal
$query = mysqli_query($koneksi, "
    SELECT y.*, x.name_user_agent, 0 as saldo
    FROM user_agent x
    INNER JOIN tb_user y ON y.id_user = x.id_user
    WHERE x.name_user_agent='$agent_name'
    AND x.id_user='$id_user'
    LIMIT 1
");
$user = mysqli_fetch_assoc($query) ?? [];

// Fungsi rupiah
function rupiah($angka){
    return "Rp. " . number_format($angka, 2, ',', '.');
}

// Set default background kelas footer menu
$bgh = $bgr = $bgrp = $bgp = 'bg-white';
$page = $_GET['page'] ?? 'home';
switch($page){
    case 'home': $bgh='bg-dark'; break;
    case 'riwayat_trx': $bgr='bg-dark'; break;
    case 'riwayat_pinjam': $bgrp='bg-dark'; break;
    case 'profile': $bgp='bg-dark'; break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>INVENTARIS</title>
  <link rel="icon" href="dist/img/logoinventaris.jpg">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <style>
    .bg-nav{background-image: linear-gradient(#6495ED,#6495ED);}
    .bg-radian{background-image: linear-gradient(white,#6495ED);}
    .bg-foot{background-image: linear-gradient(#6495ED,#6495ED);}
    .bg-abu{background-color: #6495ED;}
    .text-abu{color:#6495ED;}
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand bg-nav navbar-primary elevation-1">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="btn bg-white elevation-1 img-circle" data-widget="pushmenu">
          <i class="fas fa-wallet text-abu"></i>
        </a>
      </li>
      <li class="nav-item d-lg-inline-block">
        <h4><a href="anggota.php?page=profile" class="nav-link text-bold text-white">
          <?= $user['nama_lengkap'] ?? $user['name_user_agent'] ?? 'User'; ?>
        </a></h4>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar bg-radian text-bold text-white elevation-1">
    <a href="?page=home" class="brand-link">
      <img src="dist/img/logoinventaris.jpg" alt="Logo" class="brand-image" style="opacity:.8;" width="120" height="40">
      <span class="brand-text bold text-dark">INVENTARIS</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item">
            <a href="logout.php" class="nav-link text-dark">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>LOGOUT</p>
            </a>
          </li> 
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content pt-4 pb-3 mb-5">
      <div class="container-fluid">
        <?php
        switch($page){
            case 'home': include 'anggota/home.php'; break;
            case 'tiket_wadompet': include 'anggota/tiket_wadompet.php'; break;
            case 'riwayat_trx': include 'anggota/riwayat_trx.php'; break;
            case 'riwayat_pinjam': include 'anggota/riwayat_pinjam.php'; break;
            case 'profile': include 'anggota/profile_anggota.php'; break;
            case 'daftar_tiket': include 'anggota/daftar_tiket.php'; break;
            case 'dompet': include 'anggota/dompet.php'; break;
            case 'tariktunai': include 'anggota/tariktunai.php'; break;
            case 'sendsaldo': include 'anggota/sendsaldo.php'; break;
            case 'kas_user': include 'anggota/kas_user.php'; break;
            default: include 'anggota/home.php'; break;
        }
        ?>
      </div>
    </section>
  </div>

  <!-- Footer menu -->
  <footer class="main-footer bg-abu">
    <div class="row">
      <div class="col-3"><a href="?page=home"><div class="info-box <?= $bgh ?>"><span class="info-box-icon text-orange"><i class="fas fa-home"></i></span></div></a></div>
      <div class="col-3"><a href="?page=riwayat_trx"><div class="info-box <?= $bgr ?>"><span class="info-box-icon text-orange"><i class="fas fa-table"></i></span></div></a></div>
      <div class="col-3"><a href="?page=riwayat_pinjam"><div class="info-box <?= $bgrp ?>"><span class="info-box-icon text-orange"><i class="fas fa-table"></i></span></div></a></div>
      <div class="col-3"><a href="?page=profile"><div class="info-box <?= $bgp ?>"><span class="info-box-icon text-orange"><i class="fas fa-user"></i></span></div></a></div>
    </div>
  </footer>

</div>

<!-- JS -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<script>
$(function () {
    $('.select2').select2();
    $("#example1, #example2, #example3").DataTable({
        "responsive": true,
        "autoWidth": false
    });
});
</script>
<link rel="stylesheet" href="assets/css/custom.css">
<style>
    /* Header tabel */
    #example1 thead th {
        background: linear-gradient(45deg, #007bff, #00c6ff);
        color: white;
        text-align: center;
        font-size: 13px;
        padding: 10px;
    }

    /* Isi tabel */
    #example1 tbody td {
        font-size: 12px;
        text-align: center;
        vertical-align: middle;
        transition: all 0.3s ease;
    }

    /* Hover baris */
    #example1 tbody tr:hover {
        background-color: #f1f9ff !important;
        transform: scale(1.01);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* Zebra stripes */
    #example1 tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    /* Efek animasi muncul */
    #example1 tbody tr {
        animation: fadeIn 0.4s ease-in;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(5px);}
        to {opacity: 1; transform: translateY(0);}
    }

    /* Badge aktivitas */
    .badge-ambil {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 11px;
    }
    .badge-kembali {
        background-color: #ffc107;
        color: black;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 11px;
    }
</style>

</body>
</html>
