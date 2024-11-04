<?php
namespace Controllers;

use Models\Course;
use Models\Database;

class StudentController {
    public function viewCourse($id) {
        $conn = Database::getConnection();
        $course = Course::getById($conn, $id);
        require 'views/student/view_course.php';
    }
}
?>