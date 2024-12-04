<?php
require_once __DIR__ . '/../../models/Database.php';
use Models\Database;

$conn = Database::getConnection();

$classroomId = $_GET['classroom_id'] ?? null;

if ($classroomId) {
    $stmt = $conn->prepare("SELECT attendance FROM virtual_classrooms WHERE id = ?");
    $stmt->execute([$classroomId]);
    $attendance = $stmt->fetchColumn();

    if ($attendance) {
        $attendance = json_decode($attendance, true);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=attendance.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Student ID', 'Name', 'Registration Number', 'Attendance'));

        foreach ($attendance as $studentId => $status) {
            $stmt = $conn->prepare("SELECT name, regd_no FROM students WHERE id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                fputcsv($output, array($studentId, $student['name'], $student['regd_no'], $status));
            }
        }

        fclose($output);
    } else {
        echo "Error: No attendance data found.";
    }
} else {
    echo "Error: Classroom ID not provided.";
}
?>