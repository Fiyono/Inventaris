<link rel="stylesheet" href="assets/css/custom.css">
<style>
    /* Header tabel */
    #example1 thead th {
        background: linear-gradient(45deg, #007bff, #00c6ff);
        color: white;
        text-align: center;
        font-size: 13px;
        padding: 10px;
    }

    /* Isi tabel */
    #example1 tbody td {
        font-size: 12px;
        text-align: center;
        vertical-align: middle;
        transition: all 0.3s ease;
    }

    /* Hover baris */
    #example1 tbody tr:hover {
        background-color: #f1f9ff !important;
        transform: scale(1.01);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* Zebra stripes */
    #example1 tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    /* Efek animasi muncul */
    #example1 tbody tr {
        animation: fadeIn 0.4s ease-in;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(5px);}
        to {opacity: 1; transform: translateY(0);}
    }

    /* Badge aktivitas */
    .badge-ambil {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 11px;
    }
    .badge-kembali {
        background-color: #ffc107;
        color: black;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 11px;
    }
</style>
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-header bg-nav">
				<h6 class="card-title text-bold float-left">Daftar Tiket Masuk</h6>
			</div>
			<div class="card-body">

                <!-- Tambahkan CSS langsung di sini -->
                <style>
                    /* Styling header tabel */
                    #example1 th {
                        font-size: 13px;     /* ukuran font header */
                        font-weight: bold;   /* biar tebal */
                        text-align: center;  /* rata tengah */
                        background-color: #f8f9fa; /* opsional warna header */
                    }

                    /* Styling isi tabel */
                    #example1 td {
                        font-size: 12px;     /* ukuran font isi */
                        text-align: center;  /* rata tengah */
                        padding: 12px;       /* biar lebih lega */
                    }

                    /* Khusus kolom NO */
                    #example1 th:first-child,
                    #example1 td:first-child {
                        font-size: 13px;     /* lebih kecil */
                        width: 60px;         /* atur lebar NO */
                    }
					
                </style>
				<table id="example1" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th>NO</th>
							<th>NAMA PEMINJAM</th>
							<th>NAMA BARANG</th>
							<th>JUMLAH</th>
							<th>STATUS</th>
							<th>TUJUAN PENGGUNAAN</th>
							<th>TANGGAL PINJAM</th>
							<th>TANGGAL KEMBALI</th>
							<th>KETERANGAN</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$sql = mysqli_query($koneksi, "select * from tbl_tiketuser x left join tbl_barang  y on y.id_brg = x.id_brg left join tb_user z on z.id_user = x.id_user group by id_tiketuser desc");
						while ($row = mysqli_fetch_array($sql)) {
							?>
							<tr>
								<td><?= $row['id_tiketuser']; ?></td>
								<td><?= $row['nama_lengkap']; ?></td>
								<td><?= $row['nama_brg']; ?></td>
								<td><?= $row['jumlah']; ?></td>
								<td><?= $row['status']; ?></td>
								<td><?= $row['tujuan_gunabarang']; ?></td>
								<td><?= $row['tgl_pinjam']; ?></td>
								<td><?= $row['tgl_perkiraan_balik']; ?></td>
								<td>
								<?php 
								$btn = mysqli_fetch_array(mysqli_query($koneksi, "select * from tbl_tiketuser where status = '".$row['status']."'"));
								if ($btn['status'] == 'disetujui') {
									echo "Dalam proses peminjaman";
								}else if($btn['status'] == 'dibatalkan'){ ?>
									<a href="admin.php?page=tiket_masuk&id=<?= $row['id_tiketuser']; ?>" class="btn btn-danger" onclick="return confirm('Klik ok, untuk lanjut hapus!.')"><i class="fas fa-trash"></i></a>
								<?php }else if($btn['status'] == 'terkirim'){ ?>
									<a href="" class="btn btn-primary" data-toggle="modal" data-target="#modal-info<?= $row['id_tiketuser']; ?>"><i class="fas fa-angle-double-up"></i></a>
								<?php }
								?>
								</td>
							</tr>
							<div class="modal fade" id="modal-info<?= $row['id_tiketuser']; ?>">
							  <div class="modal-dialog">
							    <div class="modal-content bg-indigo">
							      <div class="modal-header">
							        <h6 class="modal-title"><i class="fas fa-angle-double-up"></i> AKSI ADMIN</h6>
							        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
							          <span aria-hidden="true">&times;</span></button>
							      </div>
							      <div class="modal-body">
							        <form action="" method="post">
							        	<div class="form-group" hidden>
							        		<input type="text" name="id_tiketuser" value="<?= $row['id_tiketuser']; ?>">
							        	</div>
							        	<div class="form-group">
							        		<label for="tindakan">TINDAKAN</label>
							        		<select class="select2 form-control form-control-sm" name="status">
							        			<option value="disetujui">SETUJU</option>
							        			<option value="dibatalkan">BATALKAN</option>
							        		</select>
							        	</div>
							        	<div class="form-group">
							        		<button type="submit" name="ok" class="btn btn-primary">OK</button>
							        	</div>
							        </form>
							      </div>
							    </div>
							    <!-- /.modal-content -->
							  </div>
							  <!-- /.modal-dialog -->
							</div>
							<!-- /.modal -->
						<?php }
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php 
if (isset($_POST['ok'])) {
	$id_tiketuser = $_POST['id_tiketuser'];
	$status = $_POST['status'];

	$tiket = mysqli_fetch_array(mysqli_query($koneksi, "select * from tbl_tiketuser where id_tiketuser = '".$id_tiketuser."'"));
	if ($status == 'disetujui') {
		$up = mysqli_query($koneksi, "update tbl_tiketuser set status = '".$status."' where id_tiketuser = '".$id_tiketuser."'");
		$brg = mysqli_fetch_array(mysqli_query($koneksi, "select * from tbl_barang where id_brg = '".$tiket['id_brg']."'"));
		$userpin = mysqli_fetch_array(mysqli_query($koneksi, "select * from tb_user where id_user = '".$tiket['id_user']."'"));
		if($userpin['id_organisasi'] == '1'){
			$organ = 'Guru';
		}else if($userpin['id_organisasi'] == '2'){
			$organ = 'Siswa';
		}
		//intunik didapatkan dari kode urut tbl_pinjaman dari file koneksi.php
		$insert = mysqli_query($koneksi, "insert into tbl_pinjaman(id_pinjaman,	id_brg,	id_user, tgl_pinjam, jumlah_pinjam,	organisasi,	tujuan_gunabarang,status) values('".$intunik."','".$brg['id_brg']."','".$tiket['id_user']."','".$tiket['tgl_pinjam']."','".$tiket['jumlah']."','".$organ."','".$tiket['tujuan_gunabarang']."','dipinjam')");
		$jenis_activ = "Pinjam";
		$waktu_sekarang = date('h:i:s');
		$hist = mysqli_query($koneksi, "insert into tbl_history(id_history, jenis_aktivitas, id_brg, nama_brg, jumlah_brg, tgl_history, waktu_history) values('','".$jenis_activ."','".$brg['id_brg']."', '".$brg['nama_brg']."','".$tiket['jumlah']."','".$tiket['tgl_pinjam']."','".$waktu_sekarang."');");
		$hist_pinjam = mysqli_query($koneksi, "insert into tbl_history_pinjam(id_histpinjam, id_pinjaman, id_brg, id_user, jumlahbrg_pinjam, jumlahbrg_kembali, tujuan_gunabarang, tgl_pinjam, tgl_perkiraan_balik, tgl_kembali) values('','".$intunik."','".$brg['id_brg']."','".$tiket['id_user']."','".$tiket['jumlah']."','','".$tiket['tujuan_gunabarang']."','".$tiket['tgl_pinjam']."', '".$tiket['tgl_perkiraan_balik']."','')");

		if ($up && $insert && $hist) {
			echo "<script>
			alert('TIKET BERHASIL DISETUJUI');
			document.location.href = 'admin.php?page=tiket_masuk';
			</script>";
		}else{
			echo "<script>
			alert('TIKET GAGAL DISETUJUI');
			document.location.href = 'admin.php?page=tiket_masuk';
			</script>";
		}

	}else if($status == 'dibatalkan'){
		$up = mysqli_query($koneksi, "update tbl_tiketuser set status = '".$status."' where id_tiketuser = '".$id_tiketuser."'");
		if ($up) {
			echo "<script>
			alert('TIKET BERHASIL DIBATALKAN');
			document.location.href = 'admin.php?page=tiket_masuk';
			</script>";
		}else{
			echo "<script>
			alert('TIKET GAGAL DIBATALKAN');
			document.location.href = 'admin.php?page=tiket_masuk';
			</script>";
		}
	}
}else if(isset($_GET['id'])){
	$id = $_GET['id'];

	$sql = mysqli_query($koneksi, "delete from tbl_tiketuser where id_tiketuser = '".$id."'");

	if ($sql) {
		echo "<script>
		alert('DATA TIKET dibatalkan BERHASIL DIHAPUS');
		document.location.href = 'admin.php?page=tiket_masuk';
		</script>";
	}else{
		echo "<script>
		alert('DATA TIKET dibatalkan GAGAL DIHAPUS');
		document.location.href = 'admin.php?page=tiket_masuk';
		</script>";
	}

}
?>