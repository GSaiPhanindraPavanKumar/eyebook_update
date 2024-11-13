<?php

namespace Controllers;

use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\University;
use Models\Database;
use Models\VirtualClassroom;

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

    public function profile() {
        $conn = Database::getConnection();
        $userId = $_SESSION['email'];
        $userData = Faculty::getById($conn, $userId);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = htmlspecialchars($_POST['name']);
            $jobTitle = 'Faculty';
            $email = htmlspecialchars($_POST['email']);
            $phone = htmlspecialchars($_POST['phone']);
            $department = htmlspecialchars($_POST['department']);
            $profileImage = isset($_FILES['profileImage']) && $_FILES['profileImage']['size'] > 0 ? file_get_contents($_FILES['profileImage']['tmp_name']) : $userData['profileImage'];

            Faculty::update($conn, $userId, $name, $jobTitle, $email, $phone, $department, $profileImage);

            // Refresh user data
            $userData = Faculty::getById($conn, $userId);
        }

        require 'views/faculty/profile.php';
    }

    public function myCourses() {
        $conn = Database::getConnection();
        $courses = Course::getCoursesByFaculty($conn);

        require 'views/faculty/my_courses.php';
    }

    public function viewCourse($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
        
        require 'views/faculty/view_course.php';
    }

    public function viewBook($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        if (!$course || empty($course['course_book'])) {
            echo 'SCORM content not found.';
            exit;
        }
    
        // Assuming the first unit and first material for simplicity
        $unit = $course['course_book'][0];
        $material = $unit['materials'][0];
        // $index_path = 'http://localhost/eye_final/' . $material['indexPath'];

        $index_path = 'https://eyebook.phemesoft.com/' . $material['indexPath'];
    
        require 'views/faculty/book_view.php';
    }

    private function decodeId($hashedId) {
        $hashedId = str_replace(['-', '_'], ['+', '/'], $hashedId);
        return base64_decode($hashedId);
    }

    public function virtualClassroom() {
        $conn = Database::getConnection();
        $virtualClassroomModel = new VirtualClassroom($conn);
        $facultyClassrooms = $virtualClassroomModel->getAll();

        require 'views/faculty/virtual_classroom_dashboard.php';
    }

    public function createVirtualClassroom() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => uniqid(),
                'topic' => $_POST['topic'],
                'start_time' => $_POST['start_time'],
                'duration' => $_POST['duration'],
                'join_url' => 'https://zoom.us/j/' . uniqid() // Example join URL
            ];
            $virtualClassroomModel = new VirtualClassroom($conn);
            $virtualClassroomModel->create($data);
            header('Location: /faculty/virtual_classroom');
            exit();
        }
        require 'views/faculty/create_virtual_classroom.php';
    }

    public function downloadAttendance() {
        $conn = Database::getConnection();
        $classroomId = $_GET['classroom_id'];
        $virtualClassroomModel = new VirtualClassroom($conn);
        $attendance = $virtualClassroomModel->getAttendance($classroomId);

        // Code to generate and download attendance report
        // ...

        require 'views/faculty/download_attendance.php';
    }
}