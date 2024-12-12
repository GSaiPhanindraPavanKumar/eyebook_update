<?php

namespace Controllers;

use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\University;
use Models\Database;
use Models\Assignment;
use Models\VirtualClassroom;
use PDO;
use \PDOException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;
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
            $email = htmlspecialchars($_POST['email']);
            $phone = htmlspecialchars($_POST['phone']);
            $department = htmlspecialchars($_POST['department']);

            Faculty::update($conn, $userId, $name, $email, $phone, $department);

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

        if (!isset($_SESSION['email'])) {
            die('Email not set in session.');
        }
        $student = Student::getByEmail($conn, $_SESSION['email']);
    
        // Ensure course_book is an array
        if (!is_array($course['course_book'])) {
            $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        }
        
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
    
        // Ensure course_book is an array
        if (!is_array($course['course_book'])) {
            $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_book'][0]['scorm_url'];
    
        require 'views/faculty/book_view.php';
    }

    public function viewCoursePlan($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        if (!$course || empty($course['course_plan'])) {
            echo 'Course plan not found.';
            exit;
        }
    
        // Get the index_path for the course plan
        $index_path = $course['course_plan']['url'];
    
        require 'views/faculty/pdf_view.php';
    }
    
    public function viewMaterial($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        if (!$course || empty($course['course_materials'])) {
            echo 'Course materials not found.';
            exit;
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_materials'][0]['materials'][0]['indexPath'];
    
        require 'views/faculty/pdf_view.php';
    }

    public static function grade($conn, $assignmentId, $studentId, $grade, $feedback) {
        $sql = "UPDATE submissions SET grade = :grade, feedback = :feedback WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':feedback', $feedback);
        $stmt->bindParam(':assignment_id', $assignmentId);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
    }

    public static function getSubmission($conn, $assignmentId, $studentId) {
        $sql = "SELECT * FROM submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':assignment_id', $assignmentId);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    private function decodeId($hashedId) {
        $hashedId = str_replace(['-', '_'], ['+', '/'], $hashedId);
        return base64_decode($hashedId);
    }

    public function virtualClassroom() {
        require 'views/faculty/virtual_classroom_dashboard.php';
    }
    public function createVirtualClassroom() {
        // require 'views/faculty/create_virtual_classroom.php';
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
        $year = $_POST['year'] ?? null;
        $section = $_POST['section'] ?? null;
        $students = Student::getAllBySectionYearAndUniversity($conn, $facultyId, $year, $section);

        require 'views/faculty/manage_students.php';
    }


    // Other methods...

    public function downloadReport($assignmentId, $format) {
        $conn = Database::getConnection();
        $submissions = Assignment::getSubmissions($conn, $assignmentId);

        if ($format === 'pdf') {
            $this->generatePDFReport($submissions);
        } elseif ($format === 'excel') {
            $this->generateExcelReport($submissions);
        }
    }

    private function generatePDFReport($submissions) {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = '<h1>Assignment Report</h1>';
        $html .= '<table border="1" cellpadding="10" cellspacing="0">';
        $html .= '<thead><tr><th>S.No</th><th>Student Name</th><th>Email</th><th>Grade</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($submissions as $index => $submission) {
            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>' . htmlspecialchars($submission['student_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($submission['email']) . '</td>';
            $html .= '<td>' . htmlspecialchars($submission['grade']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('assignment_report.pdf', ['Attachment' => false]);
    }

    private function generateExcelReport($submissions) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'S.No');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Grade');

        foreach ($submissions as $index => $submission) {
            $sheet->setCellValue('A' . ($index + 2), $index + 1);
            $sheet->setCellValue('B' . ($index + 2), $submission['student_name']);
            $sheet->setCellValue('C' . ($index + 2), $submission['email']);
            $sheet->setCellValue('D' . ($index + 2), $submission['grade']);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'assignment_report.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
            


    public function updatePassword() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $faculty_id = $_SESSION['faculty_id'];
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    
            Faculty::updatePassword($conn, $faculty_id, $new_password);
    
            $message = "Password updated successfully.";
            $message_type = "success";
        }
    
        require 'views/faculty/updatePassword.php';
    }





//     public function gradeAssignment($assignmentId, $studentId) {
//     $conn = Database::getConnection();

//     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         $grade = $_POST['grade'];
//         $feedback = $_POST['feedback'];
//         Assignment::grade($conn, $assignmentId, $studentId, $grade, $feedback);

//         header('Location: /faculty/manage_assignments');
//         exit;
//     }

