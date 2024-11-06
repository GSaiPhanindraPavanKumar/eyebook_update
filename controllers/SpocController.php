<?php 

namespace Controllers;

use Models\Spoc;
use Models\Database;
use Models\Faculty;

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
        $universities = $spocModel->getAllUniversities();
        $courses = $spocModel->getAllCourses();

        require 'views/spoc/dashboard.php';
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
            $year = $_POST['year'];
            $department = $_POST['department'];
            $university_id = $profile['university_id'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $spocModel->addFaculty($conn, $name, $email, $phone, $section, $stream, $year, $department, $university_id, $password);
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


}