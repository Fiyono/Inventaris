<?php
// File: admin/ajax/test_import.php
// File ini untuk test apakah AJAX berfungsi

header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'Test connection berhasil'
]);
?>