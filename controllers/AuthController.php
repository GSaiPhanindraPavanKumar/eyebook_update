<?php
namespace Controllers;
use Models\Admin;

class AuthController {
    private $adminModel;

    public function __construct(Admin $adminModel = null) {
        $this->adminModel = $adminModel ?: new Admin();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $admin = $this->adminModel->login($username, $password);
                if ($admin) {
                    $_SESSION['admin'] = $admin;
                    $_SESSION['admin']['username'] = $admin['username']; // Store username in session
                    header('Location: admin/dashboard');
                } else {
                    echo 'Invalid username or password';
                }
            } else {
                echo 'Username and password are required';
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require 'views/index.php';
        }
    }
}
?>