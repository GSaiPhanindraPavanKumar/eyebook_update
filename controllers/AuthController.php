<?php
namespace Controllers;

use Models\Admin;
use Models\Spoc;
use Models\Faculty;
use Models\Student;
use Models\Database;

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