<?php

namespace Controllers;

use Models\Spoc;
use Models\Database;
use Models\Faculty;
use Models\Student;
use Models\Course;
use Models\Assignment;
use Models\VirtualClassroom;
use Models\Lab;
use Models\Contest;
use Models\Discussion;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Models\Ticket;
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
class SpocController {
    public function dashboard() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
    
        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $faculty_count = $spocModel->getFacultyCount($university_id);
        $student_count = $spocModel->getStudentCount($university_id);
        $course_count = Course::getCountspocByUniversityId($conn, $university_id); // Fetch course count for the university
        $faculties = Faculty::getAllByUniversity($conn, $university_id); // Fetch all faculties for the university
        $courses = Course::getAllspocByUniversity($conn, $university_id); // Fetch all courses for the university

        // Fetch virtual class IDs and assignment IDs for the courses
        $virtualClassIds = Course::getspocVirtualClassIdsByCourseIds($conn, array_column($courses, 'id'));
        $assignmentIds = Course::getspocAssignmentIdsByCourseIds($conn, array_column($courses, 'id'));
        
        // Fetch virtual classes and assignments for the courses
        $virtualClassroomModel = new VirtualClassroom($conn);
        $assignmentModel = new Assignment();
    
        $virtualClasses = $virtualClassroomModel->getspocVirtualClassroomsByIds($virtualClassIds);
        $assignments = $assignmentModel->getspocAssignmentsByIds($conn, $assignmentIds);
    
