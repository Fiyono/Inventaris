<?php 
// detail_barang.php - Merapihkan dan Memperbaiki Logika Perhitungan Stok

if (isset($_GET['id'])) {
    $id_brg = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // 1. Ambil data barang (Stok Total)
    $sql_brg = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_brg = '$id_brg'");
    $row = mysqli_fetch_array($sql_brg);

    if (!$row) {
        echo "<div class='alert alert-danger'>Data barang tidak ditemukan.</div>";
        exit;
    }

    // 2. Ambil kategori
    $catrg_query = mysqli_query($koneksi, "SELECT nama_kategori FROM tbl_kategori WHERE id_kategori = '".$row['id_kategori']."'");
    $catrg = mysqli_fetch_array($catrg_query);
    $nama_kategori = $catrg['nama_kategori'] ?? 'Tidak Diketahui';

    // 3. Perbaikan Logika: Hitung pinjaman AKTIF
    // Hanya menjumlahkan barang yang statusnya BUKAN 'dikembalikan'
    $pin_query = mysqli_query($koneksi, "
        SELECT 
            COALESCE(SUM(jumlah_pinjam), 0) AS jml_pinjam 
        FROM tbl_pinjaman 
        WHERE id_brg = '$id_brg' AND status != 'dikembalikan'
    ");
    $pin = mysqli_fetch_array($pin_query);
    
    $jml_pinjam = (int) $pin['jml_pinjam'];
    
    // Perhitungan Sisa Stok
    $stok_total = (int) $row['jumlah_brg'];
    $sisa = $stok_total - $jml_pinjam;
    
    // Pastikan sisa stok tidak negatif (walaupun harusnya dicegah di proses pinjam/ambil)
    $sisa = max(0, $sisa); 

    // Tentukan nilai max untuk form Ambil dan Pinjam
    $max_stok_form = $sisa;
?>

<div class="row">
    <div class="col-sm-4 card p-3">
        <img src="dist/upload_img/<?= htmlspecialchars($row['gambar_brg']); ?>" 
             alt="<?= htmlspecialchars($row['nama_brg']); ?>" 
             width="100%" 
             class="img-circle img-fluid rounded">
    </div>

    <div class="col-sm-8 card p-3">
        <table class="table table-striped table-valign-middle table-hover table-dark rounded">
            <tbody>
                <tr>
                    <th class="col-3">ID BARANG</th>
                    <td>: <?= htmlspecialchars($row['id_brg']); ?></td>
                </tr>
                <tr>
                    <th>NAMA BARANG</th>
                    <td>: <?= htmlspecialchars($row['nama_brg']); ?></td>
                </tr>
                <tr>
                    <th>SPESIFIKASI</th>
                    <td>: <?= htmlspecialchars($row['spesifikasi_brg']); ?></td>
                </tr>
                <tr>
                    <th>MERK</th>
                    <td>: <?= htmlspecialchars($row['merk_brg']); ?></td>
                </tr>
                <tr>
                    <th>NO RAK</th>
                    <td>: <?= htmlspecialchars($row['norak_brg']); ?></td>
                </tr>
                <tr>
                    <th>TANGGAL MASUK</th>
                <td>: 
                    <?= !empty($row['tgl_masuk_brg']) && $row['tgl_masuk_brg'] != "0000-00-00"
                        ? date('d-m-Y', strtotime($row['tgl_masuk_brg']))
                        : '-' ?>
                </td>
                <tr>
                    <th>KATEGORI BARANG</th>
                    <td>: <?= htmlspecialchars($nama_kategori); ?></td>
                </tr>
                <tr>
                    <th class="text">STOK SAAT INI</th>
                    <td>: 
                        <span class="font-weight-bold"><?= number_format($stok_total); ?></span>
                        (Dipinjam: <span class="text"><?= number_format($jml_pinjam); ?></span> | 
                        Sisa: <span class="text"><?= number_format($sisa); ?></span>)
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="form-group mt-3">
            <a href="#" class="btn bg-indigo mt-2" data-toggle="modal" data-target="#modal-ambil">
                <i class="fas fa-level-down-alt"></i> AMBIL
            </a>
            <a href="#" class="btn btn-info mt-2" data-toggle="modal" data-target="#modal-pinjam">
                <i class="fas fa-box"></i> PINJAM
            </a>
            <a href="#" class="btn btn-success mt-2" data-toggle="modal" data-target="#modal-tambah">
                <i class="fas fa-plus-circle"></i> TAMBAH BARANG
            </a>
            <a href="admin.php" class="btn btn-primary mt-2">
                <i class="fas fa-step-backward"></i> KEMBALI
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-ambil">
    <div class="modal-dialog">
        <div class="modal-content bg-indigo">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-level-down-alt"></i> AMBIL BARANG</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="admin/proses/proses_ambil.php" method="post">
                    <input type="hidden" name="id_brg" value="<?= htmlspecialchars($row['id_brg']); ?>">
                    <input type="hidden" name="barcode_brg" value="<?= htmlspecialchars($row['barcode_brg']); ?>">

                    <div class="form-group">
                        <label>NAMA USER</label>
                        <select class="select2 form-control form-control-sm" name="id_user" required>
                            <option>--PILIH--</option>";
                            <?php 
                            $user = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap");
                            while ($u = mysqli_fetch_array($user)) {
                                echo "<option value='".htmlspecialchars($u['id_user'])."'>".htmlspecialchars($u['nama_lengkap'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>TANGGAL BARANG KELUAR</label>
                        <input type="date" name="tgl_brg_keluar" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>JUMLAH BARANG KELUAR</label>
                        <input type="number" name="jumlah_brg" class="form-control form-control-sm" min="1" max="<?= $max_stok_form; ?>" required>
                        <small class="text-white">Stok Tersedia: <?= $max_stok_form; ?></small>
                    </div>
                    <div class="form-group">
                        <label>ALAMAT RUANG PENGGUNAAN</label>
                        <select class="form-control form-control-sm" name="alamat_ruang" required>
                            <option value="">-- PILIH RUANG --</option>
                            <option>RUANG LAB C1</option>
                            <option>RUANG LAB C2</option>
                            <option>RUANG LAB C3</option>
                            <option>RUANG LAB C4</option>
                            <option>RUANG LAB C5</option>
                            <option>RUANG AULA</option>
                            <option>RUANG GURU</option>
                            <option>RUANG BK</option>
                            <option>RUANG INSTRUKTUR PPLG</option>
                            <option>SARPRAS</option>
                            <option>SAPRA MART</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>TUJUAN PENGGUNAAN BARANG</label>
                        <textarea name="tujuan_gunabarang" class="form-control form-control-sm" required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" name="simpanambil" class="btn btn-primary"><i class="fas fa-save"></i> SIMPAN</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-pinjam">
    <div class="modal-dialog">
        <div class="modal-content bg-info">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-box"></i> PINJAM BARANG</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="admin/proses/proses_pinjam.php" method="post">
                    <input type="hidden" name="id_brg" value="<?= htmlspecialchars($row['id_brg']); ?>">
                    <input type="hidden" name="barcode_brg" value="<?= htmlspecialchars($row['barcode_brg']); ?>">

                    <div class="form-group">
                        <label>NAMA PEMINJAM</label>
                        <select class="select2 form-control form-control-sm" name="id_user" required>
                        <option>--PILIH--</option>";
                            <?php 
                            $use = mysqli_query($koneksi, "SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap");
                            while ($p = mysqli_fetch_array($use)) {
                                echo "<option value='".htmlspecialchars($p['id_user'])."'>".htmlspecialchars($p['nama_lengkap'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>TANGGAL PINJAM</label>
                        <input type="date" 
                            name="tgl_pinjam" 
                            id="tgl_pinjam"
                            class="form-control form-control-sm" 
                            value="<?= date('Y-m-d'); ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label>TANGGAL PERKIRAAN KEMBALI</label>
                        <input type="date" 
                            name="tgl_perkiraan_balik" 
                            id="tgl_perkiraan_balik"
                            class="form-control form-control-sm" 
                            required>
                    </div>
                    <div class="form-group">
                        <label>JUMLAH PINJAM</label>
                        <input type="number" name="jumlah_brg" class="form-control form-control-sm" min="1" max="<?= $max_stok_form; ?>" required>
                        <small class="text-white">Stok Tersedia: <?= $max_stok_form; ?></small>
                    </div>
                    <div class="form-group">
                        <label>TUJUAN PENGGUNAAN BARANG</label>
                        <textarea name="tujuan_gunabarang" class="form-control form-control-sm" required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" name="simpanpinjam" class="btn btn-primary"><i class="fas fa-save"></i> SIMPAN</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-plus-circle"></i> TAMBAH BARANG</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="admin/proses/proses_tambah.php" method="post">
                    <input type="hidden" name="id_brg" value="<?= htmlspecialchars($row['id_brg']); ?>">
                    <input type="hidden" name="barcode_brg" value="<?= htmlspecialchars($row['barcode_brg']); ?>">
                    <input type="hidden" name="nama_brg" value="<?= htmlspecialchars($row['nama_brg']); ?>">
                    <input type="hidden" name="spesifikasi_brg" value="<?= htmlspecialchars($row['spesifikasi_brg']); ?>">
                    <input type="hidden" name="merk_brg" value="<?= htmlspecialchars($row['merk_brg']); ?>">

                    <div class="form-group">
                        <label>JUMLAH TAMBAHAN</label>
                        <input type="number" name="jumlah_tambah" class="form-control form-control-sm" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>TANGGAL PENAMBAHAN</label>
                        <input type="date" name="tgl_tambah" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>KETERANGAN</label>
                        <textarea id="keterangan"
                            name="keterangan"
                            class="form-control form-control-sm"
                            placeholder="Contoh: Penambahan stok baru atau penggantian barang"
                            required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" name="simpantambah" class="btn btn-primary"><i class="fas fa-save"></i> SIMPAN</button>
                        <button type="button" class="btn btn-light" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const tglPinjam = document.getElementById("tgl_pinjam");
    const tglKembali = document.getElementById("tgl_perkiraan_balik");

    function setTanggalKembali() {
        if (tglPinjam.value) {
            let date = new Date(tglPinjam.value);
            date.setDate(date.getDate() + 7);

            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');

            tglKembali.value = `${year}-${month}-${day}`;
        }
    }

    // Set otomatis saat halaman dibuka
    setTanggalKembali();

    // Update otomatis kalau tanggal pinjam diganti
    tglPinjam.addEventListener("change", setTanggalKembali);

});



document.getElementById("keterangan").addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // mencegah enter jadi baris baru
        this.form.submit(); // submit form seperti klik OK
    }
});

</script>
<?php 
} // end isset
?>