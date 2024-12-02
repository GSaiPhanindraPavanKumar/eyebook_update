<?php 

namespace Controllers;

use Models\Spoc;
use Models\Database;
use Models\Faculty;
use Models\Student;
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
        $spoc_count = $spocModel->getSpocCount();
        $course_count = $spocModel->getCourseCount();
        $spocs = $spocModel->getAllSpocs();
        $faculties = Faculty::getAllByUniversity($conn, $university_id); // Fetch all faculties for the university
        $students = Student::getAllByUniversity($conn, $university_id); // Fetch all students for the university
        $universities = $spocModel->getAllUniversities();
        $courses = $spocModel->getAllCourses();

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