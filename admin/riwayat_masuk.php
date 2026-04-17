<?php
include "../koneksi.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> RIWAYAT BARANG MASUK</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Filter Tanggal -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Dari Tanggal</label>
                            <input type="date" class="form-control" id="dari_tanggal" 
                                   value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Sampai Tanggal</label>
                            <input type="date" class="form-control" id="sampai_tanggal" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Nama Barang</label>
                            <input type="text" class="form-control" id="cari_nama" 
                                   placeholder="Cari nama barang...">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label><br>
                            <button class="btn btn-info" onclick="filterRiwayat()">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tabel Riwayat -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tblRiwayat">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Masuk</th>
                                    <th>ID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Type/Spesifikasi</th>
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
                                        ORDER BY bm.tanggal_barang_masuk DESC, bm.id_barang DESC
                                        LIMIT 100";
                                
                                $query = mysqli_query($koneksi, $sql);
                                $no = 1;
                                $total = 0;
                                
                                while($data = mysqli_fetch_array($query)) {
                                    $total += $data['jumlah_barang'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data['tanggal_barang_masuk'])); ?></td>
                                    <td><?php echo $data['id_barang']; ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['type_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['merk_barang']); ?></td>
                                    <td><?php echo $data['norak_barang']; ?></td>
                                    <td>
                                        <?php 
                                        if(!empty($data['nama_kategori'])) {
                                            echo $data['nama_kategori'];
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">+<?php echo $data['jumlah_barang']; ?></span>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="8" class="text-end"><strong>Total Barang Masuk:</strong></td>
                                    <td class="text-end"><strong><?php echo $total; ?> item</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Tombol Export -->
                    <div class="mt-3">
                        <a href="export_riwayat_masuk.php" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export ke Excel
                        </a>
                        <a href="admin.php?page=request_tiket" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Barang Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterRiwayat() {
    var dari = document.getElementById('dari_tanggal').value;
    var sampai = document.getElementById('sampai_tanggal').value;
    var nama = document.getElementById('cari_nama').value;
    
    // Redirect dengan parameter filter
    var url = 'admin.php?page=riwayat_masuk';
    var params = [];
    
    if(dari) params.push('dari=' + dari);
    if(sampai) params.push('sampai=' + sampai);
    if(nama) params.push('nama=' + encodeURIComponent(nama));
    
    if(params.length > 0) {
        url += '&' + params.join('&');
    }
    
    window.location.href = url;
}

function resetFilter() {
    document.getElementById('dari_tanggal').value = '';
    document.getElementById('sampai_tanggal').value = '';
    document.getElementById('cari_nama').value = '';
    window.location.href = 'admin.php?page=riwayat_masuk';
}
</script>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#tblRiwayat').DataTable({
        "pageLength": 25,
        "order": [[1, 'desc']],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(disaring dari _MAX_ total data)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Berikutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>