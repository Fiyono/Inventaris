<?php 
if (isset($_GET['id'])) {
	$sql = mysqli_query($koneksi, "SELECT * FROM tbl_barang WHERE id_brg = '".$_GET['id']."'");
  	$row = mysqli_fetch_array($sql);
}

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin.php?page=master_data';
?>

<style>
	/* --- STYLE PREMIUM ELEGANT + MOBILE FRIENDLY --- */
	.card-premium {
		border-radius: 18px;
		border: none;
		box-shadow: 0 8px 25px rgba(0,0,0,0.08);
	}

	.table-premium th {
		background: #f8f9fc !important;
		font-weight: 700;
		color: #4a4a4a;
	}

	.table-premium td {
		background: #ffffff;
	}

	.btn-modern {
		border-radius: 12px !important;
		font-weight: 600;
		padding: 10px 20px;
		transition: 0.3s;
	}

	.btn-modern:hover {
		transform: translateY(-2px);
		box-shadow: 0 6px 14px rgba(0,0,0,0.15);
	}

	.img-preview {
		width: 100%;
		height: 450px; /* tinggi tetap */
		object-fit: cover; /* supaya tidak gepeng */
		border-radius: 16px;
		box-shadow: 0 6px 18px rgba(0,0,0,0.15);
	}

	.heading-title {
		font-weight: 700;
		font-size: 1.2rem;
		color: #34495e;
	}

	.card-delete {
		border-radius: 16px;
		border: none;
		box-shadow: 0 8px 25px rgba(255,0,0,0.15);
	}

	/* --- MOBILE FRIENDLY (tambahan) --- */
	@media (max-width: 768px) {
		.img-preview {
			height: 250px; /* lebih kecil di mobile */
			margin-bottom: 15px;
		}

		.btn-modern {
			padding: 8px 12px;
			font-size: 0.9rem;
			width: 100%;
			margin-bottom: 8px;
		}

		.table-premium th,
		.table-premium td {
			font-size: 0.85rem;
			padding: 8px 6px;
		}

		.heading-title {
			font-size: 1rem;
		}

		.card-body {
			padding: 1rem;
		}

		/* stack tombol di mobile */
		.mt-3 {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}

		.mt-3 a,
		.mt-3 button {
			width: 100%;
			margin: 0;
		}

		/* card hapus lebih rapi */
		.card-delete .card-body {
			text-align: center;
		}

		/* form input lebih enak di mobile */
		input.form-control,
		select.form-control,
		textarea.form-control {
			font-size: 16px; /* mencegah zoom otomatis di iOS */
		}

		/* kolom foto dan form jadi tumpuk */
		.row > .col-sm-4,
		.row > .col-sm-8 {
			width: 100%;
			padding: 0 8px;
		}
	}
</style>

