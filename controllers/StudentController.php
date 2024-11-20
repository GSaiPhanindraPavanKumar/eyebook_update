<?php
namespace Controllers;

use Models\Course;
use Models\Database;
use Models\Student;
use PDO;
use PDOException;

class StudentController {
    public function viewCourse($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);

        // Fetch the student data
        if (!isset($_SESSION['student_id'])) {
            die('Student ID not set in session.');
        }
        $studentId = $_SESSION['student_id'];
        $student = Student::getById($conn, $studentId);

        require 'views/student/view_course.php';
    }
    public function viewBook($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);

        if (!$course || empty($course['course_book'])) {
            echo 'SCORM content not found.';
            exit;
        }

        // Assuming the first unit and first material for simplicity
        $unit = $course['course_book'][0];
        $material = $unit['materials'][0];
        $index_path = 'https://eyebook.phemesoft.com/' . $material['indexPath'];
        // $index_path = 'http://localhost/new/eyebook_update/' . $material['indexPath'];

        require 'views/student/book_view.php';
    }
    function getCoursesWithProgress($studentId) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM courses WHERE status = 'ongoing'");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT completed_books FROM students WHERE id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

        foreach ($courses as &$course) {
            $courseId = $course['id'];
            $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
            $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
            $course['progress'] = ($totalBooks > 0) ? ($completedBooksCount / $totalBooks) * 100 : 0;
        }

        usort($courses, function($a, $b) {
            return $a['progress'] <=> $b['progress'];
        });

        return $courses;
    }
    public function updatePassword() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $student_id = $_SESSION['student_id'];
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    
            Student::updatePassword($conn, $student_id, $new_password);
    
            $message = "Password updated successfully.";
            $message_type = "success";
        }
    
        require 'views/student/updatePassword.php';
    }

}
?>