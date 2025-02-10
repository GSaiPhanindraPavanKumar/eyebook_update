<?php
namespace Controllers;

use Models\Admin;
use Models\Spoc;
use Models\Faculty;
use Models\Student;
use Models\Database;
use Models\University;

class AuthController {
    private $adminModel;
    private $spocModel;
    private $facultyModel;
    private $studentModel;

    public function __construct() {
        $conn = Database::getConnection();
        $this->adminModel = new Admin($conn);
        $this->spocModel = new Spoc($conn);
        $this->facultyModel = new Faculty($conn);
        $this->studentModel = new Student($conn);
    }

    private function checkExistingSession() {
        if (isset($_SESSION['admin'])) {
            return ['role' => 'admin', 'name' => $_SESSION['admin']['name'] ?? 'Administrator'];
        }
        if (isset($_SESSION['email'])) {
            if (isset($_SESSION['faculty_id'])) {
                return ['role' => 'faculty', 'name' => $_SESSION['email']];
            }
            if (isset($_SESSION['student_id'])) {
                return ['role' => 'student', 'name' => $_SESSION['email']];
            }
            if (isset($_SESSION['spoc_id'])) {
                return ['role' => 'spoc', 'name' => $_SESSION['email']];
            }
        }
        return null;
    }

    public function showStudentRegisterForm() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        require 'views/studentRegisterView.php';
    }

    public function registerStudent() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = Database::getConnection();
    
            if ($_POST['university_id'] === 'other') {
                // Create new university
                $universityData = [
                    'long_name' => $_POST['long_name'],
                    'short_name' => $_POST['short_name'],
                    'location' => $_POST['location'],
                    'country' => $_POST['country']
                ];
                $result = University::addUniversity($conn, $universityData['long_name'], $universityData['short_name'], $universityData['location'], $universityData['country'], null, null, null, null, null);
                if ($result['message_type'] === 'error') {
                    // Handle error (e.g., show error message to the user)
                    echo $result['message'];
                    return;
                }
                $university_id = $result['university_id'];
            } else {
                $university_id = $_POST['university_id'];
            }
    
            $data = [
                'regd_no' => $_POST['regd_no'],
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'section' => $_POST['section'],
                'stream' => $_POST['stream'],
                'year' => $_POST['year'],
                'dept' => $_POST['dept'],
                'university_id' => $university_id,
                'password' => $_POST['password']
            ];
    
            Student::create($conn, $data);
    
            header('Location: /login');
            exit();
        }
        require 'views/studentRegisterView.php';
    }

    private function clearSession() {
        $_SESSION = array();
        session_destroy();
        session_start();
    }

    public function login() {
        $message = '';
        $warning = '';
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Check for existing session
                $existingSession = $this->checkExistingSession();
                if ($existingSession) {
                    $warning = "Note: You are currently logged in as {$existingSession['name']} ({$existingSession['role']}). Proceeding will end that session.";
                }

                // Check admin credentials
                $admin = $this->adminModel->login($username, $password);
                if ($admin) {
                    $this->clearSession();
                    $_SESSION['admin'] = $admin;
                    $_SESSION['admin_id'] = $admin['id'];
                    $this->adminModel->updateLastLogin($admin['id']);
                    header('Location: /admin/dashboard');
                    exit();
                }
    
                // Check spoc credentials
                $spoc = $this->spocModel->login($username, $password);
                if ($spoc) {
                    $this->clearSession();
                    $_SESSION['email'] = $username;
                    $this->spocModel->updateLoginDetails($spoc['id']);
                    header('Location: /spoc/dashboard');
                    exit();
                }

                // Check faculty credentials
                $faculty = $this->facultyModel->login($username, $password);
                if ($faculty) {
                    $this->clearSession();
                    $_SESSION['faculty_id'] = $faculty['id'];
                    $_SESSION['email'] = $username;
                    $this->facultyModel->updateLoginDetails($faculty['id']);
                    header('Location: /faculty/dashboard');
                    exit();
                }

                // Check student credentials
                $student = $this->studentModel->login($username, $password);
                if ($student) {
                    $this->clearSession();
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['email'] = $username;
                    $this->studentModel->updateLoginDetails($student['id']);
                    header('Location: /student/dashboard');
                    exit();
                }
    
                $message = 'Invalid username or password';
            } else {
                $message = 'Username and password are required';
            }
        }
    
        require 'views/index.php';
    }

    public function forceResetPassword() {
        if (!isset($_SESSION['force_reset_password']) || !$_SESSION['force_reset_password']) {
            header('Location: /login');
            exit();
        }
        require 'views/force_reset_password.php';
    }
    
    public function handleForceResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
    
            if ($newPassword !== $confirmPassword) {
                $message = 'Passwords do not match.';
                require 'views/force_reset_password.php';
                return;
            }
    
            $userId = $_SESSION['user_id'];
            $userType = $_SESSION['user_type'];
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $conn = Database::getConnection();
            if ($userType === 'student') {
                Student::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'students';
            } elseif ($userType === 'faculty') {
                Faculty::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'faculty';
            } elseif ($userType === 'spoc') {
                Spoc::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'spocs';
            }
    
            // Update the first_login and login_count fields
            $sql = "UPDATE $tableName SET first_login = NOW(), login_count = login_count + 1 WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $userId]);
    
            unset($_SESSION['force_reset_password']);
            $_SESSION['password_reset_success'] = true;

            header('Location: /login');
            exit();
        }
    }

    public function logout() {
        $this->clearSession();
        header('Location: /');
        exit();
    }

    public function checkAuth() {
        if (!isset($_SESSION['email']) && !isset($_SESSION['admin'])) {
            header("Location: /session-timeout");
            exit;
        }
    }
}