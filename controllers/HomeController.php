<?php
namespace Controllers;

use Models\UserModel;

class HomeController {
    public function index() {
        $userModel = new UserModel();
        $students = $userModel->getAllStudents();
        require 'views/homeView.php';
    }
}
?>