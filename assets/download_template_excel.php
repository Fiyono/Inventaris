<?php
// File: assets/download_template_excel.php

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Buat spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = ['NAMA', 'USERNAME', 'PASSWORD', 'EMAIL', 'NO HP', 'LEVEL', 'ORGANISASI'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Style header
$sheet->getStyle('A1:G1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '007BFF']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

// Lebar kolom
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(20);

// Contoh data dengan validasi ENUM
$sample_data = [
    ['Budi Santoso', 'budi', 'budi123', 'budi@mail.com', '081234567890', 'user', 'guru'],
    ['Siti Aminah', 'siti', 'siti456', 'siti@mail.com', '081298765432', 'admin', 'murid'],
    ['Ahmad Rizki', 'ahmad', 'ahmad123', 'ahmad@mail.com', '085678901234', 'user', 'guru'],
];

$row = 2;
foreach ($sample_data as $data) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Style data
$sheet->getStyle('A2:G' . ($row-1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

// Catatan penting
$notes_row = $row + 2;
$sheet->setCellValue('A' . $notes_row, '📌 PETUNJUK PENTING:');
$sheet->getStyle('A' . $notes_row)->getFont()->setBold(true)->setSize(12);

$sheet->setCellValue('A' . ($notes_row + 1), '1. LEVEL harus diisi dengan: "admin" atau "user" (tanpa tanda petik)');
$sheet->setCellValue('A' . ($notes_row + 2), '2. ORGANISASI harus diisi dengan: "guru" atau "murid" (tanpa tanda petik)');
$sheet->setCellValue('A' . ($notes_row + 3), '3. PASSWORD akan dienkripsi otomatis dengan MD5');
$sheet->setCellValue('A' . ($notes_row + 4), '4. EMAIL dan NO HP boleh dikosongkan');
$sheet->setCellValue('A' . ($notes_row + 5), '5. Hapus baris contoh sebelum import');
$sheet->setCellValue('A' . ($notes_row + 6), '6. Jangan mengubah urutan kolom');

// Beri warna kuning untuk cell yang perlu perhatian
$sheet->getStyle('F2:F' . ($row-1))->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFFFE0'); // Kuning muda untuk kolom LEVEL

$sheet->getStyle('G2:G' . ($row-1))->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('E0FFFF'); // Biru muda untuk kolom ORGANISASI

// Download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="format_import_user.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>