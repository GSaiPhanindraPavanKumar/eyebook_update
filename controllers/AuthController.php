<?php
namespace Controllers;

use Models\Admin;
use Models\Spoc;
use Models\Faculty;
use Models\Student;

class AuthController {
    private $adminModel;
    private $spocModel;
    private $facultyModel;
    private $studentModel;


    public function __construct(Admin $adminModel = null, Spoc $spocModel = null, Faculty $facultyModel = null) {
        $this->adminModel = $adminModel ?: new Admin();
        $this->spocModel = $spocModel ?: new Spoc();
        $this->facultyModel = $facultyModel ?: new Faculty();
        $this->studentModel = new Student();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Check admin credentials
                $admin = $this->adminModel->login($username, $password);
                if ($admin) {
                    $_SESSION['admin'] = $admin;
                    header('Location: /admin/dashboard');
                    exit();
                }

                // Check spoc credentials
                $spoc = $this->spocModel->login($username, $password);
                if ($spoc) {
                    $_SESSION['email'] = $username;
                    header('Location: /spoc/dashboard');
                    exit();
                }

                // Check faculty credentials
                $faculty = $this->facultyModel->login($username, $password);
                if ($faculty) {
                    $_SESSION['email'] = $username;
                    header('Location: /faculty/dashboard');
                    exit();
                }

                $student = $this->studentModel->login($username, $password);
                if ($student) {
                    $_SESSION['email'] = $username;
                    header('Location: /student/dashboard');
                    exit();
                }

                // If neither admin, spoc, nor faculty credentials match
                $message = 'Invalid username or password';
            } else {
                $message = 'Username and password are required';
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require 'views/index.php';
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit();
    }
}
?>