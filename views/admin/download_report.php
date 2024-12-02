<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Models\Database;
use Models\Admin;
use Models\Spoc;
use Models\Faculty;
use Models\Student;

$conn = Database::getConnection();

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Add Admin sheet
$adminSheet = $spreadsheet->createSheet();
$adminSheet->setTitle('Admin');
$admins = Admin::getAll($conn);
$adminSheet->fromArray(['ID', 'Name', 'Email', 'Last Login'], NULL, 'A1');
$adminSheet->fromArray($admins, NULL, 'A2');

// Add SPOC sheet
$spocSheet = $spreadsheet->createSheet();
$spocSheet->setTitle('SPOC');
$spocs = Spoc::getAll($conn);
$spocSheet->fromArray(['ID', 'Name', 'Email', 'University', 'Usage'], NULL, 'A1');
$spocSheet->fromArray($spocs, NULL, 'A2');

// Add Faculty sheet
$facultySheet = $spreadsheet->createSheet();
$facultySheet->setTitle('Faculty');
$faculties = Faculty::getAll($conn);
$facultySheet->fromArray(['ID', 'Name', 'Email', 'University', 'Section', 'Usage'], NULL, 'A1');
$facultySheet->fromArray($faculties, NULL, 'A2');

// Add Student sheet
$studentSheet = $spreadsheet->createSheet();
$studentSheet->setTitle('Student');
$students = Student::getAll($conn);
$studentSheet->fromArray(['ID', 'Name', 'Email', 'University', 'Section', 'Usage'], NULL, 'A1');
$studentSheet->fromArray($students, NULL, 'A2');

// Set the first sheet as the active sheet
$spreadsheet->setActiveSheetIndex(0);

// Save the spreadsheet to a file
$writer = new Xlsx($spreadsheet);
$filename = 'user_report.xlsx';
$writer->save($filename);

// Send the file to the browser for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
readfile($filename);

// Delete the file after download
unlink($filename);
exit();