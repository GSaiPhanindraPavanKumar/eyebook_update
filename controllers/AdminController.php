<?php 
namespace Controllers;

use Models\Admin as AdminModel;
use Models\Student;
use Models\Spoc;
use Models\Course;
use Models\University;
use Models\Database;
use Models\Todo;
use Models\Mailer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Models\Discussion;
use Models\Meetings;
use PDO;
use ZipArchive;

use Exception;
use PDOException;

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
        $meeting_count = Meetings::getCount($conn);



        $spocs = Spoc::getAll($conn);
        $universities = University::getAll($conn);
        $courses = Course::getAll($conn);
        $todos = Todo::getAll($conn); 

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
            $spoc_pass = $_POST['spoc_password'];
            $spoc_password = password_hash($spoc_pass, PASSWORD_BCRYPT);
    
            if (University::existsByShortName($conn, $short_name)) {
                $message = "Duplicate entry for short name: " . $short_name;
                $message_type = "warning";
            } else if (Spoc::existsByEmail($conn, $spoc_email)) {
                $message = "Duplicate entry for email: " . $spoc_email;
                $message_type = "warning";
            } else {
                $university = new University($conn);
                $result = $university->addUniversity($conn, $long_name, $short_name, $location, $country, $spoc_name, $spoc_email, $spoc_phone, $spoc_password);
                $message = $result['message'];
                $message_type = $result['message_type'];
    
                // Validate email address before sending
                if (filter_var($spoc_email, FILTER_VALIDATE_EMAIL)) {
                    $mailer = new Mailer();
                    $subject = 'Welcome to EyeBook!';
                    $body = "Dear $spoc_name,<br><br>Your account has been created successfully as an SPOC for <b>$long_name<b>.<br><br>Username: $spoc_email <br>Password: $spoc_pass<br><br>Best Regards,<br>EyeBook Team";
                    $mailer->sendMail($spoc_email, $subject, $body);
                } else {
                    $message = "Invalid email address: " . $spoc_email;
                    $message_type = "error";
                }
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

            // University::update($conn, $id, $long_name, $short_name, $location, $country);

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
            $duplicateRecords = [];
        
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
                $file = $_FILES['file']['tmp_name'];
                $university_id = $_POST['university_id'];
        
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
        
                // Assuming the first row contains headers
                $headers = array_shift($rows);
        
                foreach ($rows as $row) {
                    $data = array_combine($headers, $row);
                    $result = Student::uploadStudents($conn, $data, $university_id);
                    if ($result['duplicate']) {
                        $duplicateRecords[] = $result['data'];
                    } else {
                        // Send account creation email
                        // $mailer = new Mailer();
                        // $subject = 'Welcome to EyeBook!';
                        // $body = "Dear {$data['name']},<br><br>Your account has been created successfully.<br><br>Username: {$data['email']}<br>Password: {$data['password']}<br><br>Best Regards,<br>EyeBook Team";
                        // $mailer->sendMail($data['email'], $subject, $body);
                    }
                }
        
                if (empty($duplicateRecords)) {
                    $message = "Students uploaded successfully.";
                    $message_type = "success";
                } else if (!empty($duplicateRecords)) {
                    $message = "Some records were not uploaded due to duplicates.";
                    $message_type = "warning";
                } else {
                    $message = "Failed to upload students.";
                    $message_type = "danger";
                }
            } 
        
            $universities = University::getAll($conn);
            require 'views/admin/uploadStudents.php';
        }
    
    public function addCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $is_paid =  0;
            $price =  0; // Set default value for price
            $message = Course::create($conn, $name, $description, $is_paid, $price);
        }
        require 'views/admin/add_courses.php';
    }
    
    public function manageCourse() {
        $conn = Database::getConnection();
        $courses = Course::getAllWithUniversity($conn);
        require 'views/admin/manage_courses.php';
    }

    public function courseView($course_id) {
        $conn = Database::getConnection();
    
        if ($course_id === null) {
            echo "Error: Course ID is not provided.";
            return;
        }
    
        $course = Course::getById($conn, $course_id);
    
        if (!$course) {
            echo "Error: Invalid Course ID.";
            return;
        }
    
        // Ensure course_materials is an array
        if (!is_array($course['course_materials'])) {
            $course['course_materials'] = [];
        }

        // Fetch universities details
        $universities = University::getAll($conn);
    
        require 'views/admin/view_course.php';
    }

    public function addUnit() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'] ?? null;
            $unit_name = $_POST['unit_name'] ?? null;
            $scorm_file = $_FILES['scorm_file'] ?? null;
    
            if (!$unit_name || !$scorm_file) {
                echo json_encode(['message' => 'Unit name and SCORM package file are required']);
                exit;
            }
    
            // Fetch the course
            $sql = "SELECT * FROM courses WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$course) {
                echo json_encode(['message' => 'Course not found']);
                exit;
            }
    
            // Decode the existing content or initialize an empty array
            $course_content = json_decode($course['course_book'], true);
            if (!is_array($course_content)) {
                $course_content = [];
            }
    
            // Create a directory for the SCORM package
            $scorm_dir = "uploads/course-$course_id/course_book" . time() . '-' . basename($scorm_file['name'], '.zip');
            mkdir($scorm_dir, 0777, true);
    
            // Unzip the SCORM package directly to the created directory
            $zip = new ZipArchive;
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $zip->extractTo($scorm_dir);
                $zip->close();
            } else {
                echo json_encode(['message' => 'Failed to unzip SCORM package']);
                exit;
            }
    
            // Verify that the index.html file exists
            $index_path = $scorm_dir . '/index.html';
            if (!file_exists($index_path)) {
                echo json_encode(['message' => 'index.html file not found']);
                exit;
            }
    
            // Add the new unit to the course content
            $new_unit = [
                'unitTitle' => $unit_name,
                'materials' => [['scormDir' => $scorm_dir, 'indexPath' => $index_path]]
            ];
            $course_content[] = $new_unit;
    
            // Update the course in the database
            $sql = "UPDATE courses SET course_book = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $content_json = json_encode($course_content);
            $stmt->execute([$content_json, $course_id]);
    
            echo json_encode(['message' => 'Unit added successfully with SCORM content', 'indexPath' => $index_path]);
    
            header("Location: /admin/view_course/$course_id");
            exit;
        }
    }

    public function assignCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $university_id = $_POST['university_id'];
            $result = Course::assignCourseToUniversity($conn, $course_id, $university_id);
            if ($result['message'] === 'Course assigned to university successfully') {
                header("Location: /admin/view_course/$course_id");
                exit();
            } else {
                echo json_encode(['message' => $result['message']]);
                exit;
            }
        }
    }
    
    public function createAssessment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $questions = json_decode($_POST['questions'], true);
            $deadline = $_POST['deadline'];

            try {
                $conn = Database::getConnection();
                $conn->createAssessment($title, $questions, $deadline);
                $success = "Assessment created successfully!";
                include 'views/success.php';
            } catch (Exception $e) {
                $error = "Error creating assessment: " . $e->getMessage();
                include 'views/error.php';
            }
        } else {
            include 'views/create_assessment.php';
        }
    }

    public function generateQuestions() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $topic = $_POST['topic'];
            $numQuestions = intval($_POST['numQuestions']);
            $marksPerQuestion = intval($_POST['marksPerQuestion']);

            try {
                $questions = $this->model->generateQuestionsUsingGemini($topic, $numQuestions, $marksPerQuestion);
                echo json_encode($questions);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }

    public function facultyForum($course_id) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $msg = $_POST['msg'];
            $username = $_SESSION['email'];
            Discussion::addDiscussion($conn, $username, $msg, $course_id);
        }
        $discussions = Discussion::getDiscussionsByCourse($conn, $course_id);
        require 'views/faculty/discussion_forum.php';
    }

    public function studentForum($course_id) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $msg = $_POST['msg'];
            $username = $_SESSION['username'];
            Discussion::addDiscussion($conn, $username, $msg, $course_id);
        }
        $discussions = Discussion::getDiscussionsByCourse($conn, $course_id);
        require 'views/faculty/discussion_forum.php';
    }
    public function manageStudents() {
        $conn = Database::getConnection();
        $students = Student::getAll($conn);
        require 'views/admin/manageStudents.php';
    }
    
    public function handleTodo() {
        $conn = Database::getConnection();
        $action = $_POST['action'];
    
        switch ($action) {
            case 'add':
                $title = $_POST['title'];
                Todo::add($conn, $title);
                break;
            case 'update':
                $id = $_POST['id'];
                $is_completed = $_POST['is_completed'] ? 1 : 0;
                Todo::update($conn, $id, $is_completed);
                break;
            case 'delete':
                $id = $_POST['id'];
                Todo::delete($conn, $id);
                break;
        }
    
        $todos = Todo::getAll($conn);
        echo json_encode($todos);
        exit();
    }

    private function checkAuth() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
    }

    public function viewStudentProfile($studentId) {
        $this->checkAuth();
        $conn = Database::getConnection();
        $student = Student::getById($conn, $studentId);
        if (!$student) {
            // Handle student not found
            header('Location: /admin/manageStudents');
            exit();
        }
        require 'views/admin/viewStudentProfile.php';
    }

    public function viewUniversity($university_id) {
        $conn = Database::getConnection();
    
        // Fetch university details
        $university = University::getById($conn, $university_id);
        $spoc = Spoc::getByUniversityId($conn, $university_id);
        $student_count = Student::getCountByUniversityId($conn, $university_id);
        $course_count = Course::getCountByUniversityId($conn, $university_id);
    
        require 'views/admin/view_university.php';
    }

}
?>
