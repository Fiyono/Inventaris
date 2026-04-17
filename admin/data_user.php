<?php include "koneksi.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/custom.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* Style yang sama untuk desktop */
#example1 thead th{
    background:linear-gradient(45deg,#007bff,#00c6ff);
    color:white;
    text-align:center;
    font-size: 15px;
    padding: 10px;
}
#example1 tbody td{
    font-size:13px;
    text-align:center;
    vertical-align:middle;
}
#example1 tbody tr:hover{
    background:#f1f9ff!important;
    transform: scale(1.01);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
#example1 tbody tr:nth-child(even){
    background:#fafafa;
}

/* Tombol aksi */
.btn-edit, .btn-delete {
    margin: 2px;
}

html, body {
    height: auto !important;
    min-height: auto !important;
    overflow-x: hidden;
}

.content-wrapper {
    min-height: auto !important;
}

.card {
    margin-bottom: 0 !important;
    border-radius: 12px;
    overflow: hidden;
}

/* ========== RESPONSIVE MOBILE - TAMPILAN KARTU ========== */
@media screen and (max-width: 768px) {
    
    /* Sembunyikan header tabel di mobile */
    #example1 thead {
        display: none;
    }
    
    /* Setiap baris menjadi kartu */
    #example1 tbody tr {
        display: block !important;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin-bottom: 15px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 10px;
    }
    
    /* Setiap sel menjadi baris horizontal */
    #example1 tbody td {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        text-align: left !important;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        width: 100% !important;
    }
    
    /* Hapus border-bottom untuk sel terakhir */
    #example1 tbody td:last-child {
        border-bottom: none;
    }
    
    /* Label untuk setiap kolom */
    #example1 tbody td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #007bff;
        width: 35%;
        font-size: 12px;
    }
    
    /* Tombol aksi dalam satu baris */
    #example1 tbody td:last-child {
        display: flex !important;
        justify-content: center;
        gap: 8px;
    }
    
    /* Judul card lebih kecil */
    .card-header {
        font-size: 18px !important;
        padding: 12px;
    }
    
    /* Button group full width */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .btn-group .btn {
        flex: 1;
        margin-left: 0 !important;
    }
    
    /* Search box */
    .dataTables_filter {
        margin-bottom: 15px;
    }
    
    .dataTables_filter label {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .dataTables_filter input {
        width: 100% !important;
        margin-top: 5px;
    }
    
    /* Pagination */
    .dataTables_paginate .paginate_button {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }
    
    .dataTables_info {
        font-size: 11px;
    }
}

/* Tablet */
@media screen and (min-width: 769px) and (max-width: 1024px) {
    #example1 thead th {
        font-size: 13px;
        padding: 8px 5px;
    }
    
    #example1 tbody td {
        font-size: 12px;
        padding: 8px 5px;
    }
}
</style>

