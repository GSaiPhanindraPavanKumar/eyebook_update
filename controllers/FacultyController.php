<?php


namespace Controllers;

use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\University;
use Models\Database;



class FacultyController {
    public function index() {
        $faculty = new Faculty();
        require 'views/faculty/index.php';
    }

    public function dashboard() {
        $faculty = new Faculty();
        $faculty_id = $_SESSION['email'];
        $email = $faculty_id;



        require 'views/faculty/dashboard.php';
    }

    public function userProfile() {
        $faculty = new Faculty();
        $conn = Database::getConnection();
        $faculty = $faculty->getUserProfile($conn);
        require 'views/faculty/profile.php';
    }

    public function myCourses() {
        $conn = Database::getConnection();
        $courses = Course::getCoursesByFaculty($conn);

        require 'views/faculty/my_courses.php';
    }

    public function viewCourse($id) {
        $conn = Database::getConnection();
        $course = Course::getById($conn, $id);
        require 'views/faculty/view_course.php';
    }
}
?>