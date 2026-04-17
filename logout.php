<?php
session_start();

include "koneksi.php";

/* ================= HAPUS USER AGENT ================= */
if(isset($_SESSION['agent'])){

    $agent = mysqli_real_escape_string($koneksi, $_SESSION['agent']);

    mysqli_query($koneksi,"
        DELETE FROM user_agent
        WHERE name_user_agent='$agent'
    ");
}

/* ================= HAPUS SESSION ================= */
$_SESSION = [];
session_unset();
session_destroy();

/* ================= REDIRECT ================= */
header("Location: index.php");
exit;
?>