<?php
url: "/proses_catatan.php",
include "koneksi.php"; // pastikan path ke koneksi benar


if(isset($_POST['action'])){
    $action = $_POST['action'];

    if($action=="add"){
        $isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
        $date = date("Y-m-d H:i:s");
        $simpan = mysqli_query($koneksi, "INSERT INTO catatan (isi, tgl_waktu) VALUES('$isi','$date')");

        if($simpan){
            $id = mysqli_insert_id($koneksi);
            echo '
            <div class="col-md-4 mb-3" id="catatan-'.$id.'">
              <div class="card shadow-sm catatan-card h-100" data-toggle="modal" data-target="#modal'.$id.'">
                <div class="card-body">
                  <h6 class="card-subtitle mb-2 text-muted">
                    <i class="far fa-clock"></i> '.date("d M Y H:i").'
                  </h6>
                  <p class="card-text">'.nl2br(htmlspecialchars($isi)).'</p>
                </div>
              </div>
            </div>';
        } else {
            echo "error";
        }
    }

    if($action=="delete"){
        $id = $_POST['id'];
        mysqli_query($koneksi, "DELETE FROM catatan WHERE id_catatan='$id'");
        echo "ok";
    }

    if($action=="upload"){
        $isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
        mysqli_query($koneksi, "UPDATE notifikasi_catatan SET isi_chat='$isi' WHERE id_notifcat='1'");
        echo "ok";
    }
}
?>
