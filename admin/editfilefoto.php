<?php 
if (isset($_GET['page']) == 'editfile' && isset($_GET['id'])) { 
	$row = mysqli_fetch_array(mysqli_query($koneksi, 
        "SELECT * FROM tbl_barang WHERE id_brg = '".$_GET['id']."'"));
?>
	
<style>
	.preview-img {
		width: 100%;
		max-width: 240px;
		height: auto;
		border-radius: 15px;
		box-shadow: 0 4px 20px rgba(0,0,0,0.15);
		transition: 0.3s;
	}
	.preview-img:hover {
		transform: scale(1.03);
		box-shadow: 0 8px 28px rgba(0,0,0,0.25);
	}

	.custom-card {
		border-radius: 18px;
		overflow: hidden;
		box-shadow: 0 8px 20px rgba(0,0,0,0.12);
	}

	.custom-header {
		background: linear-gradient(135deg, #17a2b8, #138496);
		color: white;
		padding: 18px 20px;
	}
</style>

<div class="row d-flex justify-content-center mt-4">
	<div class="col-md-6">
		<div class="card custom-card">
			
			<div class="custom-header">
				<h5 class="mb-0"><i class="fas fa-image"></i> Edit Foto Barang</h5>
			</div>

			<div class="card-body">

				<form action="admin/proses/proses_editfile_foto.php" method="post" enctype="multipart/form-data">

					<div class="mb-4 text-center">
						<img src="dist/upload_img/<?= $row['gambar_brg']; ?>" 
							 id="output" 
							 class="preview-img mb-3">

						<div class="input-group">
							<label class="input-group-text"><i class="fas fa-upload"></i></label>
							<input type="file" accept="image/*" class="form-control" onchange="loadFile(event)" name="gambar" id="gambar" required>
						</div>

						<input type="hidden" name="id_brg" value="<?= $row['id_brg']; ?>">
					</div>

					<div class="d-flex justify-content-between">
						<a href="admin.php?page=editmaster_data&id=<?= $_GET['id']; ?>" class="btn btn-outline-secondary">
							<i class="fas fa-arrow-left"></i> Kembali
						</a>

						<div>
							<a href="" class="btn btn-outline-danger me-2">
								<i class="fas fa-times"></i> Cancel
							</a>

							<button type="submit" class="btn btn-primary" name="simpanfile">
								<i class="fas fa-save"></i> Simpan
							</button>
						</div>
					</div>

				</form>

			</div>

		</div>
	</div>
</div>

<?php } ?>

<script>
var loadFile = function(event){
	var output = document.getElementById('output');
	output.src = URL.createObjectURL(event.target.files[0]);
}
</script>