<div class="row">
  <div class="col-sm-12">
    <div class="card shadow-sm">

      <!-- HEADER -->
      <div class="card-header bg-primary text-white text-center"
           style="font-size:22px;font-weight:bold;">
        <i class="fas fa-users me-2"></i> DAFTAR USER
      </div>

      <!-- BODY -->
      <div class="card-body">

        <div class="btn-group mb-3">
          <button class="btn btn-success btn-sm" onclick="openTambahModal()">
            <i class="fas fa-user-plus"></i> TAMBAH USER
          </button>
          <button class="btn btn-info btn-sm" onclick="openImportModal()">
            <i class="fas fa-file-import"></i> IMPORT EXCEL
          </button>
          <a href="assets/download_template_excel.php" class="btn btn-warning btn-sm" download>
            <i class="fas fa-download"></i> DOWNLOAD FORMAT
          </a>
        </div>

        <div class="table-responsive">
          <table id="example1" class="table table-sm table-bordered table-hover mb-0">
            <thead>
              <tr>
                <th>NO</th>
                <th>NAMA</th>
                <th>USERNAME</th>
                <th>PASSWORD</th>
                <th>EMAIL</th>
                <th>NO HP</th>
                <th>AKSI</th>
              </tr>
            </thead>

            <tbody>
              <?php
              $no=1;
              $sql=mysqli_query($koneksi,"
              SELECT * FROM tb_user x
              INNER JOIN leveluser y ON y.id_level=x.id_level
              INNER JOIN tbl_organisasi z ON z.id_organisasi=x.id_organisasi
              ORDER BY x.id_user DESC
              ");

              while($row=mysqli_fetch_array($sql)){ ?>
              <tr>
                <td data-label="NAMA"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                <td data-label="USERNAME"><?= htmlspecialchars($row['user']); ?></td>
                <td data-label="PASSWORD">••••••••</td>
                <td data-label="EMAIL"><?= htmlspecialchars($row['email']); ?></td>
                <td data-label="NO HP"><?= htmlspecialchars($row['no_whatsapp']); ?></td>
                <td data-label="AKSI">
                  <button class="btn btn-sm btn-primary btn-edit"
                          onclick="openEditModal(<?= $row['id_user']; ?>)">
                    <i class="fas fa-user-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger btn-delete"
                          data-id="<?= $row['id_user']; ?>"
                          data-nama="<?= htmlspecialchars($row['nama_lengkap']); ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>


<!-- ================= MODAL IMPORT EXCEL ================= -->
<div class="modal fade" id="modalImport">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="fas fa-file-import"></i> Import User dari Excel
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <form id="formImport" enctype="multipart/form-data">
        <div class="modal-body">
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Format file yang didukung:</strong><br>
            • Excel (.xls, .xlsx)<br>
            • CSV (.csv)<br><br>
            
            <strong>Struktur kolom:</strong><br>
            NAMA | USERNAME | PASSWORD | EMAIL | NO HP | LEVEL | ORGANISASI<br><br>
            
            <small class="text-warning">
              <i class="fas fa-exclamation-triangle"></i>
              Password akan dienkripsi otomatis dengan MD5
            </small>
          </div>
          
          <div class="form-group">
            <label>Pilih File Excel/CSV</label>
            <input type="file" name="file_excel" id="file_excel" class="form-control" 
                   accept=".xlsx,.xls,.csv" required>
            <small class="text-muted">Maksimal ukuran file: 5MB</small>
          </div>
          
          <div class="progress" style="display: none;" id="progressImport">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                 style="width: 100%">Memproses file...</div>
          </div>
          
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-info" id="btnImport">
            <i class="fas fa-upload"></i> IMPORT
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ================= MODAL USER ================= -->
<div class="modal fade" id="modalUser">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-success text-white" id="modalHeader">
        <h5 class="modal-title" id="modalTitle">
          <i class="fas fa-user-plus"></i> Tambah User Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <form id="formUser">
        <input type="hidden" name="id_user" id="id_user">
        <div class="modal-body">
          <div class="row">

            <div class="col-md-6">
              <label>Nama Lengkap</label>
              <input type="text" name="nama" id="nama" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label>Username</label>
              <input type="text" name="user" id="user" class="form-control" required>
            </div>

            <div class="col-md-6 mt-3">
              <label>Email</label>
              <input type="email" name="email" id="email" class="form-control">
            </div>

            <div class="col-md-6 mt-3">
              <label>No HP</label>
              <input type="text" name="hp" id="hp" class="form-control">
            </div>

            <div class="col-md-6 mt-3">
              <label>Password <small class="text-muted" id="passwordNote">(Wajib diisi)</small></label>
              <input type="password" name="pass" id="pass" class="form-control">
            </div>

            <div class="col-md-6 mt-3">
              <label>Organisasi</label>
              <select name="id_organisasi" id="id_organisasi" class="form-control" required>
                <option value="">-- Pilih Organisasi --</option>
                <?php
                $org = mysqli_query($koneksi, "SELECT * FROM tbl_organisasi ORDER BY nama_organisasi");
                while ($o = mysqli_fetch_array($org)) {
                    echo "<option value='".$o['id_organisasi']."'>".htmlspecialchars($o['nama_organisasi'])."</option>";
                }
                ?>
              </select>
            </div>

            <div class="col-md-6 mt-3">
              <label>Level User</label>
              <select name="id_level" id="id_level" class="form-control" required>
                <option value="">-- Pilih Level --</option>
                <?php
                $lvl = mysqli_query($koneksi, "SELECT * FROM leveluser ORDER BY name_level");
                while ($l = mysqli_fetch_array($lvl)) {
                    echo "<option value='".$l['id_level']."'>".htmlspecialchars($l['name_level'])."</option>";
                }
                ?>
              </select>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="btnSubmit">
            <i class="fas fa-save"></i> Simpan
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- JQUERY (sudah ada di index.php, tapi untuk berjaga-jaga) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){
    
    /* ================= HAPUS USER ================= */
    $(document).on("click",".btn-delete",function(){

        let id   = $(this).data("id");
        let nama = $(this).data("nama");

        Swal.fire({
            title: "Hapus User?",
            text: "Akun "+nama+" akan dihapus permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then((result)=>{

            if(result.isConfirmed){
                $.ajax({
                    url: "admin/ajax/delete_user.php",
                    type: "POST",
                    data: {id: id},
                    success: function(){
                        Swal.fire({
                            icon:'success',
                            title:'Berhasil',
                            text:'User berhasil dihapus',
                            timer:1500,
                            showConfirmButton:false
                        });
                        setTimeout(()=>{ location.reload(); },1500);
                    },
                    error: function(xhr){
                        Swal.fire("Error", "Gagal menghapus user", "error");
                    }
                });
            }

        });

    });

    /* ================= PROSES FORM (Tambah/Edit) ================= */
    $("#formUser").on("submit", function(e){
        e.preventDefault();
        
        let id_user = $("#id_user").val();
        let url = id_user ? "admin/ajax/edit_user.php" : "admin/ajax/tambah_user.php";
        let formData = $(this).serialize();
        
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    title: 'Mohon tunggu',
                    text: 'Sedang memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(res){
                Swal.close();
                if(res.status === "success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    $("#modalUser").modal("hide");
                    $("#formUser")[0].reset();
                    
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            },
            error: function(xhr, status, error){
                Swal.close();
                console.log("ERROR:", xhr.responseText);
                Swal.fire("Error", "Gagal terhubung ke server", "error");
            }
        });
    });

});