        require 'views/spoc/dashboard.php';
    }

    public function manageStudents() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);

        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];

        $students = Student::getAllByUniversity($conn, $university_id); // Fetch all students for the university

        require 'views/spoc/manage_students.php';
    }

    public function manageCourses() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
    
        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $courses = Course::getAllspocByUniversity($conn, $university_id); // Fetch all courses for the university
    
        require 'views/spoc/manage_courses.php';
    }

    
    public function viewCourse($encoded_course_id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded_course_id));
        
        if (!$course_id) {
            die('Invalid course ID.');
        }
    
        $course = Course::getById($conn, $course_id);
        
        if (!$course) {
            die('Course not found.');
        }
    
        $username = $_SESSION['email'];
        $spocModel = new Spoc($conn);
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];
    
        $allFaculty = Faculty::getAllByUniversity($conn, $university_id); // Fetch all faculty of the university
        $allStudents = Student::getAllByUniversity($conn, $university_id); // Fetch all students of the university
    
        $assignedFaculty = array_filter($allFaculty, function($faculty) use ($course_id) {
            $assigned_courses = $faculty['assigned_courses'] ? json_decode($faculty['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });
    
        $assignedStudents = array_filter($allStudents, function($student) use ($course_id) {
            $assigned_courses = $student['assigned_courses'] ? json_decode($student['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });
    
        require 'views/spoc/view_course.php';
    }

    public function assignFaculty() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::assignCourse($conn, $faculty_id, $course_id);
            Course::assignFaculty($conn, $course_id, $faculty_id);
        }
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }
    
    public function assignStudents() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $student_ids = $_POST['student_ids'];
    
        foreach ($student_ids as $student_id) {
            Student::assignCourse($conn, $student_id, $course_id);
        }
        Course::assignStudents($conn, $course_id, $student_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }

    public function unassignFaculty() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::unassignCourse($conn, $faculty_id, $course_id);
        }
        Course::unassignFaculty($conn, $course_id, $faculty_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }
    
    public function unassignStudents() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $student_ids = $_POST['student_ids'] ?? [];
    
        if (!empty($student_ids)) {
            foreach ($student_ids as $student_id) {
                Student::unassignCourse($conn, $student_id, $course_id);
            }
            Course::unassignStudents($conn, $course_id, $student_ids);
        }
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header('Location: /spoc/view_course/' . $encoded_course_id);
        exit();
    }

    public function viewBook($hashedId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);

        if (!$course || empty($course['course_book'])) {
            $error_message = 'Course book content not found.';
            require 'views/spoc/book_view.php';
            exit;
        }

        // Ensure course_book is an array
        if (!is_array($course['course_book'])) {
            $course['course_book'] = json_decode($course['course_book'], true) ?? [];
        }

        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_book'][0]['scorm_url'];
        
        require 'views/spoc/book_view.php';
    }

    public function manageFaculties() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);

        $username = $_SESSION['email'];
        $userData = $spocModel->getUserData($username);
        $university_id = $userData['university_id'];

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $faculty = $this->fetchFaculty($conn, $university_id, $search, $limit, $offset);
        $totalFaculty = $this->countFaculty($conn, $university_id, $search);
        $totalPages = ceil($totalFaculty / $limit);

        require 'views/spoc/manage_faculty.php';
    }

    private function fetchFaculty($conn, $university_id, $search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search) LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%$search%";
        $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
        $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countFaculty($conn, $university_id, $search = '') {
        $sql = "SELECT COUNT(*) as count FROM faculty WHERE university_id = :university_id AND (name LIKE :search OR email LIKE :search)";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%$search%";
        $stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
        $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function userProfile() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
        $spoc = $spocModel->getUserProfile($conn);
        require 'views/spoc/profile.php';
    }

    public function profile() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);

        // Get the email from the session
        $email = $_SESSION['email'];
        $userId = $_SESSION['email'];

        // Fetch user data
        $userData = $spocModel->getUserData($email);

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $newEmail = $_POST['email'];
            $phone = $_POST['phone'];
            $profileImage = null;

            // Handle profile image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $bucketName = AWS_BUCKET_NAME;
                $keyName = 'profile/spoc/' . $userId . '/' . basename($_FILES['profile_image']['name']);
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
                    $stmt = $conn->prepare("UPDATE spocs SET profile_image_url = ? WHERE email = ?");
                    $stmt->execute([$profileImageUrl, $userId]);
    
                    // Update the userData array for display
                    $userData['profile_image_url'] = $profileImageUrl;
                } catch (AwsException $e) {
                    echo "Error uploading image: " . $e->getMessage();
                } catch (Exception $e) {
                    echo "Error uploading image: " . $e->getMessage();
                }
            }

            // Update user data
            $spocModel->updateUserData($email, $name, $newEmail, $phone);

            // Update session email if changed
            if ($email !== $newEmail) {
                $_SESSION['email'] = $newEmail;
            }

            header("Location: profile");
            exit;
        }

        require 'views/spoc/profile.php';
    }

    public function addFaculty() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
        $profile = $spocModel->getUserProfile($conn);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $section = $_POST['section'];
            $stream = $_POST['stream'];
            $department = $_POST['department'];
            $university_id = $profile['university_id'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
            $spocModel->addFaculty($conn, $name, $email, $phone, $section, $stream, $department, $university_id, $password);
            $message = "Faculty added successfully.";
            $message_type = "success";
            header('Location: /spoc/addFaculty');
            exit();
        }
        require 'views/spoc/addFaculty.php';
    }

    public function updatePassword() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        $conn = Database::getConnection();
        $spocModel = new Spoc($conn);
        $message = '';
        $message_type = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get SPOC data since session variables might not be set
            $userData = $spocModel->getUserData($_SESSION['email']);
            if (!$userData) {
                header('Location: /session-timeout');
                exit;
            }
            
            $spoc_id = $userData['id']; // Get ID from database instead of session
            $currentPassword = $_POST['currentPassword'];
            $newPassword = $_POST['newPassword'];
            $confirmPassword = $_POST['confirmPassword'];
            
            // Verify current password
            if (!password_verify($currentPassword, $userData['password'])) {
                $message = "Current password is incorrect";
                $message_type = "danger";
            }
            // Verify new password matches confirmation
            else if ($newPassword !== $confirmPassword) {
                $message = "New password and confirmation do not match";
                $message_type = "danger";
            }
            else {
                $new_password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
                Spoc::updatePassword($conn, $spoc_id, $new_password_hash);
                
                $message = "Password updated successfully";
                $message_type = "success";
            }
        }

        require 'views/spoc/updatePassword.php';
    }

    public static function getByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function iLab() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $iLabPath = $basePath . DIRECTORY_SEPARATOR . 'eyebook_update' . 
                    DIRECTORY_SEPARATOR . 'views' . 
                    DIRECTORY_SEPARATOR . 'student' . 
                    DIRECTORY_SEPARATOR . 'i-Lab' . 
                    DIRECTORY_SEPARATOR . 'index.html';
        
        error_log("SpocController: Attempting to access i-Lab at: " . $iLabPath);
        
        if (file_exists($iLabPath)) {
            require_once $iLabPath;
        } else {
            error_log("i-Lab file not found at: " . $iLabPath);
            header("HTTP/1.0 404 Not Found");
            echo "i-Lab page not found. Path: " . $iLabPath;
        }
    }
    public function viewLabs($hashedId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }

        $labs = Lab::getAllByCourseId($conn, $course_id);

        // Fetch submissions for each lab
        foreach ($labs as &$lab) {
            $lab['submissions'] = Lab::getSubmissions($conn, $lab['id']);
        }

        require 'views/spoc/view_labs.php';
    }

    public function viewLabDetail($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        if (!is_numeric($labId)) {
            die('Invalid lab ID');
        }

        $lab = Lab::getById($conn, $labId);
        $lab['submissions'] = Lab::getSubmissions($conn, $labId);

        require 'views/spoc/view_lab_detail.php';
    }
    public function downloadLabReport($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $submissions = Lab::getSubmissions($conn, $labId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Student Name');
        $sheet->setCellValue('B1', 'Runtime');
        $sheet->setCellValue('C1', 'Submission Date');

        foreach ($submissions as $index => $submission) {
            $sheet->setCellValue('A' . ($index + 2), $submission['student_name']);
            $sheet->setCellValue('B' . ($index + 2), $submission['runtime']);
            $sheet->setCellValue('C' . ($index + 2), (new \DateTime($submission['submission_date']))->format('Y-m-d H:i:s'));
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'lab_report_' . $labId . '.xlsx';

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
    public function manageContests() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $email = $_SESSION['email']; // Assuming the email is stored in the session

        // Fetch the spoc_id using the email
        $sql = "SELECT id, university_id FROM spocs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $spoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$spoc) {
            die('SPOC not found.');
        }

        $spocId = $spoc['id'];
        $spocUniversityId = $spoc['university_id'];

        // Fetch contests by university ID
        $contests = Contest::getByUniversityId($conn, $spocUniversityId);
        require 'views/spoc/manage_contests.php';
    }
    public function viewContest($contestId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $contest = Contest::getById($conn, $contestId);
        $questions = Contest::getQuestions($conn, $contestId);
        $leaderboard = Contest::getLeaderboard($conn, $contestId);
        require 'views/spoc/view_contest.php';
    }
    public function viewQuestion($questionId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $question = Contest::getQuestionById($conn, $questionId);
        require 'views/spoc/view_question.php';
    }
    private function ensureUniversityIdInSession() {
        if (!isset($_SESSION['university_id'])) {
            $conn = Database::getConnection();
            $email = $_SESSION['email']; // Assuming the email is stored in the session

            // Fetch the spoc_id using the email
            $sql = "SELECT university_id FROM spocs WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $spoc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$spoc) {
                die('SPOC not found.');
            }

            $_SESSION['university_id'] = $spoc['university_id'];
        }
    }

    public function viewAssignment($assignment_id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        $course_id = json_decode($assignment['course_id'], true)[0];

        require 'views/spoc/view_assignment.php';
    }

    public function viewDiscussions() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $this->ensureUniversityIdInSession();
        $conn = Database::getConnection();
        $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session
        $discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);
        require 'views/spoc/discussion_forum.php';
    }

    public function createDiscussion() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
            header('Location: /spoc/discussion_forum');
            exit();
        }
    }

    public function replyDiscussion() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
            header('Location: /spoc/discussion_forum');
            exit();
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            $conn = Database::getConnection();
            
            // Get SPOC data with university info
            $stmt = $conn->prepare("SELECT s.*, u.id as university_id, u.name as university_name 
                                   FROM spocs s 
                                   JOIN universities u ON s.university_id = u.id 
                                   WHERE s.email = :email");
            $stmt->execute(['email' => $email]);
            $spoc = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($spoc && password_verify($password, $spoc['password'])) {
                // Set all necessary session variables
                $_SESSION['email'] = $spoc['email'];
                $_SESSION['spoc_id'] = $spoc['id'];
                $_SESSION['university_id'] = $spoc['university_id'];
                $_SESSION['university_name'] = $spoc['university_name'];
                $_SESSION['name'] = $spoc['name'];
                $_SESSION['role'] = 'spoc';

                header('Location: /spoc/dashboard');
                exit;
            } else {
                $_SESSION['error'] = 'Invalid email or password';
                header('Location: /spoc/login');
                exit;
            }
        }
        require 'views/spoc/login.php';
    }

    public function tickets() {
        // if (!isset($_SESSION['email'])) {
        //     header('Location: /session-timeout');
        //     exit;
        // }

        $conn = Database::getConnection();
        
        // Get SPOC data since session variables might not be set
        $stmt = $conn->prepare("SELECT s.*, u.id as university_id 
                               FROM spocs s 
                               JOIN universities u ON s.university_id = u.id 
                               WHERE s.email = :email");
        $stmt->execute(['email' => $_SESSION['email']]);
        $spoc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$spoc) {
            header('Location: /login');
            exit;
        }
        
        // Set session variables if not already set
        $_SESSION['spoc_id'] = $spoc['id'];
        $_SESSION['university_id'] = $spoc['university_id'];
        
        // Get active and closed tickets for the SPOC's university
        $activeTickets = Ticket::getTicketsByUniversity($conn, $spoc['university_id'], 'active');
        $closedTickets = Ticket::getTicketsByUniversity($conn, $spoc['university_id'], 'closed');
        
        require 'views/spoc/tickets.php';
    }

    public function viewTicket($ticket_id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }

        $conn = Database::getConnection();
        $universityId = $_SESSION['university_id'];
        
        // Get ticket details
        $ticketData = Ticket::getTicketDetails($conn, $ticket_id);
        
        // Check if ticket exists and belongs to SPOC's university
        if (!$ticketData || !isset($ticketData['ticket']) || $ticketData['ticket']['university_id'] != $universityId) {
            $_SESSION['error'] = 'Ticket not found or unauthorized access';
            header('Location: /spoc/tickets');
            exit;
        }
        
        // Set variables for the view
        $ticket = $ticketData['ticket'];
        $replies = $ticketData['replies'];
        
        require 'views/spoc/view_ticket.php';
    }

    public function addTicketReply() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /spoc/tickets');
            exit;
        }
        
        $conn = Database::getConnection();
        $spocId = $_SESSION['spoc_id'];
        $universityId = $_SESSION['university_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        if (!$ticketId || empty($message)) {
            $_SESSION['error'] = 'Invalid input';
            header('Location: /spoc/view_ticket/' . $ticketId);
            exit;
        }
        
        // Verify ticket belongs to SPOC's university and is active
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        if ($ticket['ticket']['university_id'] != $universityId || $ticket['ticket']['status'] !== 'active') {
            header('Location: /spoc/tickets');
            exit;
        }
        
        $success = Ticket::addReply($conn, $ticketId, $spocId, 'spoc', $message);
        
        if ($success) {
            $_SESSION['success'] = 'Reply added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add reply';
        }
        
        header('Location: /spoc/view_ticket/' . $ticketId);
        exit;
    }

    public function closeTicket() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /spoc/tickets');
            exit;
        }
        
        $conn = Database::getConnection();
        $spocId = $_SESSION['spoc_id'];
        $universityId = $_SESSION['university_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        
        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
            exit;
        }
        
        // Verify ticket belongs to SPOC's university and is active
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        if (!$ticket || $ticket['ticket']['university_id'] != $universityId || $ticket['ticket']['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or ticket already closed']);
            exit;
        }
        
        $success = Ticket::closeTicket($conn, $ticketId, $spocId, 'spoc');
        echo json_encode(['success' => $success, 'redirect' => '/spoc/tickets']);
        exit;
    }
}