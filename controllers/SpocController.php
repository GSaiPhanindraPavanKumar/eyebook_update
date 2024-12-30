<?php

namespace Controllers;

use Models\Spoc;
use Models\Database;
use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\Assignment;
use Models\VirtualClassroom;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;

class SpocController {
    public function dashboard() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
    
        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $faculty_count = $spocModel->getFacultyCount($university_id);
        $student_count = $spocModel->getStudentCount($university_id);
        $course_count = Course::getCountspocByUniversityId($conn, $university_id); // Fetch course count for the university
        $faculties = Faculty::getAllByUniversity($conn, $university_id); // Fetch all faculties for the university
        $courses = Course::getAllspocByUniversity($conn, $university_id); // Fetch all courses for the university
    
        // Fetch virtual classes and assignments for the courses
        $virtualClassroomModel = new VirtualClassroom($conn);
        $assignmentModel = new Assignment();
    
        $course_ids = array_column($courses, 'id');
        $virtualClasses = $virtualClassroomModel->getVirtualClassroomsByCourseIds($course_ids);
        $assignments = $assignmentModel->getAssignmentsByCourseIds($conn, $course_ids);
    
        require 'views/spoc/dashboard.php';
    }

    public function manageStudents() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);

        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];

        $students = Student::getAllByUniversity($conn, $university_id); // Fetch all students for the university

        require 'views/spoc/manage_students.php';
    }

    public function manageCourses() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
    
        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $courses = Course::getAllByUniversity($conn, $university_id); // Fetch all courses for the university
    
        require 'views/spoc/manage_courses.php';
    }

    
    public function viewCourse($encoded_course_id) {
        $conn = Database::getConnection();
        $course_id = base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded_course_id));
        
        if (!$course_id) {
            die('Invalid course ID.');
        }
    
        $course = Course::getById($conn, $course_id);
        
        if (!$course) {
            die('Course not found.');
        }
    
        $username = $_SESSION['email'];
        $spocModel = new Spoc($conn);
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $allFaculty = Faculty::getAllByUniversity($conn, $university_id); // Fetch all faculty of the university
        $allStudents = Student::getAllByUniversity($conn, $university_id); // Fetch all students of the university
    
        $assignedFaculty = array_filter($allFaculty, function($faculty) use ($course_id) {
            $assigned_courses = $faculty['assigned_courses'] ? json_decode($faculty['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });
    
        $assignedStudents = array_filter($allStudents, function($student) use ($course_id) {
            $assigned_courses = $student['assigned_courses'] ? json_decode($student['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });
    
        require 'views/spoc/view_course.php';
    }

    public function assignFaculty() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::assignCourse($conn, $faculty_id, $course_id);
            Course::assignFaculty($conn, $course_id, $faculty_id);
        }
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }
    
    public function assignStudents() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $student_ids = $_POST['student_ids'];
    
        foreach ($student_ids as $student_id) {
            Student::assignCourse($conn, $student_id, $course_id);
        }
        Course::assignStudents($conn, $course_id, $student_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }

    public function unassignFaculty() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::unassignCourse($conn, $faculty_id, $course_id);
        }
        Course::unassignFaculty($conn, $course_id, $faculty_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }
    
    public function unassignStudents() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $student_ids = $_POST['student_ids'] ?? [];
    
        if (!empty($student_ids)) {
            foreach ($student_ids as $student_id) {
                Student::unassignCourse($conn, $student_id, $course_id);
            }
            Course::unassignStudents($conn, $course_id, $student_ids);
        }
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
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
        if (!is_array($course['course_book'])) {
            $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_book'][0]['scorm_url'];
    
        require 'views/spoc/book_view.php';
    }

    public function manageFaculties() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);

        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $faculty = $this->fetchFaculty($conn, $university_id, $search, $limit, $offset);
        $totalFaculty = $this->countFaculty($conn, $university_id, $search);
        $totalPages = ceil($totalFaculty / $limit);

        require 'views/spoc/manage_faculty.php';
    }

    private function fetchFaculty($conn, $university_id, $search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search) LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%$search%";
        $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
        $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countFaculty($conn, $university_id, $search = '') {
        $sql = "SELECT COUNT(*) as count FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search)";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%$search%";
        $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
        $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function userProfile() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
        $spoc = $spocModel->getUserProfile($conn);
        require 'views/spoc/profile.php';
    }

    public function addFaculty() {
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
        $profile = $spocModel->getUserProfile($conn);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $section = $_POST['section'];
            $stream = $_POST['stream'];
            $department = $_POST['department'];
            $university_id = $profile['university_id'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
            $spocModel->addFaculty($conn, $name, $email, $phone, $section, $stream, $department, $university_id, $password);
            $message = "Faculty added successfully.";
            $message_type = "success";
            header('Location: /spoc/addFaculty');
            exit();
        }
        require 'views/spoc/addFaculty.php';
    }

    public function updatePassword() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $spoc_id = $_SESSION['spoc']['id'];
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);

            Spoc::updatePassword($conn, $spoc_id, $new_password);

            $message = "Password updated successfully.";
            $message_type = "success";
        }

        require 'views/spoc/updatePassword.php';
    }

    public static function getByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}