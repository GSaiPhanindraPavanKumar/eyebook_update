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

    // Fetch the course ID associated with the virtual classroom
    $stmt = $conn->prepare("SELECT course_id FROM virtual_classrooms WHERE id = ?");
    $stmt->execute([$classroomId]);
    $courseIdJson = $stmt->fetchColumn();
    $courseIds = json_decode($courseIdJson, true);

    if (!empty($courseIds)) {
        // Use the first course ID for redirection
        $courseId = $courseIds[0];
        $hashedCourseId = base64_encode($courseId);
        $hashedCourseId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedCourseId);
        header("Location: /faculty/view_course/$hashedCourseId");
        exit();
    } else {
        echo "Error: No course ID found.";
    }
} else {
    echo "Error: Classroom ID or attendance data not provided.";
}
?>