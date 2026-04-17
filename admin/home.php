<!-- STYLE -->
<style>
  /* Grid container */
  .products-row {
    display: flex;
    flex-wrap: wrap;
    margin: -10px;
  }

  .product-col {
    flex: 1 0 16.66%;
    max-width: 16.66%;
    padding: 10px;
    display: flex;
  }

  /* Card produk */
  .product-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    width: 100%;
  }

  .product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  }

  /* Gambar produk */
  .product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .product-card img:hover {
    transform: scale(1.05);
  }

  /* Footer selalu di bawah */
  .card-footer {
    margin-top: auto;
    text-align: center;
    padding: 10px;
    font-weight: bold;
  }

  /* Responsive Desktop (TIDAK BERUBAH - tetap seperti semula) */
  @media (max-width: 1200px) {
    .product-col { 
      flex: 0 0 20%; 
      max-width: 20%;  /* 5 kolom */
    }
  }
  
  @media (max-width: 992px) {
    .product-col { 
      flex: 0 0 25%; 
      max-width: 25%;  /* 4 kolom */
    }
  }
  
  /* ===== INI YANG DIUBAH: Mobile 768px ke bawah jadi 3 kolom ===== */
  @media (max-width: 768px) {
    .product-col { 
      flex: 0 0 33.33%; 
      max-width: 33.33%; /* 3 kolom (TETAP 3 KOLOM) */
    }
    
    /* Perbaikan tampilan card di mobile */
    .product-card img {
      height: 160px; /* Lebih kecil agar pas di mobile */
    }
    
    .card-footer {
      font-size: 13px;
      padding: 8px;
    }
  }
  
  /* ===== INI YANG DIUBAH: Mobile 576px ke bawah tetap 3 kolom ===== */
  @media (max-width: 576px) {
    .product-col { 
      flex: 0 0 33.33%; 
      max-width: 33.33%; /* TETAP 3 kolom (bukan 2 kolom seperti sebelumnya) */
    }
    
    .product-card img {
      height: 130px;
    }
    
    .card-footer {
      font-size: 12px;
      padding: 6px;
    }
  }
  
  /* Tambahan untuk layar sangat kecil (480px) agar tetap rapi */
  @media (max-width: 480px) {
    .product-col { 
      flex: 0 0 33.33%; 
      max-width: 33.33%; /* Tetap 3 kolom */
    }
    
    .product-card img {
      height: 110px;
    }
    
    .card-footer {
      font-size: 11px;
      padding: 5px;
    }
  }

  .search-bar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: white;
    padding: 10px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  /* Memastikan modal tetap responsif di mobile */
  @media (max-width: 576px) {
    .modal-body .row {
      flex-direction: column;
    }
    .modal-body .col-md-5, 
    .modal-body .col-md-7 {
      width: 100%;
      max-width: 100%;
    }
    .modal-body .col-md-5 {
      margin-bottom: 15px;
    }
    .table-sm th, 
    .table-sm td {
      font-size: 12px;
      padding: 4px;
    }
  }
</style>

<div class="row">
  <div class="col-md-12">
    <!-- Kolom Pencarian -->
    <form method="POST" class="search-bar mb-3">
      <div class="input-group">
        <input 
          type="text" 
          name="search" 
          class="form-control" 
          placeholder="Cari barang..." 
          autocomplete="off"
          value="<?= htmlspecialchars($_POST['search'] ?? '') ?>"
        >
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Cari</button>
        </div>
      </div>
    </form>

    <div class="products-row">
      <?php 
      if (isset($_POST['search']) && $_POST['search'] != '') {
        $search = mysqli_real_escape_string($koneksi, $_POST['search']);
        $sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang 
        WHERE LOWER(id_brg) LIKE LOWER('%$search%')
        OR LOWER(nama_brg) LIKE LOWER('%$search%')
        OR LOWER(merk_brg) LIKE LOWER('%$search%')
        OR LOWER(spesifikasi_brg) LIKE LOWER('%$search%')
        OR LOWER(norak_brg) LIKE LOWER('%$search%')");
      }
      else if (isset($_POST['kategori'])) {
          $sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang 
          WHERE id_kategori = '".$_POST['kategori']."' 
          ORDER BY id_brg DESC");
      } 
      else {
          $sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang 
          ORDER BY id_brg DESC");
      }
      
      while ($row = mysqli_fetch_array($sql)) { 
      ?>
      <div class="product-col">
        <div class="card product-card" data-toggle="modal" data-target="#quickView<?= $row['id_brg']; ?>">
          <img src="dist/upload_img/<?= $row['gambar_brg']; ?>" alt="<?= htmlspecialchars($row['nama_brg']); ?>" onerror="this.src='dist/upload_img/default.jpg'">
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
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body row">
              <div class="col-md-5">
                <img src="dist/upload_img/<?= $row['gambar_brg']; ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($row['nama_brg']); ?>" onerror="this.src='dist/upload_img/default.jpg'">
              </div>
              <div class="col-md-7">
                <table class="table table-borderless table-sm">
                  <tr>
                    <th>NAMA BARANG</th>
                    <td>: <?= htmlspecialchars($row['nama_brg']); ?></td>
                  </tr>
                  <tr>
                    <th>ID BARANG</th>
                    <td>: <?= htmlspecialchars($row['id_brg']); ?></td>
                  </tr>
                  <tr>
                    <th>TYPE BARANG</th>
                    <td>: <?= htmlspecialchars($row['spesifikasi_brg']); ?></td>
                  </tr>
                  <tr>
                    <th>MERK BARANG</th>
                    <td>: <?= htmlspecialchars($row['merk_brg']); ?></td>
                  </tr>
                  <tr>
                    <th>NORAK BARANG</th>
                    <td>: <?= htmlspecialchars($row['norak_brg']); ?></td>
                  </tr>
                  <tr>
                    <th>JUMLAH BARANG</th>
                    <td>: <?= htmlspecialchars($row['jumlah_brg']); ?></td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <a href="admin.php?page=detailbarang&id=<?= urlencode($row['id_brg']); ?>" class="btn btn-success">
                LIHAT DETAIL LENGKAP
              </a>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Modal -->

      <?php } ?>
    </div>    
  </div>
</div>