<?php
session_start();
include "koneksi.php";

/*
|--------------------------------------------------------------------------
| CEK SESSION LOGIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/
$id_user = $_SESSION['id_user'];
$query_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user='$id_user' LIMIT 1");
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK LEVEL ADMIN
|--------------------------------------------------------------------------
*/
if ($user['id_level'] != 'admin') {
    header("Location: logout.php");
    exit;
}

function rupiah($angka)
{
    return "Rp. " . number_format($angka, 2, ',', '.');
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventaris</title>
    <link rel="icon" href="dist/img/inventaris.png">

    <!-- CSS LIBRARY -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <!-- CUSTOM CSS -->
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f4f6f9;
        }

        /* ===================== NAVBAR TOP (mobile hamburger) ===================== */
        .main-header {
            display: none;
        }

        @media (max-width: 991px) {
            .main-header {
                display: flex !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1035;
                height: 57px;
                background: linear-gradient(to right, rgb(30, 60, 114), #2a5298);
                align-items: center;
                padding: 0 15px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .main-header .navbar-brand {
                color: #fff;
                font-weight: bold;
                font-size: 18px;
                text-decoration: none;
                margin-left: 10px;
            }
            .main-header .pushmenu-btn {
                background: transparent;
                border: none;
                color: #fff;
                font-size: 22px;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }
        }

        /* ===================== CONTENT WRAPPER ===================== */
        .content-wrapper {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        section.content {
            padding-top: 0 !important;
            padding-bottom: 0;
        }

        .card-wrapper {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-top: 0 !important;
            margin-bottom: 20px;
            background: #fff;
        }

        /* ===================== SIDEBAR ===================== */
        .main-sidebar {
            background: linear-gradient(to bottom, rgb(30, 60, 114), #2a5298) !important;
        }

        .sidebar {
            background: transparent;
        }

        .brand-link {
            background: rgba(0,0,0,0.15) !important;
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        }

        .brand-text {
            color: #fff !important;
        }

        .nav-sidebar .nav-link {
            color: #fff !important;
            transition: all 0.3s;
            border-radius: 8px;
        }

        .nav-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffeb3b !important;
        }

        .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link {
            background: rgba(255, 255, 255, 0.15);
        }

        .nav-treeview {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            margin-left: 5px;
            padding-left: 5px;
        }

        /* ===================== FOOTER ===================== */
        .main-footer {
            padding: 5px 0 !important;
            background-color: #f8f9fa !important;
        }

        .footer-link {
            padding: 10px 0 !important;
            display: block;
            color: #888;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-link i {
            font-size: 20px;
            margin-bottom: 2px !important;
        }

        .footer-link span {
            font-size: 11px;
            margin-top: -2px !important;
            display: block;
            line-height: 1.2;
        }

        .footer-link:hover i,
        .footer-link:hover span {
            color: rgb(0, 89, 255);
            transform: scale(1.2) translateY(-2px);
            text-shadow: 0 0 5px rgb(27, 0, 0);
        }

        .active-footer i,
        .active-footer span {
            color: rgb(30, 60, 114) !important;
            transform: scale(1.1) translateY(-2px);
        }

        /* ===================== MOBILE OVERRIDES ===================== */
        @media (max-width: 991px) {
            /* Konten turun agar tidak tertutup navbar */
            .content-wrapper {
                margin-left: 0 !important;
                padding-top: 57px !important;
                padding-bottom: 90px !important;
            }

            /* Footer fixed di bawah */
            .main-footer {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                margin-left: 0 !important;
                z-index: 1030 !important;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }

            /* Sidebar overlay di atas konten */
            .main-sidebar {
                z-index: 1040 !important;
                transform: translateX(-250px);
                transition: transform 0.3s ease;
            }

            /* Saat sidebar terbuka (class sidebar-open pada body oleh AdminLTE) */
            .sidebar-open .main-sidebar {
                transform: translateX(0) !important;
            }

            .wrapper {
                overflow-x: hidden;
            }

            /* Overlay gelap saat sidebar terbuka */
            .sidebar-open::after {
                content: '';
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1035;
            }

            .footer-link i {
                font-size: 20px;
            }

            .footer-link span {
                font-size: 10px;
            }

            .card-wrapper {
                padding: 12px;
            }
        }

        /* Desktop: sembunyikan header mobile, tampilkan sidebar normal */
        @media (min-width: 992px) {
            .content-wrapper {
                padding-bottom: 80px;
            }
        }

        .table-loader {
            display: none;
            text-align: center;
            padding: 50px 0;
            font-size: 18px;
            color: #007bff;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- ===================== NAVBAR TOP (hanya mobile) ===================== -->
        <nav class="main-header">
            <button class="pushmenu-btn" data-widget="pushmenu" role="button">
                <i class="fas fa-bars"></i>
            </button>
            <a href="?page=home" class="navbar-brand">
                INVENTARIS
            </a>
            <div class="ml-auto">
                <a href="?page=profile" style="color:#fff; font-size:22px; text-decoration:none;">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </nav>

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="main-sidebar elevation-4">
            <a href="?page=home" class="brand-link">
                <img src="dist/img/inventaris.jpg" alt="Logo" class="brand-image" style="opacity: .8;" width="250" height="120">
                <span class="brand-text font-weight-bold">INVENTARIS</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <!-- MASTER DATA -->
                        <li class="nav-item has-treeview <?= in_array($page, ['master_data', 'data_user', 'data_kategori']) ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= in_array($page, ['master_data', 'data_user', 'data_kategori']) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-database"></i>
                                <p>MASTER DATA <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?page=master_data" class="nav-link <?= ($page == 'master_data') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>DATA BARANG</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?page=data_user" class="nav-link <?= ($page == 'data_user') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>DATA USER</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?page=data_kategori" class="nav-link <?= ($page == 'data_kategori') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>DATA KATEGORI</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- RIWAYAT -->
                        <li class="nav-item has-treeview <?= in_array($page, ['aktifitas', 'riwayat_pinjam', 'riwayat_tambah']) ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= in_array($page, ['aktifitas', 'riwayat_pinjam', 'riwayat_tambah']) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-history"></i>
                                <p>RIWAYAT <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="?page=aktifitas" class="nav-link <?= ($page == 'aktifitas') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>RIWAYAT AMBIL</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?page=riwayat_pinjam" class="nav-link <?= ($page == 'riwayat_pinjam') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>RIWAYAT PINJAM</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?page=riwayat_tambah" class="nav-link <?= ($page == 'riwayat_tambah') ? 'active' : '' ?>">
                                        <i class="far fa-circle nav-icon"></i><p>RIWAYAT TAMBAH</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="?page=peminjaman" class="nav-link <?= ($page == 'peminjaman') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-box-open"></i>
                                <p>PEMINJAMAN</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="?page=pengambilan" class="nav-link <?= ($page == 'pengambilan') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>PENGAMBILAN</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="?page=prioritas_stok" class="nav-link <?= ($page == 'prioritas_stok') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cart-plus"></i>
                                <p>DAFTAR BELANJA</p>
                            </a>
                        </li>

                        <!-- LOGOUT -->
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i><p>LOGOUT</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>

        <!-- ===================== CONTENT WRAPPER ===================== -->
        <div class="content-wrapper">
            <section class="content" style="padding-top:0; padding-bottom:0;">
                <div class="container-fluid" style="padding-top:0; padding-bottom:0;">
                    <div class="card-wrapper">
                        <?php
                        switch ($page) {
                            case 'home':             include 'admin/home.php'; break;
                            case 'request_tiket':    include 'admin/request_tiket.php'; break;
                            case 'set_members':      include 'admin/set_members.php'; break;
                            case 'master_data':      include 'admin/master_data.php'; break;
                            case 'editmaster_data':  include 'admin/editmaster_data.php'; break;
                            case 'editfoto':         include 'admin/editfoto.php'; break;
                            case 'editfilefoto':     include 'admin/editfilefoto.php'; break;
                            case 'detailbarang':     include 'admin/detailbarang.php'; break;
                            case 'tiket_masuk':      include 'admin/tiket_masuk.php'; break;
                            case 'aktifitas':        include 'admin/aktifitas.php'; break;
                            case 'riwayat_pinjam':   include 'admin/riwayat_pinjam.php'; break;
                            case 'riwayat_tambah':   include 'admin/riwayat_tambah.php'; break;
                            case 'data_user':        include 'admin/data_user.php'; break;
                            case 'data_organisasi':  include 'admin/data_organisasi.php'; break;
                            case 'data_kategori':    include 'admin/data_kategori.php'; break;
                            case 'profile':          include 'admin/profile.php'; break;
                            case 'kas':              include 'admin/kas.php'; break;
                            case 'tabungan':         include 'admin/tabungan.php'; break;
                            case 'riwayat_tabungankeluar': include 'admin/riwayat_tabungankeluar.php'; break;
                            case 'kas_masuk':        include 'admin/kas_masuk.php'; break;
                            case 'edit_aktivitas':   include 'admin/edit_aktivitas.php'; break;
                            case 'kas_keluar':       include 'admin/kas_keluar.php'; break;
                            case 'catatan':          include 'admin/catatan.php'; break;
                            case 'daftarlogin':      include 'admin/daftarlogin.php'; break;
                            case 'edit_set_member':  include 'admin/edit_set_member.php'; break;
                            case 'edit_riwayat_tambah': include 'admin/edit_riwayat_tambah.php'; break;
                            case 'cetak_struk': include 'admin/cetak_struk.php'; break;
                            case 'peminjaman': include 'admin/peminjaman.php'; break;
                            case 'prioritas_stok':  include 'admin/prioritas_stok.php'; break;
                            case 'pengambilan': include 'admin/pengambilan.php'; break;
                            default:                 include 'admin/home.php'; break;
                        }
                        ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- ===================== FOOTER BOTTOM NAV ===================== -->
        <footer class="main-footer">
            <div class="container">
                <div class="row text-center">
                    <div class="col-3">
                        <a href="?page=home" class="footer-link <?= ($page == 'home') ? 'active-footer' : '' ?>">
                            <i class="fas fa-home mb-1"></i><br><span>HOME</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="?page=request_tiket" class="footer-link <?= ($page == 'request_tiket') ? 'active-footer' : '' ?>">
                            <i class="fas fa-pen-alt mb-1"></i><br><span>INPUT</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="?page=kas" class="footer-link <?= ($page == 'kas') ? 'active-footer' : '' ?>">
                            <i class="fas fa-sync-alt mb-1"></i><br><span>BELUM KEMBALI</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="?page=set_members" class="footer-link <?= ($page == 'set_members') ? 'active-footer' : '' ?>">
                            <i class="fas fa-sign-out-alt mb-1"></i><br><span>BRG KELUAR</span>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

    </div><!-- /.wrapper -->

    <!-- ===================== JAVASCRIPT ===================== -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>

    <script>
        $(document).ready(function () {

            // SELECT2
            $(".select2").select2();
            $(".select2bs4").select2({ theme: 'bootstrap4' });

            // DATATABLE #example1
            if ($('#example1').length) {
                $('#loader1').show();
                $('#example1').DataTable({
                    responsive: true,
                    autoWidth: false,
                    initComplete: function () {
                        $('#loader1').hide();
                        $('#example1').fadeIn();
                    }
                });
            }

            // DATATABLE #example2
            if ($('#example2').length) {
                $('#loader2').show();
                $('#example2').DataTable({
                    paging: true,
                    lengthChange: false,
                    searching: false,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    responsive: true,
                    initComplete: function () {
                        $('#loader2').hide();
                        $('#example2').fadeIn();
                    }
                });
            }

            // Tutup sidebar hanya saat klik link yang BUKAN parent treeview (bukan toggle dropdown)
            $(document).on('click', '.nav-sidebar .nav-link', function () {
                if ($(window).width() < 992) {
                    // Cek apakah parent <li> punya class has-treeview (artinya ini tombol toggle dropdown)
                    var isTreeviewToggle = $(this).closest('li').hasClass('has-treeview');
                    if (!isTreeviewToggle) {
                        // Ini link biasa (sub-menu atau logout) — tutup sidebar
                        $('body').removeClass('sidebar-open');
                    }
                    // Jika treeview toggle → biarkan AdminLTE buka/tutup dropdown, sidebar tetap terbuka
                }
            });

            // Tutup sidebar saat klik overlay
            $(document).on('click', function (e) {
                if ($(window).width() < 992 && $('body').hasClass('sidebar-open')) {
                    if (!$(e.target).closest('.main-sidebar, .pushmenu-btn, [data-widget="pushmenu"]').length) {
                        $('body').removeClass('sidebar-open');
                    }
                }
            });

        });

        // DEBUG CLICK
        $(document).on("click", ".btn-edit", function () {
            console.log("CLICK OK");
        });
    </script>
</body>

</html>