/* ================= FUNGSI OPEN MODAL TAMBAH ================= */
function openTambahModal() {
    $("#formUser")[0].reset();
    $("#id_user").val("");
    
    $("#modalHeader").removeClass("bg-warning").addClass("bg-success");
    $("#modalTitle").html('<i class="fas fa-user-plus"></i> Tambah User Baru');
    $("#btnSubmit").html('<i class="fas fa-save"></i> Simpan');
    $("#passwordNote").text("(Wajib diisi)");
    $("#pass").prop("required", true);
    
    $("#modalUser").modal("show");
}

/* ================= FUNGSI OPEN MODAL EDIT ================= */
function openEditModal(id) {
    console.log("EDIT ID:", id);
    
    $("#formUser")[0].reset();
    
    $("#modalHeader").removeClass("bg-success").addClass("bg-warning");
    $("#modalTitle").html('<i class="fas fa-user-edit"></i> Edit User');
    $("#btnSubmit").html('<i class="fas fa-save"></i> Update');
    $("#passwordNote").text("(Kosongkan jika tidak diubah)");
    $("#pass").prop("required", false);
    
    Swal.fire({
        title: 'Mohon tunggu',
        text: 'Mengambil data user...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: "admin/ajax/get_user.php",
        type: "POST",
        data: {id: id},
        dataType: "json",
        timeout: 10000,
        success: function(res){
            Swal.close();
            
            if(res.status === "success" && res.data){
                console.log("Data diterima:", res.data);
                
                $("#id_user").val(res.data.id_user);
                $("#nama").val(res.data.nama_lengkap);
                $("#user").val(res.data.user);
                $("#email").val(res.data.email || '');
                $("#hp").val(res.data.no_whatsapp || '');
                
                if(res.data.id_level) {
                    $("#id_level").val(res.data.id_level);
                }
                
                if(res.data.id_organisasi) {
                    $("#id_organisasi").val(res.data.id_organisasi);
                }
                
                setTimeout(() => {
                    $("#modalUser").modal("show");
                }, 100);
                
            } else {
                Swal.fire("Error", res.message || "Data tidak ditemukan", "error");
            }
        },
        error: function(xhr, status, error){
            Swal.close();
            console.log("AJAX Error Status:", status);
            console.log("Response Text:", xhr.responseText);
            
            let errorMsg = "Gagal terhubung ke server";
            if(status === "timeout") {
                errorMsg = "Koneksi timeout, coba lagi";
            } else if(xhr.status === 404) {
                errorMsg = "File get_user.php tidak ditemukan";
            } else if(xhr.status === 500) {
                errorMsg = "Error pada server, cek file get_user.php";
            }
            
            Swal.fire("Error", errorMsg, "error");
        }
    });
}

