<?php
namespace Controllers;
use Models\Admin;
use Models\Spoc;

class AuthController {
    private $adminModel;
    private $spocModel;

    public function __construct(Admin $adminModel = null, Spoc $spocModel = null) {
        $this->adminModel = $adminModel ?: new Admin();
        $this->spocModel = $spocModel ?: new Spoc();
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
                    $_SESSION['spoc'] = $spoc;
                    header('Location: /spoc/dashboard');
                    exit();
                }

                // If neither admin nor spoc credentials match
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