<?php
namespace Controllers;

use Models\Course;
use Models\Database;
use PDO;
use PDOException;

class StudentController {
    public function viewCourse($id) {
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

        // Check if the student is subscribed to the course
        $sql = "SELECT * FROM subscriptions WHERE student_id = :student_id AND course_id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $student_id, ':course_id' => $id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subscription) {
            $course = Course::getById($conn, $id);
            require 'views/student/view_course.php';
        } else {
            echo "You are not subscribed to this course.";
        }
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
        // $index_path = 'http://localhost/eye_final/' . $material['indexPath'];
        $index_path = 'https://eyebook.phemesoft.com/' . $material['indexPath'];

        require 'views/student/book_view.php';
    }
}
?>