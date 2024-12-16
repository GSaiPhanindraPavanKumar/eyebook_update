<?php

namespace Controllers;

use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\University;
use Models\Database;
use Models\Assignment;
use Models\VirtualClassroom;
use Models\Discussion;
use PDO;
use \PDOException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
require 'vendor/autoload.php';
require_once __DIR__ . '/../aws_config.php';

$bucketName = AWS_BUCKET_NAME;
$region = AWS_REGION;
$accessKey = AWS_ACCESS_KEY_ID;
$secretKey = AWS_SECRET_ACCESS_KEY;

// Debugging: Log the values of the configuration variables
error_log('AWS_BUCKET_NAME: ' . $bucketName);
error_log('AWS_REGION: ' . $region);
error_log('AWS_ACCESS_KEY_ID: ' . $accessKey);
error_log('AWS_SECRET_ACCESS_KEY: ' . $secretKey);

if (!$bucketName || !$region || !$accessKey || !$secretKey) {
    throw new Exception('Missing AWS configuration in aws_config.php file');
}

$s3Client = new S3Client([
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ],
]);
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

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $bucketName = AWS_BUCKET_NAME;
                $keyName = 'profile/faculty/' . $userId . '/' . basename($_FILES['profile_image']['name']);
                $filePath = $_FILES['profile_image']['tmp_name'];
    
                // Initialize S3 client
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region'  => AWS_REGION,
                    'credentials' => [
                        'key'    => AWS_ACCESS_KEY_ID,
                        'secret' => AWS_SECRET_ACCESS_KEY,
                    ],
                ]);
    
                try {
                    // Upload the image to S3
                    $result = $s3->putObject([
                        'Bucket' => $bucketName,
                        'Key'    => $keyName,
                        'SourceFile' => $filePath,
                        'ACL'    => 'public-read',
                    ]);
    
                    // Get the URL of the uploaded image
                    $profileImageUrl = $result['ObjectURL'];
    
                    // Save the URL to the database
                    $stmt = $conn->prepare("UPDATE faculty SET profile_image_url = ? WHERE email = ?");
                    $stmt->execute([$profileImageUrl, $userId]);
    
                    // Update the userData array for display
                    $userData['profile_image_url'] = $profileImageUrl;
                } catch (AwsException $e) {
                    echo "Error uploading image: " . $e->getMessage();
                }
            }

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
    
        // Fetch virtual classrooms for the course
        $virtualClassIds = !empty($course['virtual_class_id']) ? json_decode($course['virtual_class_id'], true) : [];
        $virtualClassrooms = [];
        if (!empty($virtualClassIds)) {
            $virtualClassroomModel = new VirtualClassroom($conn);
            $virtualClassrooms = $virtualClassroomModel->getById($virtualClassIds);

            // Add attendance_taken key to each virtual classroom
            foreach ($virtualClassrooms as &$classroom) {
                $classroom['attendance_taken'] = $virtualClassroomModel->getAttendance($classroom['classroom_id']) ? true : false;
            }
            // Sort virtual classrooms by start date in descending order
            usort($virtualClassrooms, function($a, $b) {
                return strtotime($b['start_time']) - strtotime($a['start_time']);
            });
        }

        // Fetch assignments for the course
        $assignmentIds = !empty($course['assignments']) ? json_decode($course['assignments'], true) : [];
        $assignments = [];
        if (!empty($assignmentIds)) {
            $assignments = Assignment::getByIds($conn, $assignmentIds);
        }

        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }

        usort($assignments, function($a, $b) {
            return strtotime($b['due_date']) - strtotime($a['due_date']);
        });
    
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

    public function downloadReport($assignmentId) {
        $conn = Database::getConnection();
        $submissions = Assignment::getSubmissions($conn, $assignmentId);
        $this->generateExcelReport($submissions);
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
            $sheet->setCellValue('B' . ($index + 2), $submission['name']);
            $sheet->setCellValue('C' . ($index + 2), $submission['email']);
            $sheet->setCellValue('D' . ($index + 2), $submission['grade'] ?? 'Not Graded');
        }
    
        $writer = new Xlsx($spreadsheet);
        $filename = 'assignment_report.xlsx';
    
        // Clear the output buffer
        if (ob_get_contents()) ob_end_clean();
    
        // Set headers to force download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
        // Save the file to the output
        $writer->save('php://output');
        exit;
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

        $course_id = json_decode($assignment['course_id'], true)[0];

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
            
                // Redirect to the view course page
                $hashedId = base64_encode($course_ids[0]);
                $hashedId = str_replace(['+', '/', '='], ['-', '_', ''], $hashedId);
                header('Location: /faculty/view_course/' . $hashedId);
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

    private function ensureUniversityIdInSession() {
        if (!isset($_SESSION['university_id'])) {
            $conn = Database::getConnection();
            $faculty_id = $_SESSION['faculty_id']; // Assuming faculty_id is stored in session

            // Fetch the university_id from the faculty table
            $sql = "SELECT university_id FROM faculty WHERE id = :faculty_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':faculty_id' => $faculty_id]);
            $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$faculty) {
                die("Faculty not found.");
            }

            $_SESSION['university_id'] = $faculty['university_id'];
        }
    }

    public function viewDiscussions() {
        $this->ensureUniversityIdInSession();
        $conn = Database::getConnection();
        $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session
        $discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);
        require 'views/faculty/discussion_forum.php';
    }

    public function createDiscussion() {
        $this->ensureUniversityIdInSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = Database::getConnection();
            $name = $_SESSION['name']; // Assuming email is stored in session
            $post = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Ensure 'msg' is retrieved correctly
            $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session

            if (empty($post)) {
                die("Post content cannot be empty.");
            }

            Discussion::addDiscussion($conn, $name, $post, $university_id);
            header('Location: /faculty/discussion_forum');
            exit();
        }
    }

    public function replyDiscussion() {
        $this->ensureUniversityIdInSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = Database::getConnection();
            $parent_post_id = filter_input(INPUT_POST, 'parent_post_id', FILTER_VALIDATE_INT);
            $name = $_SESSION['name']; // Assuming email is stored in session
            $post = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session

            if (empty($post)) {
                die("Reply content cannot be empty.");
            }

            Discussion::addDiscussion($conn, $name, $post, $university_id, $parent_post_id);
            header('Location: /faculty/discussion_forum');
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
    
