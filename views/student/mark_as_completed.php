<?php
use Models\Database;
use Models\Student;

$conn = Database::getConnection();

$studentId = $_SESSION['student_id']; // Assuming student ID is stored in session
$courseId = $_POST['course_id'];
$indexPath = $_POST['indexPath'];

// Fetch the student
$student = Student::getById($conn, $studentId);
if (!$student) {
    die('Student not found');
}

// Update the completed_books field
$completedBooks = json_decode($student['completed_books'], true) ?? [];
if (!isset($completedBooks[$courseId])) {
    $completedBooks[$courseId] = [];
}
if (!in_array($indexPath, $completedBooks[$courseId])) {
    $completedBooks[$courseId][] = $indexPath;
}

$completedBooksJson = json_encode($completedBooks);
$sql = "UPDATE students SET completed_books = :completed_books WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['completed_books' => $completedBooksJson, 'id' => $studentId]);

echo 'Success';
?>