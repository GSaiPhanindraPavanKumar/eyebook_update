<?php
require_once 'AdminModel.php';

class AdminController {
    private $model;

    public function __construct($db) {
        $this->model = new AdminModel($db);
    }

    public function initializeDatabase() {
        return $this->model->createAdminsTable();
    }

    // public function registerUser($username, $password) {
    //     return $this->model->register($username, $password);
    // }
}
?>