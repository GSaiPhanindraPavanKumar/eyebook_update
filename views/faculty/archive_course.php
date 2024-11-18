<?php
use Models\Database;

require_once 'vendor/autoload.php';

$conn = Database::getConnection();

if (isset($_POST['archive_course_id'])) {
    $courseId = $_POST['archive_course_id'];
    $stmt = $conn->prepare("UPDATE courses SET status = 'archived' WHERE id = :course_id");
    $stmt->execute(['course_id' => $courseId]);
}

// Redirect back to the my_courses.php page
header('Location: my_courses');
exit();
?>