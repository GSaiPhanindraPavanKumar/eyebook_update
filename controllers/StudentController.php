<?php
namespace Controllers;

use Models\Course;
use Models\Database;
use Models\Student;
use Models\Assignment;
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
        if (!isset($_SESSION['email'])) {
            die('Email not set in session.');
        }
        $student = Student::getByEmail($conn, $_SESSION['email']);
    
        // Ensure course_book is an array
        if (!is_array($course['course_book'])) {
            $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        }
    
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
    
        // Assuming the first unit for simplicity
        $unit = $course['course_book'][0];
        $index_path = $unit['scorm_url'];
    
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
            $student = Student::getByEmail($conn, $_SESSION['email']);
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    
            Student::updatePassword($conn, $student['id'], $new_password);
    
            $message = "Password updated successfully.";
            $message_type = "success";
        }
    
        require 'views/student/updatePassword.php';
    }

    
        public function manageAssignments() {
            $conn = Database::getConnection();
            $student_id = $_SESSION['email'];
            $assignments = Assignment::getAssignmentsByStudentId($conn, $student_id);
            require 'views/student/manage_assignments.php';
        }
    
        public function submitAssignment($assignmentId) {
            $conn = Database::getConnection();
            $student_id = $_SESSION['student_id'];
            $messages = [];
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $file_content = null;
        
                if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
                    $file_content = file_get_contents($_FILES['assignment_file']['tmp_name']);
                }
        
                try {
                    Student::submitAssignment($conn, $student_id, $assignmentId, $file_content);
                    $messages[] = "Assignment submitted successfully.";
                } catch (PDOException $e) {
                    $messages[] = "Error submitting assignment: " . $e->getMessage();
                }
            }
        
            $assignment = Assignment::getById($conn, $assignmentId);
            require 'views/student/assignment_submit.php';
        }
        
        public function viewGrades() {
            $conn = Database::getConnection();
            $student_id = $_SESSION['student_id'];
            $grades = Assignment::getGradesByStudentId($conn, $student_id);
            require 'views/student/view_grades.php';
        }

    
    }
?>