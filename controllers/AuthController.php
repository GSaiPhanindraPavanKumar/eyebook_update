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

    public function login() {
        $message = ''; // Initialize the message variable
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];
    
                // Check admin credentials
                $admin = $this->adminModel->login($username, $password);
                if ($admin) {
                    $_SESSION['admin'] = $admin;
                    $_SESSION['admin_id'] = $admin['id'];
                    $this->adminModel->updateLastLogin($admin['id']);
                    header('Location: /admin/dashboard');
                    exit();
                }
    
                // Check spoc credentials
                $spoc = $this->spocModel->login($username, $password);
                if ($spoc) {
                    $_SESSION['email'] = $username;
                    $this->spocModel->updateLastLogin($spoc['id']);
                    $this->spocModel->updateLastLogin($spoc['id']);
                    header('Location: /spoc/dashboard');
                    exit();
                }
    
                // Check faculty credentials
                $faculty = $this->facultyModel->login($username, $password);
                if ($faculty) {
                    $_SESSION['faculty_id'] = $faculty['id'];
                    $_SESSION['email'] = $username;
                    $this->facultyModel->updateLastLogin($faculty['id']);
                    header('Location: /faculty/dashboard');
                    exit();
                }
    
                // Check student credentials
                $student = $this->studentModel->login($username, $password);
                if ($student) {
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['email'] = $username;
                    $this->studentModel->updateLastLogin($student['id']);
                    header('Location: /student/dashboard');
                    exit();
                }
    
                // If neither admin, spoc, faculty, nor student credentials match
                $message = 'Invalid username or password';
            } else {
                $message = 'Username and password are required';
            }
        }
    
        require 'views/index.php'; // Pass the message to the view
    }

    public function logout() {
        session_destroy();
        header('Location: /');
        exit();
        // Check if a session is already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Destroy the session
        session_unset();
        session_destroy();
    
        // Redirect to the login page after 1 second
        echo '<script>
                setTimeout(function() {
                    window.location.href = "/login";
                }, 1000);
              </script>';
        echo 'You have been logged out. Redirecting to login page...';
    }
}