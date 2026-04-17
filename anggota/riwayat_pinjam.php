<?php
$id_user = $_SESSION['id_user']; // ambil id_user dari session
?>
<link rel="stylesheet" href="assets/css/custom.css">
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header bg-nav">
                <h6 class="card-title text-bold float-left">RIWAYAT</h6>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-sm table-hover table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA BARANG</th>
                            <th>ID BARANG</th>
                            <th>JUMLAH PINJAM</th>
                            <th>JUMLAH KEMBALI</th>
                            <th>TUJUAN PENGGUNAAN</th>
                            <th>TANGGAL PINJAM</th>
                            <th>TANGGAL KEMBALI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no=1;
                            // SQL query with JOIN to get the item name
                            $sql = mysqli_query($koneksi, "SELECT 
                                    hp.*, 
                                    b.nama_brg 
                                FROM 
                                    tbl_history_pinjam hp
                                JOIN 
                                    tbl_barang b ON hp.id_brg = b.id_brg
                                WHERE 
                                    hp.id_user = '$id_user'");
                            while($row = mysqli_fetch_assoc($sql)){ ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nama_brg']; ?></td>
                                    <td><?= $row['id_brg']; ?></td>
                                    <td><?= $row['jumlahbrg_pinjam']; ?></td>
                                    <td><?= $row['jumlahbrg_kembali']; ?></td>
                                    <td><?= $row['tujuan_gunabarang']; ?></td>
                                    <td><?= $row['tgl_pinjam']; ?></td>
                                    <td><?= $row['tgl_kembali']; ?></td>
                                </tr>
                            <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>