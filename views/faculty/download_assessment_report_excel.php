<?php
require_once 'functions.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$assessmentId = $_GET['id'] ?? null;

if (!$assessmentId) {
    die('Assessment ID is required.');
}

// Fetch assessment results
$results = getAssessmentResults($assessmentId);

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the header row
$sheet->setCellValue('A1', 'Student Name');
$sheet->setCellValue('B1', 'Score');

// Populate the data rows
$row = 2;
foreach ($results as $result) {
    $sheet->setCellValue('A' . $row, $result['student_name']);
    $sheet->setCellValue('B' . $row, $result['score']);
    $row++;
}

// Set headers to download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="assessment_report.xlsx"');
header('Cache-Control: max-age=0');

// Write the file to the output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;