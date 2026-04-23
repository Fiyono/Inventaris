<?php 
// detail_barang.php - Detail Barang dengan Perhitungan Stok yang Benar

if (isset($_GET['id'])) {
    $id_brg = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // 1. Ambil data barang (Stok saat ini)
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

    // 3. Hitung TOTAL yang dipinjam (status 'Dipinjam' - belum lunas)
    $pin_query = mysqli_query($koneksi, "
        SELECT COALESCE(SUM(jumlah_pinjam), 0) AS total_dipinjam
        FROM tbl_pinjaman 
        WHERE id_brg = '$id_brg' AND status = 'Dipinjam'
    ");
    $pin = mysqli_fetch_array($pin_query);
    $total_dipinjam = (int) $pin['total_dipinjam'];
    
    // 4. Hitung TOTAL yang sudah dikembalikan (dari history untuk peminjaman aktif)
    $kembali_query = mysqli_query($koneksi, "
        SELECT COALESCE(SUM(h.jumlahbrg_kembali), 0) AS total_kembali
        FROM tbl_history_pinjam h
        JOIN tbl_pinjaman p ON h.id_pinjaman = p.id_pinjaman
        WHERE p.id_brg = '$id_brg' AND p.status = 'Dipinjam'
    ");
    $kembali = mysqli_fetch_array($kembali_query);
    $total_kembali = (int) $kembali['total_kembali'];
    
    // 5. Hitung SISA yang BELUM dikembalikan (yang sedang dipinjam)
    $sedang_dipinjam = $total_dipinjam - $total_kembali;
    
    // Stok Tersedia = Stok Fisik (karena stok fisik sudah mencerminkan stok yang tersedia)
    $stok_tersedia = (int) $row['jumlah_brg'];
    
    // Tentukan nilai max untuk form Ambil dan Pinjam
    $max_stok_form = $stok_tersedia;
?>

<style>
/* Detail Barang Mobile Responsive */
@media (max-width: 768px) {
    .detail-container {
        flex-direction: column;
    }
    .detail-gambar {
        margin-bottom: 15px;
    }
    .detail-info table {
        font-size: 12px;
    }
    .detail-info th, .detail-info td {
        padding: 8px 5px !important;
    }
    .btn-group-mobile {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }
    .btn-group-mobile a {
        margin-top: 0 !important;
        padding: 6px 12px;
        font-size: 12px;
    }
    .modal-content {
        margin: 10px;
    }
}
</style>

<div class="row detail-container">
    <div class="col-sm-12 col-md-4 card p-3 detail-gambar">
        <img src="dist/upload_img/<?= htmlspecialchars($row['gambar_brg']); ?>" 
             alt="<?= htmlspecialchars($row['nama_brg']); ?>" 
             width="100%" 
             class="img-fluid rounded"
             style="object-fit: cover; max-height: 300px;"
             onerror="this.src='dist/upload_img/default.png'">
    </div>

    <div class="col-sm-12 col-md-8 card p-3 detail-info">
        <table class="table table-striped table-valign-middle table-hover table-bordered">
            <tbody>
                <tr>
                    <th style="width: 35%;">ID BARANG</th>
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
                    <th>SEDANG DIPINJAM</th>
                    <td>: 
                        <span><?= number_format($sedang_dipinjam); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>STOK TERSEDIA</th>
                    <td>: 
                        <span><?= number_format($stok_tersedia); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="form-group mt-3 btn-group-mobile">
            <a href="#" class="btn bg-indigo" data-toggle="modal" data-target="#modal-ambil">
                <i class="fas fa-level-down-alt"></i> AMBIL
            </a>
            <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modal-pinjam">
                <i class="fas fa-box"></i> PINJAM
            </a>
            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modal-tambah">
                <i class="fas fa-plus-circle"></i> TAMBAH STOK
            </a>
            <a href="admin.php" class="btn btn-primary">
                <i class="fas fa-step-backward"></i> KEMBALI
            </a>
        </div>
    </div>
</div>

<!-- MODAL AMBIL BARANG -->
<div class="modal fade" id="modal-ambil" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-indigo">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-level-down-alt"></i> AMBIL BARANG</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="admin/proses/proses_ambil.php" method="post">
                    <input type="hidden" name="id_brg" value="<?= htmlspecialchars($row['id_brg']); ?>">
                    <input type="hidden" name="barcode_brg" value="<?= htmlspecialchars($row['barcode_brg']); ?>">

                    <div class="form-group">
                        <label>NAMA USER</label>
                        <select class="form-control select2" name="id_user" required style="width:100%;">
                            <option value="">-- PILIH USER --</option>
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
                        <input type="date" name="tgl_brg_keluar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>JUMLAH BARANG KELUAR</label>
                        <input type="number" name="jumlah_brg" class="form-control" min="1" max="<?= $max_stok_form; ?>" required>
                        <small class="text-white">Stok Tersedia: <?= $max_stok_form; ?></small>
                    </div>
                    <div class="form-group">
                        <label>ALAMAT RUANG PENGGUNAAN</label>
                        <select class="form-control" name="alamat_ruang" required>
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
                        <textarea name="tujuan_gunabarang" class="form-control" rows="2" required></textarea>
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

<!-- MODAL PINJAM BARANG -->
<div class="modal fade" id="modal-pinjam" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-info">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-box"></i> PINJAM BARANG</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="admin/proses/proses_pinjam.php" method="post">
                    <input type="hidden" name="id_brg" value="<?= htmlspecialchars($row['id_brg']); ?>">
                    <input type="hidden" name="barcode_brg" value="<?= htmlspecialchars($row['barcode_brg']); ?>">

                    <div class="form-group">
                        <label>NAMA PEMINJAM</label>
                        <select class="form-control select2" name="id_user" required style="width:100%;">
                            <option value="">-- PILIH PEMINJAM --</option>
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
                        <input type="date" name="tgl_pinjam" id="tgl_pinjam_modal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>TANGGAL PERKIRAAN KEMBALI</label>
                        <input type="date" name="tgl_perkiraan_balik" id="tgl_perkiraan_balik_modal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>JUMLAH PINJAM</label>
                        <input type="number" name="jumlah_brg" class="form-control" min="1" max="<?= $max_stok_form; ?>" required>
                        <small class="text-white">Stok Tersedia: <?= $max_stok_form; ?></small>
                    </div>
                    <div class="form-group">
                        <label>TUJUAN PENGGUNAAN BARANG</label>
                        <textarea name="tujuan_gunabarang" class="form-control" rows="2" required></textarea>
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

<!-- MODAL TAMBAH STOK BARANG -->
<div class="modal fade" id="modal-tambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-plus-circle"></i> TAMBAH STOK BARANG</h4>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
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
                        <input type="number" name="jumlah_tambah" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>TANGGAL PENAMBAHAN</label>
                        <input type="date" name="tgl_tambah" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>KETERANGAN</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" 
                            placeholder="Contoh: Penambahan stok baru atau penggantian barang" required></textarea>
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
    // Auto-set tanggal perkiraan kembali (H+7)
    const tglPinjam = document.getElementById("tgl_pinjam_modal");
    const tglKembali = document.getElementById("tgl_perkiraan_balik_modal");

    function setTanggalKembali() {
        if (tglPinjam && tglPinjam.value) {
            let date = new Date(tglPinjam.value);
            date.setDate(date.getDate() + 7);
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            if (tglKembali) {
                tglKembali.value = `${year}-${month}-${day}`;
            }
        }
    }

    if (tglPinjam) {
        setTanggalKembali();
        tglPinjam.addEventListener("change", setTanggalKembali);
    }

    // Enter submit untuk textarea keterangan
    const keterangan = document.getElementById("keterangan");
    if (keterangan) {
        keterangan.addEventListener("keydown", function(e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    // Inisialisasi Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('.modal')
        });
    }
});
</script>

<?php 
} // end isset
?>