<div class="row">
	<div class="col-sm-12">
		<div class="card card-premium">
			<div class="card-header bg-info text-white rounded-top">
				<h5 class="heading-title mb-0"><i class="fas fa-edit"></i> EDIT DATA BARANG</h5>
			</div>

			<div class="card-body">
				<form action="" method="post">
					<input type="hidden" name="redirect" value="<?= $redirect; ?>">

					<div class="row">

						<!-- FOTO BARANG -->
						<div class="col-sm-4 text-center">
						<?php
						$foto = !empty($row['gambar_brg']) && file_exists("dist/upload_img/".$row['gambar_brg'])
								? "dist/upload_img/".$row['gambar_brg']
								: "dist/upload_img/no-image.png"; // siapkan gambar default
						?>

						<img src="<?= $foto; ?>" class="img-preview mb-3">

							<a href="?page=editfoto&id=<?= $row['id_brg']; ?>&redirect=<?= urlencode($redirect); ?>" 
							   class="btn btn-secondary btn-modern mb-2 w-100">
							   <i class="fas fa-camera"></i> UBAH FOTO
							</a>

							<a href="?page=editfilefoto&id=<?= $row['id_brg']; ?>&redirect=<?= urlencode($redirect); ?>" 
							   class="btn btn-dark btn-modern w-100">
							   <i class="fas fa-folder-open"></i> UBAH FOTO DARI FILE
							</a>
						</div>

						<!-- FORM INPUT -->
						<div class="col-sm-8">
							<table class="table table-bordered table-premium">
								<tr>
									<th>ID BARANG</th>
									<td><input type="text" name="id_brg" class="form-control" value="<?= $row['id_brg']; ?>"></td>
								</tr>
								<tr>
									<th>NAMA</th>
									<td><input type="text" name="nama_brg" class="form-control" value="<?= $row['nama_brg']; ?>"></td>
								</tr>
								<tr>
									<th>SPESIFIKASI</th>
									<td><textarea name="spesifikasi_brg" class="form-control" rows="3"><?= $row['spesifikasi_brg']; ?></textarea></td>
								</tr>
								<tr>
									<th>MERK</th>
									<td><input type="text" name="merk_brg" class="form-control" value="<?= $row['merk_brg']; ?>"></td>
								</tr>
								<tr>
									<th>NO RAK</th>
									<td><input type="text" name="norak_brg" class="form-control" value="<?= $row['norak_brg']; ?>"></td>
								</tr>
								<tr>
									<th>TANGGAL MASUK</th>
									<td><input type="date" name="tgl_masuk_brg" class="form-control" value="<?= $row['tgl_masuk_brg']; ?>"></td>
								</tr>
								<tr>
									<th>KATEGORI</th>
									<td>
										<select class="form-control" name="kategori_brg">
											<?php 
											$norak = mysqli_query($koneksi, "SELECT * FROM tbl_kategori");
											while ($dt = mysqli_fetch_array($norak)) { 
												$select = ($row['id_kategori'] == $dt['id_kategori']) ? 'selected' : '';
											?>
												<option value="<?= $dt['id_kategori']; ?>" <?= $select; ?>>
													<?= $dt['nama_kategori']; ?>
												</option>
											<?php } ?>
										</select>
									</tr>
									<tr>
										<th>JUMLAH</th>
										<td><input type="number" name="jumlah_brg" class="form-control" value="<?= $row['jumlah_brg']; ?>"></td>
									</tr>
								</table>

							<!-- BUTTON -->
							<div class="mt-3">
								<button type="submit" class="btn btn-primary btn-modern" name="simpan"
									onclick="return confirm('Yakin ingin menyimpan perubahan?')">
									<i class="fas fa-save"></i> SIMPAN
								</button>

								<a href="<?= $redirect; ?>" class="btn btn-outline-primary btn-modern">
									<i class="fas fa-arrow-left"></i> KEMBALI
								</a>
							</div>
						</div>

						<!-- DELETE CARD -->
						<div class="col-sm-12 mt-4">
							<div class="card card-delete">
								<div class="card-header bg-danger text-white rounded-top">
									<h6 class="mb-0"><i class="fas fa-trash"></i> HAPUS DATA INI</h6>
								</div>
								<div class="card-body">
									<a href="admin/proses/proses_deletebarang.php?delete=<?= $row['id_brg']; ?>&redirect=<?= urlencode($redirect); ?>" 
									   class="btn btn-danger btn-modern"
									   onclick="return confirm('Data ini memiliki stok <?= $row['jumlah_brg']; ?> pcs. Klik OK untuk menghapus!')">
										<i class="fas fa-exclamation-triangle"></i> HAPUS DATA
									</a>
								</div>
							</div>
						</div>

					</div> <!-- row -->
				</form>
			</div>
		</div>
	</div>
</div>

<?php 
// PROSES UPDATE
if (isset($_POST['simpan'])) {
	$id_brg         = $_POST['id_brg'];
	$nama_brg       = $_POST['nama_brg'];
	$tgl_masuk_brg  = $_POST['tgl_masuk_brg'];
	$spesifik_brg   = $_POST['spesifikasi_brg'];
	$merk_brg       = $_POST['merk_brg'];
	$norak_brg      = $_POST['norak_brg'];
	$kategori_brg   = $_POST['kategori_brg'];
	$jumlah_brg     = $_POST['jumlah_brg'];
	$redirect       = $_POST['redirect'];

	$sql = mysqli_query($koneksi, "
		UPDATE tbl_barang 
		SET nama_brg        = '".$nama_brg."', 
			tgl_masuk_brg   = '".$tgl_masuk_brg."', 
			spesifikasi_brg = '".$spesifik_brg."', 
			merk_brg        = '".$merk_brg."', 
			norak_brg       = '".$norak_brg."', 
			id_kategori     = '".$kategori_brg."', 
			jumlah_brg      = '".$jumlah_brg."' 
		WHERE id_brg = '".$id_brg."'
	");

	if ($sql) {
		echo "<script>alert('Perubahan berhasil disimpan');document.location.href='".$redirect."';</script>";
	} else {
		echo "<script>alert('Perubahan gagal disimpan');document.location.href='".$redirect."';</script>";
	}
}
?>