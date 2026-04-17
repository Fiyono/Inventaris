<a href="#" class="btn btn-primary float-right shadow" data-toggle="modal" data-target="#modal-default">
  <i class="fas fa-plus"></i> Tambah Catatan
</a>

<div class="row mt-4">
  <?php 
  $colors = ['#FFF176', '#A5D6A7', '#90CAF9', '#F48FB1', '#FFCC80']; // warna sticky notes
  $queryct = mysqli_query($koneksi, "SELECT * FROM catatan ORDER BY id_catatan DESC");
  while($datact = mysqli_fetch_array($queryct)){ 
    $randomColor = $colors[array_rand($colors)];
    $rotate = rand(-5,5); // biar mirip kertas ditempel
  ?>
    <div class="col-md-3 col-sm-6 mb-4" id="catatan-<?= $datact['id_catatan']; ?>">
      <div class="sticky-note shadow-sm" style="background:<?= $randomColor ?>; transform: rotate(<?= $rotate ?>deg);" data-toggle="modal" data-target="#modal<?= $datact['id_catatan']; ?>">
        <div class="note-content">
          <p><?= nl2br(htmlspecialchars($datact['isi'])); ?></p>
        </div>
        <small class="text-muted"><i class="far fa-clock"></i> <?= date('d M Y H:i', strtotime($datact['tgl_waktu'])); ?></small>
        <div class="note-actions">
          <a href="?page=catatan&upload=<?= urlencode($datact['isi']); ?>" 
             class="btn btn-sm btn-success rounded-circle" 
             title="Upload" onclick="return confirm('Upload catatan ini?')">
             <i class="fas fa-upload"></i>
          </a>
          <a href="?page=catatan&delete=<?= $datact['id_catatan']; ?>" 
             class="btn btn-sm btn-danger rounded-circle" 
             title="Hapus" onclick="return confirm('Hapus catatan ini?')">
             <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modal<?= $datact['id_catatan']; ?>">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-sticky-note"></i> Catatan</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <p><?= nl2br(htmlspecialchars($datact['isi'])); ?></p>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>
</div>

<!-- Modal Tambah Catatan -->
<div class="modal fade" id="modal-default">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="post">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Catatan</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <textarea class="form-control auto-expand" name="isi" placeholder="Tulis catatan..." rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" name="simpan" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Styling -->
<style>
.sticky-note {
  padding: 15px;
  border-radius: 6px;
  min-height: 150px;
  position: relative;
  transition: 0.3s;
  cursor: pointer;
  animation: popIn 0.5s ease;
}
.sticky-note:hover {
  transform: scale(1.05);
  z-index: 5;
}
.note-content {
  font-size: 14px;
  margin-bottom: 10px;
  word-wrap: break-word;
}
.note-actions {
  position: absolute;
  bottom: 10px;
  right: 10px;
  display: flex;
  gap: 6px;
}
@keyframes popIn {
  from {opacity: 0; transform: scale(0.8);}
  to {opacity: 1; transform: scale(1);}
}
/* Auto expand textarea */
textarea.auto-expand {
  overflow: hidden;
  resize: none;
}
</style>

<script>
// auto expand textarea
document.addEventListener("input", function(e){
  if(e.target.classList.contains("auto-expand")){
    e.target.style.height = "auto";
    e.target.style.height = (e.target.scrollHeight) + "px";
  }
});
</script>

<?php 
// Simpan catatan
if (isset($_POST['simpan'])) {
  $isi = $_POST['isi'];
  $date = date('Y-m-d H:i:s');
  $querycat = mysqli_query($koneksi, "INSERT INTO catatan VALUES('', '$isi','$date')");
  if ($querycat) {
      echo "<script>document.location.href='?page=catatan';</script>";
  }
}
// Upload catatan
if (isset($_GET['upload'])) {
  $upload = $_GET['upload'];
  mysqli_query($koneksi, "UPDATE notifikasi_catatan SET isi_chat = '$upload' WHERE id_notifcat = '1'");
  echo "<script>document.location.href='?page=catatan';</script>";
}
// Hapus catatan
if(isset($_GET['delete'])){
  $delete = $_GET['delete'];
  mysqli_query($koneksi, "DELETE FROM catatan WHERE id_catatan = '$delete'");
  echo "<script>document.location.href='?page=catatan';</script>"; 
}
?>
