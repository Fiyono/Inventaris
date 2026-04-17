<?php
include "../koneksi.php";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> DATA STOK BARANG</h5>
                    <div>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-database"></i> Sumber: tb_barang_masuk
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Info -->
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        Data stok dihitung dari total jumlah barang di tabel <strong>tb_barang_masuk</strong>
                    </div>
                    
                    <!-- Filter -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="cariNama" placeholder="Cari nama barang...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filterKategori">
                                <option value="">Semua Kategori</option>
                                <?php 
                                $kat = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY nama_kategori");
                                while($k = mysqli_fetch_array($kat)) {
                                    echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info" onclick="filterData()">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="admin.php?page=request_tiket" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </a>
                        </div>
                    </div>
                    
                    <!-- Tabel Stok -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tblStok">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>ID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Type/Spesifikasi</th>
                                    <th>Merk</th>
                                    <th>No Rak</th>
                                    <th>Kategori</th>
                                    <th>Total Stok</th>
                                    <th>Terakhir Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query untuk menghitung total stok per barang
                                $sql = "
                                    SELECT 
                                        bm.id_barang,
                                        bm.nama_barang,
                                        bm.type_barang,
                                        bm.merk_barang,
                                        bm.norak_barang,
                                        tk.nama_kategori,
                                        MAX(bm.tanggal_barang_masuk) as last_update,
                                        SUM(bm.jumlah_barang) as total_stok
                                    FROM tb_barang_masuk bm
                                    LEFT JOIN tbl_kategori tk ON bm.id_kategori_barang = tk.id_kategori
                                    GROUP BY bm.id_barang, bm.nama_barang, bm.type_barang, 
                                             bm.merk_barang, bm.norak_barang, tk.nama_kategori
                                    ORDER BY bm.nama_barang
                                ";
                                
                                $query = mysqli_query($koneksi, $sql);
                                $no = 1;
                                $total_all = 0;
                                
                                if(mysqli_num_rows($query) > 0) {
                                    while($data = mysqli_fetch_array($query)) {
                                        $total_all += $data['total_stok'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo $data['id_barang']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['type_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['merk_barang']); ?></td>
                                    <td><?php echo $data['norak_barang']; ?></td>
                                    <td><?php echo $data['nama_kategori'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success" style="font-size: 14px;">
                                            <?php echo $data['total_stok']; ?> unit
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($data['last_update'])); ?></td>
                                    <td>
                                        <a href="admin.php?page=detail_barang&id=<?php echo $data['id_barang']; ?>" 
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="admin.php?page=riwayat_barang&id=<?php echo $data['id_barang']; ?>" 
                                           class="btn btn-sm btn-warning" title="Riwayat">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                        Belum ada data barang. 
                                        <a href="admin.php?page=request_tiket">Klik di sini untuk menambah barang.</a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="7" class="text-end"><strong>Total Semua Barang:</strong></td>
                                    <td class="text-center"><strong><?php echo $total_all; ?> unit</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterData() {
    var nama = document.getElementById('cariNama').value;
    var kategori = document.getElementById('filterKategori').value;
    
    var params = [];
    if(nama) params.push('nama=' + encodeURIComponent(nama));
    if(kategori) params.push('kategori=' + kategori);
    
    var url = 'admin.php?page=stok_barang';
    if(params.length > 0) {
        url += '&' + params.join('&');
    }
    
    window.location.href = url;
}

function resetFilter() {
    window.location.href = 'admin.php?page=stok_barang';
}

// DataTables
$(document).ready(function() {
    $('#tblStok').DataTable({
        "pageLength": 25,
        "order": [[2, 'asc']], // Urutkan berdasarkan nama
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

<!-- DataTables CSS & JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>