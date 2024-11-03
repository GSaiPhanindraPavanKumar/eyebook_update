<?php 

namespace Controllers;

use Models\Spoc;
use Models\Database;

class SpocController {
    public function dashboard() {
        $conn = Database::getConnection();
        // Fetch necessary data for the dashboard
        require 'views/spoc/dashboard.php';
    }

    public function userProfile() {
        $conn = Database::getConnection();
        $spocModel = new Spoc();
        $spoc = $spocModel->getUserProfile($conn);
        require 'views/spoc/dashboard.php';
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
}