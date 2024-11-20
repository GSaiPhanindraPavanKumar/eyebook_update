<?php
require_once 'functions.php';

// Dummy data for assessment results
$results = [
    ['student_name' => 'Sumanth', 'score' => 85],
    ['student_name' => 'Ravi Ram', 'score' => 90],
    ['student_name' => 'Sumanth', 'score' => 78]
];

// Generate CSV content
$csvContent = "Student Name,Score\n";
foreach ($results as $result) {
    $csvContent .= "{$result['student_name']},{$result['score']}\n";
}

// Set headers to download the file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="assessment_report.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Output CSV content
echo $csvContent;
exit;