<?php

namespace Controllers;

use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\University;
use Models\Database;
use Models\Assignment;
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
        $index_path = 'http://localhost/eye_final/' . $material['indexPath'];

        // $index_path = 'https://eyebook.phemesoft.com/' . $material['indexPath'];
    
        require 'views/faculty/book_view.php';
    }

    private function decodeId($hashedId) {
        $hashedId = str_replace(['-', '_'], ['+', '/'], $hashedId);
        return base64_decode($hashedId);
    }

    public function virtualClassroom() {
        require 'views/faculty/virtual_classroom_dashboard.php';
    }
    public function createVirtualClassroom() {
        require 'views/faculty/create_virtual_classroom.php';
    }
    public function downloadAttendance() {
        require 'views/faculty/download_attendance.php';
    }
    public function takeAttendance() {
        require 'views/faculty/take_attendance.php';
    }
    public function saveAttendance() {
        require 'views/faculty/save_attendance.php';
    }

    public function manageStudents() {
        $conn = Database::getConnection();
        $facultyId = $_SESSION['email']; // Assuming faculty_id is stored in session
        $students = Student::getAllByFaculty($conn, $facultyId);

        require 'views/faculty/manage_students.php';
    }

    public function viewReports() {
        $conn = Database::getConnection();
        $assessments = getAssessments();
        require 'views/faculty/view_reports.php';
    }

    public function downloadReport($assessmentId) {
        $conn = Database::getConnection();
        $results = getAssessmentResults($assessmentId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=assessment_report.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student Name', 'Score', 'Grade']);

        foreach ($results as $result) {
            fputcsv($output, [$result['student_name'], $result['score'], $result['grade']]);
        }

        fclose($output);
        exit;
    }

    public function createAssignment() {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $instructions = $_POST['instructions'];
            $deadline = $_POST['deadline'];
            $course_id = $_POST['course_id'];

            Assignment::create($conn, $title, $instructions, $deadline, $course_id);

            header('Location: /faculty/manage_assignments');
            exit;
        }

        $courses = Course::getCoursesByFaculty($conn);
        require 'views/faculty/create_assignment.php';
    }

    public function manageAssignments() {
        $conn = Database::getConnection();
        $assignments = Assignment::getAllByFaculty($conn, $_SESSION['email']);

        require 'views/faculty/manage_assignments.php';
    }

    public function gradeAssignment($assignmentId, $studentId) {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $grade = $_POST['grade'];
            Assignment::grade($conn, $assignmentId, $studentId, $grade);

            header('Location: /faculty/manage_assignments');
            exit;
        }

        $submission = Assignment::getSubmission($conn, $assignmentId, $studentId);
        require 'views/faculty/grade_assignment.php';
    }

    public function updatePassword() {
        // ...existing code...
    }

    public function discussionForum($id) {
        // ...existing code...
    }

    public function createAssessment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission for creating an assessment
            // ...existing code...
        } else {
            // Display the assessment creation form
            require 'views/faculty/create_assessment.php';
        }
    }

    public function manageAssessments() {
        // ...existing code...
    }

    public function generateQuestions() {
        // ...existing code...
    }
}