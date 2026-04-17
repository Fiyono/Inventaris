<?php
$id_user = $_SESSION['id_user'];

$no = 1;
$sql = mysqli_query($koneksi, "SELECT x.*, y.nama_brg 
    FROM tbl_pinjaman x 
    INNER JOIN tbl_barang y ON y.id_brg = x.id_brg 
    WHERE x.id_user = '$id_user' AND x.status != 'dikembalikan'");
?>
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
			<h1 class="card-title text-bold float-left">DAFTAR BARANG DIPINJAM</h1>
		</div>
		<div class="card-body">
		<table id="example1" class="table table-sm table-bordered table-striped table-hover">
			<thead>
				<tr>
					<th>NO</th>
					<th>NAMA BARANG</th>
					<th>JUMLAH</th>
					<th>TANGGAL PINJAM</th>
					<th>STATUS</th>
					<th>TANGGAL PERKIRAAN KEMBALI</th>
					<th>KETERANGAN</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = mysqli_fetch_array($sql)) { 
					$riwpin = mysqli_fetch_array(mysqli_query($koneksi, 
						"SELECT * FROM tbl_history_pinjam WHERE id_pinjaman = '".$row['id_pinjaman']."'"));
				?>
					<tr>
						<td><?= $no++; ?></td>
						<td><?= $row['nama_brg']; ?></td>
						<td><?= $row['jumlah_pinjam']; ?></td>
						<td><?= $row['tgl_pinjam']; ?></td>
						<td><?= $row['status']; ?></td>
						<td><?= $riwpin['tgl_perkiraan_balik']; ?></td>
						<td><?= $row['tujuan_gunabarang']; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		</div>
	</div>
	</div>
</div>
