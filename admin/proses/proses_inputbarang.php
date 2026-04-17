<?php
require_once("../../koneksi.php");

if (!isset($_POST['simpan'])) {
    header("Location: ../../admin.php?page=home");
    exit;
}

// ================== AMBIL DATA ==================
$id_brg = mysqli_real_escape_string($koneksi, $_POST['id_brg']);
$nama_brg    = mysqli_real_escape_string($koneksi, $_POST['nama_brg']);
$spec_brg    = mysqli_real_escape_string($koneksi, $_POST['spesifikasi_brg']);
$merk_brg    = mysqli_real_escape_string($koneksi, $_POST['merk_brg']);
$norak_brg   = mysqli_real_escape_string($koneksi, $_POST['norak_brg']);
$tgl_masuk   = mysqli_real_escape_string($koneksi, $_POST['tgl_masuk_brg']);
$id_kategori = intval($_POST['id_kategori']);
$jumlah_brg  = intval($_POST['jumlah_brg']);
$keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? 'Request Tiket');

// VALIDASI WAJIB
if (empty($id_brg)) {
    die("ID Barang tidak boleh kosong atau 0.");
}

if ($jumlah_brg < 1) {
    die("Jumlah barang minimal 1.");
}

// ================== UPLOAD GAMBAR ==================
$folderPath = "../../dist/upload_img/";
$fileName = "";

if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

if (!empty($_POST['image']) && strpos($_POST['image'], 'data:image') === 0) {
    $image_parts = explode(";base64,", $_POST['image']);
    if (isset($image_parts[1])) {
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = uniqid() . '.png';
        file_put_contents($folderPath . $fileName, $image_base64);
    }
}

// ================== CEK BARANG ==================
$cek = mysqli_query($koneksi, "
    SELECT * FROM tbl_barang WHERE id_brg = '$id_brg'
");

if (mysqli_num_rows($cek) > 0) {

    // UPDATE STOK
    $update = mysqli_query($koneksi, "
        UPDATE tbl_barang 
        SET jumlah_brg = jumlah_brg + $jumlah_brg
        WHERE id_brg = '$id_brg'
    ");

    if (!$update) {
        die("Gagal update stok: " . mysqli_error($koneksi));
    }

} else {

    // INSERT BARU
    $insert = mysqli_query($koneksi, "
        INSERT INTO tbl_barang
        (id_brg, barcode_brg, nama_brg, gambar_brg,
         norak_brg, tgl_masuk_brg, spesifikasi_brg,
         merk_brg, id_kategori, jumlah_brg)
        VALUES
        ('$id_brg', '$id_brg', '$nama_brg', '$fileName',
         '$norak_brg', '$tgl_masuk', '$spec_brg',
         '$merk_brg', '$id_kategori', '$jumlah_brg')
    ");

    if (!$insert) {
        die("Gagal insert barang: " . mysqli_error($koneksi));
    }
}

// ================== SIMPAN RIWAYAT ==================
mysqli_query($koneksi, "
    INSERT INTO tbl_riwayat_tambah
    (id_brg, nama_brg, spesifikasi_brg, merk_brg,
     jumlah_tambah, tanggal, keterangan)
    VALUES
    ('$id_brg', '$nama_brg', '$spec_brg', '$merk_brg',
     '$jumlah_brg', NOW(), '$keterangan')
");

// ================== AMBIL TOTAL STOK ==================
$get = mysqli_query($koneksi, "
    SELECT jumlah_brg FROM tbl_barang WHERE id_brg = '$id_brg'
");

$data = mysqli_fetch_assoc($get);
$total_stok = $data['jumlah_brg'] ?? 0;

// ================== SUKSES ==================
echo "<script>
alert('✅ DATA BERHASIL DISIMPAN!\\nTotal stok sekarang: $total_stok unit');
window.location.href='../../admin.php?page=request_tiket';
</script>";
exit;
?>