/* ================= FUNGSI OPEN MODAL IMPORT ================= */
function openImportModal() {
    $("#formImport")[0].reset();
    $("#progressImport").hide();
    $("#modalImport").modal("show");
}

/* ================= PROSES IMPORT EXCEL ================= */
$("#formImport").on("submit", function(e){
    e.preventDefault();
    
    let fileInput = $("#file_excel")[0];
    let file = fileInput.files[0];
    
    if(!file) {
        Swal.fire("Error", "Pilih file terlebih dahulu", "warning");
        return;
    }
    
    let ext = file.name.split('.').pop().toLowerCase();
    let allowed = ['xls', 'xlsx', 'csv'];
    
    if(!allowed.includes(ext)) {
        Swal.fire("Error", "File harus .xls, .xlsx, atau .csv", "error");
        return;
    }
    
    let formData = new FormData();
    formData.append("file_excel", file);
    
    Swal.fire({
        title: 'Importing...',
        text: 'Mohon tunggu, sedang memproses file',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: "admin/ajax/import_excel.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        timeout: 120000,
        success: function(res){
            Swal.close();
            
            if(res.status === "success") {
                let msg = `
                    <div style="text-align: left">
                        <p>✅ Berhasil: <strong>${res.success}</strong> user</p>
                        <p>❌ Gagal: <strong>${res.error}</strong> user</p>
                        <p>📊 Total: <strong>${res.total}</strong> data</p>
                    </div>
                `;
                
                if(res.errors && res.errors.length > 0) {
                    msg += `<br><b>Detail Error:</b><br>`;
                    msg += `<div style="max-height: 200px; overflow-y: auto; text-align: left; font-size: 12px;">`;
                    res.errors.forEach(err => {
                        msg += `• ${err}<br>`;
                    });
                    msg += `</div>`;
                }
                
                Swal.fire({
                    icon: res.error > 0 ? 'warning' : 'success',
                    title: 'Import Selesai',
                    html: msg,
                    width: '600px',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if(res.success > 0) {
                        $("#modalImport").modal("hide");
                        location.reload();
                    }
                });
            } else {
                Swal.fire("Error", res.message, "error");
            }
        },
        error: function(xhr, status, error){
            Swal.close();
            console.log("Error detail:", xhr.responseText);
            
            let errorMsg = "Gagal mengimport file";
            if(status === "timeout") {
                errorMsg = "File terlalu besar, coba gunakan file yang lebih kecil";
            }
            
            Swal.fire("Error", errorMsg, "error");
        }
    });
});
</script>