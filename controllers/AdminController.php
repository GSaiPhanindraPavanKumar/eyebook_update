<?php 
namespace Controllers;

use Models\Admin as AdminModel;
use Models\Student;
use Models\Spoc;
use Models\Course;
use Models\University;
use Models\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminController {
    public function index() {
        $admin = new AdminModel();
        require 'views/admin/index.php';
    }

    public function userProfile(){
        $conn = Database::getConnection();
        $adminModel = new AdminModel();
        $admin = $adminModel->getUserProfile($conn);
        require 'views/admin/userProfile.php';
    }

    public function dashboard() {
        $conn = Database::getConnection();
        $adminModel = new AdminModel();
        $user = $adminModel->getUserProfile($conn);


        $university_count = University::getCount($conn);
        $student_count = Student::getCount($conn);
        $spoc_count = Spoc::getCount($conn);
        $course_count = Course::getCount($conn);

        $spocs = Spoc::getAll($conn);
        $universities = University::getAll($conn);
        $courses = Course::getAll($conn);

        require 'views/admin/dashboard.php';
    }

    public function addUniversity() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $long_name = $_POST['long_name'];
            $short_name = $_POST['short_name'];
            $location = $_POST['location'];
            $country = $_POST['country'];
            $spoc_name = $_POST['spoc_name'];
            $spoc_email = $_POST['spoc_email'];
            $spoc_phone = $_POST['spoc_phone'];
            $spoc_password = password_hash($_POST['spoc_password'], PASSWORD_BCRYPT);

            if (University::existsByShortName($conn, $short_name)) {
                $message = "Duplicate entry for short name: " . $short_name;
                $message_type = "warning";
            } else if (Spoc::existsByEmail($conn, $spoc_email)) {
                $message = "Duplicate entry for email: " . $spoc_email;
                $message_type = "warning";
            } else {
                $university = new University($conn);
                $result = $university->addUniversity($conn,$long_name, $short_name, $location, $country, $spoc_name, $spoc_email, $spoc_phone, $spoc_password);
                $message = $result['message'];
                $message_type = $result['message_type'];
            }
        }

        require 'views/admin/addUniversity.php';
    }

    public function manageUniversity() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        require 'views/admin/manageUniversity.php';
    }

    public function updateUniversity() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
            $id = $_POST['id'];
            $long_name = $_POST['long_name'];
            $short_name = $_POST['short_name'];
            $location = $_POST['location'];
            $country = $_POST['country'];

            University::update($conn, $id, $long_name, $short_name, $location, $country);

            header('Location: /admin/manageUniversity');
            exit();
        }
    }

    public function deleteUniversity() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
            $id = $_POST['delete'];

            University::delete($conn, $id);

            header('Location: /admin/manageUniversity');
            exit();
        }
    }

    public function updatePassword() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $admin_id = $_POST['admin_id'];
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);

            AdminModel::updatePassword($conn, $admin_id, $new_password);

            $message = "Password updated successfully.";
            $message_type = "success";
        }

        require 'views/admin/updatePassword.php';
    }

    
    
    public function uploadStudents() {
        $conn = Database::getConnection();
        $message = '';
        $message_type = '';
    

        $allowed_file_types = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $university_id = filter_input(INPUT_POST, 'university_id', FILTER_SANITIZE_NUMBER_INT);
            $file = $_FILES['file'];
    
            if ($file['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $file['tmp_name'];
                $file_name = basename($file['name']);
                $file_size = $file['size'];
                $file_type = $file['type'];
    

                if (!in_array($file_type, $allowed_file_types)) {
                    $message = "Invalid file type. Only CSV and Excel files are allowed.";
                    $message_type = "error";
                } elseif ($file_size > 5000000) { 
                    $message = "File size exceeds the limit of 5MB.";
                    $message_type = "error";
                } else {
                    try {
                        Student::addStudentsFromExcel($conn, $file_tmp, $university_id);
                        $message = "File uploaded and students added successfully";
                        $message_type = "success";
                    } catch (\Exception $e) {
                        $message = "Error processing file: " . $e->getMessage();
                        $message_type = "error";
                    }
                }
            } else {
                $message = "Error uploading file: " . $file['error'];
                $message_type = "error";
            }
        }    

        $universities = University::getAll($conn);
    
        require 'views/admin/uploadStudents.php';
    }

    public function addCourse() {
        $conn = Database::getConnection();
        require 'views/admin/add_courses.php';
    }

    public function manageCourse() {
        require 'views/admin/manage_courses.php';
    }


    public function courseView($id) {
        $conn = Database::getConnection();
        $course = Course::getById($conn, $id);
        $universities = Course::getUniversitiesByCourseId($conn, $course);

        require 'views/admin/view_course.php';
    }
}
?>