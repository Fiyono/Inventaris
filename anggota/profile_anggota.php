<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "koneksi.php";

// Pastikan user login
if (!isset($_SESSION['agent']) || !isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$agent = $_SESSION['agent'];

// Query data user
$sql = "SELECT y.* 
        FROM user_agent x
        INNER JOIN tb_user y ON y.id_user = x.id_user
        WHERE x.name_user_agent = '$agent' AND x.id_user = '$id_user'";
$result = mysqli_query($koneksi, $sql);

// Jika data tidak ditemukan
if (!$result || mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-danger'>Data user tidak ditemukan.</div>";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Fungsi aman menampilkan nilai
function safe($val) {
    return htmlspecialchars($val ?? '');
}
?>

<div class="row">
   <div class="col-md-12">
      <div class="card card-widget widget-user">
        <div class="widget-user-header bg-nav">
          <h3 class="widget-user-username">
            <a href="#" class="text-orange" data-toggle="modal" data-target="#modal-sm"><?= safe($data['nama_lengkap']); ?></a>
          </h3>
          <h5 class="widget-user-desc"><?= safe($data['position']); ?></h5>
        </div>
        <div class="widget-user-image">
          <img class="img-circle elevation-2" src="dist/img/user1-128x128.jpg" alt="User Avatar" data-toggle="modal" data-target="#modal-profile">
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-sm-4 border-right">
              <div class="description-block">
                <h5 class="description-header">Username</h5>
                <span class="description-text">
                  <input class="text-center form-control" type="text" value="<?= safe($data['user']); ?>" readonly>
                </span>
              </div>
            </div>
            <div class="col-sm-4 border-right">
              <div class="description-block">
                <h5 class="description-header">Password</h5>
                <span class="description-text">
                  <input class="text-center form-control" type="password" value="<?= safe($data['pass']); ?>" readonly>
                </span>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="description-block">
                <h5 class="description-header">Email</h5>
                <span class="description-text"><?= safe($data['email']); ?></span>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="description-block">
                <h5 class="description-header">Tempat & Tgl Lahir</h5>
                <span class="description-text"><?= safe($data['temp_lahir']).", ".safe($data['tgl_lahir']); ?></span>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="description-block">
                <h5 class="description-header">Alamat Lengkap</h5>
                <span class="description-text">
                  <textarea class="form-control" readonly><?= safe($data['alamat_sekarang']); ?></textarea>
                </span>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="description-block">
                <a href="#" class="btn elevation-2 btn-block" data-toggle="modal" data-target="#modal-sm-edit">
                  <b><i class="fas fa-edit text-orange"></i> Edit Profile</b>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
   </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal fade" id="modal-sm-edit">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form action="anggota/proses/proses_edit_profile.php" method="post">
        <div class="modal-header">
          <h4>Edit Profile</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="user" class="form-control" value="<?= safe($data['user']); ?>" readonly>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control check" value="<?= safe($data['pass']); ?>">
            <input type="checkbox" class="form-checkbox"> Show password
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= safe($data['email']); ?>">
          </div>
          <div class="form-group">
            <label>Tempat & Tanggal Lahir</label>
            <input type="text" name="temp_lahir" class="form-control mb-1" value="<?= safe($data['temp_lahir']); ?>">
            <input type="date" name="tgl_lahir" class="form-control" value="<?= safe($data['tgl_lahir']); ?>">
          </div>
          <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="alamat_sekarang" class="form-control"><?= safe($data['alamat_sekarang']); ?></textarea>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" name="simpan" class="btn btn-primary"><i class="fas fa-save"></i> Save Change</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Foto Profile -->
<div class="modal fade" id="modal-profile">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Ubah Foto Profile</h4>
      </div>
      <div class="modal-body">
        <input type="file" id="img" class="form-control">
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary"><i class="fas fa-save"></i> Save Change</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nama & Posisi -->
<div class="modal fade" id="modal-sm">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Info User</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Nama</label>
          <input type="text" class="form-control" value="<?= safe($data['nama_lengkap']); ?>" readonly>
        </div>
        <div class="form-group">
          <label>Position</label>
          <input type="text" class="form-control" value="<?= safe($data['position']); ?>" readonly>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    $('.form-checkbox').click(function(){
      if($(this).is(':checked')){
        $('.check').attr('type','text');
      } else {
        $('.check').attr('type','password');
      }
    });
});
</script>