//     $submission = Assignment::getSubmission($conn, $assignmentId, $studentId);
//     require 'views/faculty/grade_assignment.php';
// }

        // public function manageAssignments() {
        //     $conn = Database::getConnection();
        //     $sql = "SELECT assignments.id, assignments.title, courses.name as course_name, assignments.deadline 
        //             FROM assignments 
        //             JOIN courses ON assignments.course_id = courses.id";
        //     $stmt = $conn->prepare($sql);
        //     $stmt->execute();
        //     $assignments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //     require 'views/faculty/manage_assignments.php';
        // }
    
    public function viewAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        require 'views/faculty/view_assignment.php';
    }

    public function gradeSubmissionPage($assignment_id, $student_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        // Find the specific student's submission
        $student_submission = null;
        foreach ($submissions as $submission) {
            if ($submission['student_id'] == $student_id) {
                $student_submission = $submission;
                break;
            }
        }

        require 'views/faculty/grade_submission.php';
    }

    public function gradeSubmission($assignment_id, $student_id) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $grade = $_POST['grade'];
            $feedback = $_POST['feedback'];
            Assignment::grade($conn, $assignment_id, $student_id, $grade, $feedback);
            header('Location: /faculty/view_assignment/' . $assignment_id);
            exit;
        }
    }

    public function markAssignment() {
        $conn = Database::getConnection();
        $submissionId = $_POST['submission_id'];
        $grade = $_POST['grade'];
        $feedback = $_POST['feedback'];

        $sql = "UPDATE submissions SET grade = :grade, feedback = :feedback WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':feedback', $feedback);
        $stmt->bindParam(':id', $submissionId);
        $stmt->execute();

        header('Location: /faculty/manage_assignments');
    }



    public function manageAssignments() {
        $conn = Database::getConnection();
        $faculty_id = $_SESSION['faculty_id'];
        $assignments = Assignment::getAssignmentsByFaculty($conn, $faculty_id);

        // Fetch submission count for each assignment
        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }

        require 'views/faculty/manage_assignments.php';
    }

    

    public function createAssignment() {
        $conn = Database::getConnection();
        $messages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['assignment_title'];
            $description = $_POST['assignment_description'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $file_content = null;

            if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
                $file_content = file_get_contents($_FILES['assignment_file']['tmp_name']);
            }

            try {
                $assignment_id = Assignment::create($conn, $title, $description, $due_date, $course_ids, $file_content);

                foreach ($course_ids as $course_id) {
                    Course::addAssignmentToCourse($conn, $course_id, $assignment_id);
                }

                header('Location: /faculty/manage_assignments');
                exit;
            } catch (PDOException $e) {
                $messages[] = "Error creating assignment: " . $e->getMessage();
            }
        }

        $faculty_id = $_SESSION['faculty_id'];
        $assigned_courses = Faculty::getAssignedCourses($conn, $faculty_id);
        $courses = Course::getOngoingCoursesByIds($conn, $assigned_courses);

        require 'views/faculty/assignment_create.php';
    }

    public function archiveCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['archive_course_id'];
            Course::archiveCourse($conn, $course_id);
    
            $_SESSION['message'] = 'Course archived successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /faculty/my_courses');
            exit();
        }
    }




    // public function viewAssignment($assignmentId) {
    //     $conn = Database::getConnection();
    //     $assignment = Assignment::getById($conn, $assignmentId);
    //     $submissions = Assignment::getSubmissions($conn, $assignmentId);
    //     require 'views/faculty/view_assignment.php';
    // }

    // public function gradeAssignment($assignmentId, $studentId) {
    //     $conn = Database::getConnection();
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $grade = $_POST['grade'];
    //         $feedback = $_POST['feedback'];
    //         Assignment::grade($conn, $assignmentId, $studentId, $grade, $feedback);
    //         header('Location: /faculty/manage_assignments');
    //         exit;
    //     }
    //     $submission = Assignment::getSubmission($conn, $assignmentId, $studentId);
    //     require 'views/faculty/grade_assignment.php';
    // }


    public function gradeAssignment($assignmentId, $studentId) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $marks = $_POST['marks'];
            $feedback = $_POST['feedback'];
            $grade = $this->calculateGrade($marks);

            Assignment::grade($conn, $assignmentId, $studentId, $grade, $feedback);
            header('Location: /faculty/manage_assignments');
            exit;
        }
        $submission = Assignment::getSubmission($conn, $assignmentId, $studentId);
        require 'views/faculty/grade_assignment.php';
    }

    private function calculateGrade($marks) {
        if ($marks >= 90) {
            return 'A';
        } elseif ($marks >= 80) {
            return 'B';
        } elseif ($marks >= 70) {
            return 'C';
        } elseif ($marks >= 60) {
            return 'D';
        } else {
            return 'F';
        }
    }
}
    
