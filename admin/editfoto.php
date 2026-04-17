<?php 
if (isset($_GET['page']) && $_GET['page'] == 'editfoto' && isset($_GET['id'])) { 
    // Ambil ID untuk digunakan di tautan kembali
    $itemId = htmlspecialchars($_GET['id']);
?>
<div class="row justify-content-center mt-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-gradient-info text-white">
                <h4 class="card-title mb-0"><i class="fas fa-camera-retro"></i> Edit Foto via WebCam</h4>
            </div>
            
            <div class="card-body p-4">
                <form action="admin/proses/proses_editfoto.php" method="post">
                    <div class="row">
                        
                        <div class="col-md-6 mb-3">
                            <h5 class="text-info mb-3">Tampilan WebCam</h5>
                            <div id="my_camera" class="border border-secondary rounded d-flex justify-content-center align-items-center mb-3" style="width: 250px; height: 250px; overflow: hidden;">
                                </div>
                            
                            <div class="d-grid gap-2 d-md-block">
                                <button type="button" class="btn btn-warning me-2 mb-2" onclick="take_snapshot()">
                                    <i class="fas fa-camera"></i> Ambil Foto
                                </button>
                                
                                <button type="submit" name="simpanfoto" class="btn btn-success me-2 mb-2">
                                    <i class="fas fa-save"></i> Simpan Foto
                                </button>
                                
                                <a href="admin.php?page=editmaster_data&id=<?= $itemId; ?>" class="btn btn-secondary mb-2">
                                    <i class="fas fa-step-backward"></i> Kembali
                                </a>
                            </div>
                            
                            <input type="hidden" name="image" class="image-tag">
                            <input type="hidden" name="id_brg" value="<?= $itemId; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <h5 class="text-primary mb-3">Hasil Foto</h5>
                            <div id="results" class="border border-primary rounded p-2 d-flex justify-content-center align-items-center" style="min-height: 250px;">
                                <p class="text-muted m-0">Foto yang diambil akan muncul di sini...</p>
                            </div>
                            <small class="form-text text-muted mt-2">Pastikan gambar sudah muncul sebelum menekan tombol Simpan.</small>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  
<?php } ?>

<script language="JavaScript">

    Webcam.set({
        width: 250,
        height: 250,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    // Menempelkan WebCam ke elemen #my_camera
    Webcam.attach( '#my_camera' );
 
    function take_snapshot() {
        Webcam.snap( function(data_uri) {
            $(".image-tag").val(data_uri);
            // Menghapus teks placeholder dan menampilkan gambar
            document.getElementById('results').innerHTML = '<img src="'+data_uri+'" class="img-fluid rounded shadow-sm" alt="Foto Hasil WebCam"/>';
        } );
    }

</script>