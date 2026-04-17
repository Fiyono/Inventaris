<?php
include "../koneksi.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> DATA BARANG MASUK</h5>
                    <a href="admin.php?page=request_tiket" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Tambah Barang
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Info -->
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        Total data: 
                        <?php 
                        $count = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_barang_masuk");
                        $data_count = mysqli_fetch_assoc($count);
                        echo '<strong>' . $data_count['total'] . ' record</strong>';
                        ?>
                    </div>
                    
                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>ID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Type</th>
                                    <th>Merk</th>
                                    <th>No Rak</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT 
                                        bm.*,
                                        tk.nama_kategori
                                        FROM tb_barang_masuk bm
                                        LEFT JOIN tbl_kategori tk ON bm.id_kategori_barang = tk.id_kategori
                                        ORDER BY bm.tanggal_barang_masuk DESC, bm.id_barang DESC";
                                
                                $query = mysqli_query($koneksi, $sql);
                                $no = 1;
                                
                                if(mysqli_num_rows($query) > 0) {
                                    while($data = mysqli_fetch_array($query)) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($data['tanggal_barang_masuk'])); ?></td>
                                    <td><strong><?php echo $data['id_barang']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['type_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['merk_barang']); ?></td>
                                    <td><?php echo $data['norak_barang']; ?></td>
                                    <td><?php echo $data['nama_kategori'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $data['jumlah_barang']; ?></span>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                        Belum ada data barang.<br>
                                        <a href="admin.php?page=request_tiket" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus"></i> Tambah Barang Pertama
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>