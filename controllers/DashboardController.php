<?php
namespace Controllers;

use Models\Student;
use Models\SPOC;
use Models\Course;
use Models\University;

class DashboardController {
    public function index() {
        include '../config/database.php'; // Assuming you have a database connection file

        $university_count = University::getCount($conn);
        $student_count = Student::getCount($conn);
        $spoc_count = SPOC::getCount($conn);
        $course_count = Course::getCount($conn);

        $spocs = SPOC::getAll($conn);
        $universities = University::getAll($conn);
        $courses = Course::getAll($conn);

        $conn->close();

        include '../views/dashboard.php';
    }
}
?>