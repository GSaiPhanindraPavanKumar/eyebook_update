<?php
require_once __DIR__ . '/../../models/Database.php';
use Models\Database;

$conn = Database::getConnection();

$classroomId = $_POST['classroom_id'] ?? null;
$attendance = $_POST['attendance'] ?? [];

if ($classroomId && !empty($attendance)) {
    // Save attendance to the database
    $stmt = $conn->prepare("UPDATE virtual_classrooms SET attendance = ? WHERE id = ?");
    $stmt->execute([json_encode($attendance), $classroomId]);

    // Redirect back to the dashboard
    header('Location: /faculty/virtual_classroom');
    exit();
} else {
    echo "Error: Classroom ID or attendance data not provided.";
}
?>