<?php
use Models\Database;
use Models\Course;

$conn = Database::getConnection();

$courseId = $_POST['course_id'];
$indexPath = $_POST['indexPath'];

// Fetch the course
$course = Course::getById($conn, $courseId);
if (!$course) {
    die('Course not found');
}

// Update the completed_books field
$completedBooks = json_decode($course['completed_books'], true) ?? [];
if (!in_array($indexPath, $completedBooks)) {
    $completedBooks[] = $indexPath;
}

$completedBooksJson = json_encode($completedBooks);
$sql = "UPDATE courses SET completed_books = :completed_books WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['completed_books' => $completedBooksJson, 'id' => $courseId]);

echo 'Success';
?>