<?php
include "koneksi.php";

$search = trim($_POST['search'] ?? '');
$search_sql = '';

if ($search != '') {
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $search_sql = "WHERE LOWER(id_brg) LIKE LOWER('%$search_safe%')
                    OR LOWER(nama_brg) LIKE LOWER('%$search_safe%')
                    OR LOWER(merk_brg) LIKE LOWER('%$search_safe%')
                    OR LOWER(spesifikasi_brg) LIKE LOWER('%$search_safe%')
                    OR LOWER(norak_brg) LIKE LOWER('%$search_safe%')";
}

$sql = "SELECT * FROM tbl_barang $search_sql ORDER BY id_brg DESC";
$result = mysqli_query($koneksi, $sql);

if(mysqli_num_rows($result) > 0){
    echo '<div class="products-row">';
    while($row = mysqli_fetch_assoc($result)){
        ?>
        <div class="product-col">
          <div class="card product-card" data-toggle="modal" data-target="#quickView<?= $row['id_brg']; ?>">
            <img src="dist/upload_img/<?= htmlspecialchars($row['gambar_brg']); ?>" alt="<?= htmlspecialchars($row['nama_brg']); ?>" onerror="this.src='dist/upload_img/default.jpg'">
            <div class="card-footer">
              <?= htmlspecialchars($row['nama_brg']); ?>
            </div>
          </div>
        </div>

        <!-- Quick View Modal -->
        <div class="modal fade" id="quickView<?= $row['id_brg']; ?>" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><?= htmlspecialchars($row['nama_brg']); ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body row">
                <div class="col-md-5">
                  <img src="dist/upload_img/<?= htmlspecialchars($row['gambar_brg']); ?>" class="img-fluid rounded" onerror="this.src='dist/upload_img/default.jpg'">
                </div>
                <div class="col-md-7">
                  <table class="table table-borderless table-sm">
                    <tr><th>NAMA BARANG</th><td>: <?= htmlspecialchars($row['nama_brg']); ?></td></tr>
                    <tr><th>ID BARANG</th><td>: <?= $row['id_brg']; ?></td></tr>
                    <tr><th>TYPE BARANG</th><td>: <?= htmlspecialchars($row['spesifikasi_brg']); ?></td></tr>
                    <tr><th>MERK BARANG</th><td>: <?= htmlspecialchars($row['merk_brg']); ?></td></tr>
                    <tr><th>NORAK BARANG</th><td>: <?= htmlspecialchars($row['norak_brg']); ?></td></tr>
                    <tr><th>JUMLAH BARANG</th><td>: <?= $row['jumlah_brg']; ?> pcs</td></tr>
                  甚
                </div>
              </div>
              <div class="modal-footer">
                <a href="admin.php?page=detailbarang&id=<?= $row['id_brg']; ?>" class="btn btn-success">LIHAT DETAIL LENGKAP</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo '<div class="text-center p-5">
            <i class="fas fa-box-open" style="font-size: 50px; color: #ccc;"></i>
            <p class="mt-3">Barang tidak ditemukan.</p>
          </div>';
}
?>