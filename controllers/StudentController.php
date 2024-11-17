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

        require 'views/student/book_view.php';
    }
}
?>