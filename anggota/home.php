<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // pastikan koneksi database benar

// ambil data user dari session
if(!isset($_SESSION['id_user'])){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id_user'];

// ganti tbl_user sesuai nama tabel yang ada di database
$result = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user='$user_id' LIMIT 1");
$user = mysqli_fetch_assoc($result);
if(!$user){
    die("User tidak ditemukan!");
}


// ambil data kas (opsional)
?>

<!-- =========================================================== -->
<div class="row">
  <div class="col-md-12 col-sm-6 col-12">
    <div class="info-box callout callout-grey">
      <span class="info-box-icon text-orange elevation-1"><i class="far fa-bell"></i></span>
      <div class="info-box-content">
        <div class="direct-chat-msg">
          <div class="direct-chat text-left" id="notifcat">
            <!-- show limit 1 -->
          </div>  
        </div> 
        <div class="progress">
          <div class="progress-bar" style="width: 70%"></div>
        </div>
        <span class="progress-description">
          <i class="fas fa-bell"></i> NOTIFIKASI
        </span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header bg-nav">
        <h1 class="card-title text-bold float-left">BUAT TIKET PINJAM ALAT</h1>
      </div>
      <div class="card-body">
        <form action="anggota/proses/proses_tiketuser.php" method="post">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?? ''; ?>">
                <input type="text" name="nama_user" class="form-control" value="<?= $user['nama_lengkap'] ?? ''; ?>" readonly>
              </div>
              <div class="form-group">
                <label>PILIH BARANG</label>
                <select class="select2 form-control form-control-sm" name="id_brg" required>
                  <option value="">---PILIH BARANG---</option>
                  <?php 
                  $sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang");
                  while ($row = mysqli_fetch_assoc($sql)) {
                    echo "<option value='".$row['id_brg']."'>".$row['id_brg']." | ".$row['nama_brg']." | ".$row['jumlah_brg']."</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="penggunaan">TUJUAN PENGGUNAAN</label>
                <textarea class="form-control" name="tujuan_gunabarang" required placeholder="..."></textarea>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="jumlah_brg">JUMLAH BARANG YANG DIPINJAM</label>
                <input type="number" name="jumlah_brg" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="kembalikan">PERKIRAAN TANGGAL KEMBALIKAN BARANG</label>
                <input type="date" name="tgl_perkiraan_balik" class="form-control" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary" name="kirimtiket">KIRIM</button>
              </div>  
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-sm-12">
    <div class="card">
      <div class="card-header bg-nav">
        <h6 class="card-title text-bold float-left">TIKET ANDA</h6>
      </div>
      <div class="card-body">
        <table id="example1" class="table table-sm table-bordered table-hover">
          <thead>
            <tr>
              <th>ID TIKET</th>
              <th>NAMA BARANG</th>
              <th>JUMLAH BARANG</th>
              <th>TANGGAL PINJAM</th>
              <th>STATUS</th>
              <th>TUJUAN PENGGUNAAN</th>
              <th>TANGGAL PERKIRAAN KEMBALI</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $user_id_safe = $user['id_user'] ?? 0;
            $sql = mysqli_query($koneksi, "
              SELECT x.*, y.nama_brg 
              FROM tbl_tiketuser x 
              INNER JOIN tbl_barang y ON y.id_brg = x.id_brg 
              WHERE x.id_user = '$user_id_safe' 
              ORDER BY x.id_tiketuser DESC
            ");
            while ($row = mysqli_fetch_assoc($sql)) { ?>
              <tr>
                <td><?= $row['id_tiketuser'] ?? '-'; ?></td>
                <td><?= $row['nama_brg'] ?? '-'; ?></td>
                <td><?= $row['jumlah'] ?? '-'; ?></td>
                <td><?= $row['tgl_pinjam'] ?? '-'; ?></td>
                <td><?= $row['status'] ?? '-'; ?></td>
                <td><?= $row['tujuan_gunabarang'] ?? '-'; ?></td>
                <td><?= $row['tgl_perkiraan_balik'] ?? '-'; ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
