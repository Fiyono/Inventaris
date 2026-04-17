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
        font-weight: bold;
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
		
	</div>
	<div class="col-sm-12">
		<div class="card">
			<div class="card-header bg-nav">
				<h6>Daftar Organisasi</h6>
			</div>
			<div class="card-body">
				<table id="example1" class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th>NO</th>
							<th>ID ORGANISASI</th>
							<th>NAMA ORGANISASI</th>
							<th>AKSI</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$no=1;
						$sql = mysqli_query($koneksi, "select * from tbl_organisasi");
						while ($row = mysqli_fetch_array($sql)) { ?>
							<tr>
								<td><?= $no++; ?></td>
								<td><?= $row['id_organisasi']; ?></td>
								<td><?= $row['nama_organisasi']; ?></td>
								<td><a href="" class="btn btn-primary">Edit</a></td>
							</tr>
						<?php }
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>