<?php
require_once '../../config/connection.php';
require_once 'config.php';


// Establish database connection
$conn = new PDO('mysql:host=' . $servername . ';dbname=' . $dbname, $username, $password);

// Include Zoom integration
require_once 'zoom_integration.php';

// Initialize ZoomAPI
$zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

$classroomId = $_GET['classroom_id'] ?? null;

if ($classroomId) {
    $attendance = $zoom->getAttendance($classroomId);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=attendance.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Student ID', 'Name', 'Email', 'Join Time', 'Leave Time'));

    foreach ($attendance as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
} else {
    echo "Error: Classroom ID not provided.";
}
?>