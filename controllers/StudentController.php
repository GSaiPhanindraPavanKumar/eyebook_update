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
    
        // Ensure course_book is an array
        //if (!is_array($course['course_book'])) {
        //    $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        //}
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_book'][0]['scorm_url'];
    
        require 'views/student/book_view.php';
    }

    public function viewMaterial($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        if (!$course || empty($course['course_materials'])) {
            echo 'Course materials not found.';
            exit;
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_materials'][0]['materials'][0]['indexPath'];
    
        require 'views/student/pdf_view.php';
    }
    
    public function viewCoursePlan($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        if (!$course || empty($course['course_plan'])) {
            echo 'Course plan not found.';
            exit;
        }
    
        // Get the index_path for the course plan
        $index_path = $course['course_plan']['url'];
    
        require 'views/student/pdf_view.php';
    }
    public function profile() {
        $conn = Database::getConnection();
    
        // Check if the user is not logged in
        if (!isset($_SESSION['email'])) {
            header("Location: /login");
            exit;
        }
    
        // Get the email from the session
        $email = $_SESSION['email'];
    
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT students.*, universities.long_name AS university_name 
                                FROM students 
                                JOIN universities ON students.university_id = universities.id 
                                WHERE students.email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check if user data is available; if not, set default placeholder values
        $name = isset($userData['name']) ? htmlspecialchars($userData['name']) : "Danny McLoan";
        $profileImage = isset($userData['profileImage']) ? $userData['profileImage'] : null;
        $email = isset($userData['email']) ? htmlspecialchars($userData['email']) : "danny@example.com";
        $regd_no = isset($userData['regd_no']) ? htmlspecialchars($userData['regd_no']) : "123456";
        $section = isset($userData['section']) ? htmlspecialchars($userData['section']) : "A";
        $stream = isset($userData['stream']) ? htmlspecialchars($userData['stream']) : "Science";
        $year = isset($userData['year']) ? htmlspecialchars($userData['year']) : "1st Year";
        $dept = isset($userData['dept']) ? htmlspecialchars($userData['dept']) : "Journalism";
        $university = isset($userData['university_name']) ? htmlspecialchars($userData['university_name']) : "Unknown University";
    
        // Handle form submission for profile update
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Update user data with the submitted form data
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $regd_no = htmlspecialchars($_POST['regd_no']);
            $section = htmlspecialchars($_POST['section']);
            $stream = htmlspecialchars($_POST['stream']);
            $year = htmlspecialchars($_POST['year']);
            $dept = htmlspecialchars($_POST['dept']);
    
            // Save the updated data to the database
            $stmt = $conn->prepare("UPDATE students SET name=?, email=?, regd_no=?, section=?, stream=?, year=?, dept=? WHERE email=?");
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $email);
            $stmt->bindParam(3, $regd_no);
            $stmt->bindParam(4, $section);
            $stmt->bindParam(5, $stream);
            $stmt->bindParam(6, $year);
            $stmt->bindParam(7, $dept);
            $stmt->bindParam(8, $email);
            $stmt->execute();
    
            // Update the userData array for display
            $userData['name'] = $name;
            $userData['email'] = $email;
            $userData['regd_no'] = $regd_no;
            $userData['section'] = $section;
            $userData['stream'] = $stream;
            $userData['year'] = $year;
            $userData['dept'] = $dept;
        }
    
        require 'views/student/profile.php';
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