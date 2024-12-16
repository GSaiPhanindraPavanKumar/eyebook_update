<?php
namespace Controllers;

use Models\Course;
use Models\Database;
use Models\Student;
use Models\Assignment;
use Models\VirtualClassroom;
use Models\Discussion;
use PDO;
use PDOException;

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

class StudentController {
    public function viewCourse($hashedId) {
        $conn = Database::getConnection();
        $course_id = base64_decode($hashedId);
        if (!is_numeric($course_id)) {
            die('Invalid course ID');
        }
        $course = Course::getById($conn, $course_id);
    
        // Fetch the student data
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
        $assignments = Assignment::getByCourseId($conn, $course_id);

        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }

        usort($assignments, function($a, $b) {
            return strtotime($b['due_date']) - strtotime($a['due_date']);
        });
    
        require 'views/student/view_course.php';
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
    
        require 'views/student/book_view.php';
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
    
        require 'views/student/pdf_view.php';
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
    
        require 'views/student/pdf_view.php';
    }
    public function profile() {
        $conn = Database::getConnection();

        // Check if the user is not logged in
        if (!isset($_SESSION['email'])) {
            header("Location: /login");
            exit;
        }

        // Get the email from the session
        $email = $_SESSION['email'];

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT students.*, universities.long_name AS university_name 
                                FROM students 
                                JOIN universities ON students.university_id = universities.id 
                                WHERE students.email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Check if user data is available; if not, set default placeholder values
        $studentId = $userData['id'];
        $name = isset($userData['name']) ? htmlspecialchars($userData['name']) : "Danny McLoan";
        $profileImage = isset($userData['profile_image_url']) ? $userData['profile_image_url'] : null;
        $email = isset($userData['email']) ? htmlspecialchars($userData['email']) : "danny@example.com";
        $regd_no = isset($userData['regd_no']) ? htmlspecialchars($userData['regd_no']) : "123456";
        $section = isset($userData['section']) ? htmlspecialchars($userData['section']) : "A";
        $stream = isset($userData['stream']) ? htmlspecialchars($userData['stream']) : "Science";
        $year = isset($userData['year']) ? htmlspecialchars($userData['year']) : "1st Year";
        $dept = isset($userData['dept']) ? htmlspecialchars($userData['dept']) : "Journalism";
        $university = isset($userData['university_name']) ? htmlspecialchars($userData['university_name']) : "Unknown University";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
                $bucketName = AWS_BUCKET_NAME;
                $keyName = 'profile/student/' . $studentId . '/' . basename($_FILES['profile_image']['name']);
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
                    $stmt = $conn->prepare("UPDATE students SET profile_image_url = ? WHERE email = ?");
                    $stmt->execute([$profileImageUrl, $_SESSION['email']]);

                    // Update the userData array for display
                    $userData['profile_image_url'] = $profileImageUrl;
                } catch (AwsException $e) {
                    echo "Error uploading image: " . $e->getMessage();
                }
            } else {
                // Update user data with the submitted form data
                $name = htmlspecialchars($_POST['name']);
                $email = htmlspecialchars($_POST['email']);
                $regd_no = htmlspecialchars($_POST['regd_no']);
                $section = htmlspecialchars($_POST['section']);
                $stream = htmlspecialchars($_POST['stream']);
                $year = htmlspecialchars($_POST['year']);
                $dept = htmlspecialchars($_POST['dept']);

                // Save the updated data to the database
                $stmt = $conn->prepare("UPDATE students SET name=?, email=?, regd_no=?, section=?, stream=?, year=?, dept=? WHERE email=?");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $email);
                $stmt->bindParam(3, $regd_no);
                $stmt->bindParam(4, $section);
                $stmt->bindParam(5, $stream);
                $stmt->bindParam(6, $year);
                $stmt->bindParam(7, $dept);
                $stmt->bindParam(8, $email);
                $stmt->execute();

                // Update the userData array for display
                $userData['name'] = $name;
                $userData['email'] = $email;
                $userData['regd_no'] = $regd_no;
                $userData['section'] = $section;
                $userData['stream'] = $stream;
                $userData['year'] = $year;
                $userData['dept'] = $dept;
            }
        }

        require 'views/student/profile.php';
    }

    public function submitFeedback() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $student_id = $_SESSION['student_id'];
            $feedback = $_POST['feedback'];
    
            Course::saveFeedback($conn, $course_id, $student_id, $feedback);
    
            $_SESSION['message'] = 'Feedback submitted successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /student/view_course/' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)));
            exit();
        }
    }

    function getCoursesWithProgress($studentId) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT * FROM courses WHERE status = 'ongoing'");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT completed_books FROM students WHERE id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

        foreach ($courses as &$course) {
            $courseId = $course['id'];
            $courseBooks = !empty($course['course_book']) ? json_decode($course['course_book'], true) : [];
            $totalBooks = is_array($courseBooks) ? count($courseBooks) : 0;
            $completedBooksCount = is_array($completedBooks[$courseId] ?? []) ? count($completedBooks[$courseId] ?? []) : 0;
            $course['progress'] = ($totalBooks > 0) ? ($completedBooksCount / $totalBooks) * 100 : 0;
        }

        usort($courses, function($a, $b) {
            return $a['progress'] <=> $b['progress'];
        });

        return $courses;
    }
    public function updatePassword() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $student = Student::getByEmail($conn, $_SESSION['email']);
            $new_password = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    
            Student::updatePassword($conn, $student['id'], $new_password);
    
            $message = "Password updated successfully.";
            $message_type = "success";
        }
    
        require 'views/student/updatePassword.php';
    }

    
    public function manageAssignments() {
        $conn = Database::getConnection();
        $student_email = $_SESSION['email'];
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
        $assignments = Assignment::getAssignmentsByStudentId($conn, $student_email);

        // Ensure $assignments is an array
        if (!is_array($assignments)) {
            $assignments = [];
        }

        // Fetch submission status for each assignment
        foreach ($assignments as &$assignment) {
            $assignment['is_submitted'] = Assignment::isSubmitted($conn, $assignment['id'], $student_id);
        }

        require 'views/student/manage_assignments.php';
    }

    public function viewAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
    
        // Fetch existing submissions
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignment_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Handle the case where 'submissions' might be null or invalid
        $submissions = [];
        if (!empty($result['submissions'])) {
            $decoded = json_decode($result['submissions'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $submissions = $decoded;
            }
        }
    
        // Find the student's submission
        $student_submission = null;
        foreach ($submissions as $submission) {
            if (isset($submission['student_id']) && $submission['student_id'] == $student_id) {
                $student_submission = $submission;
                break;
            }
        }

        // Fetch the course_id from the assignment
        $course_id = json_decode($assignment['course_id'], true)[0];

    
        require 'views/student/view_assignment.php';
    }
    

    public function submitAssignment($assignment_id) {
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
                $file_content = file_get_contents($_FILES['submission_file']['tmp_name']);
                $submission_date = date('Y-m-d H:i:s');

                // Fetch existing submissions
                $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':assignment_id' => $assignment_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $submissions = $result ? json_decode($result['submissions'], true) : [];

                // Add or update the student's submission
                $updated = false;
                foreach ($submissions as &$submission) {
                    if ($submission['student_id'] == $student_id) {
                        $submission['file'] = base64_encode($file_content);
                        $submission['date_of_submit'] = $submission_date;
                        $updated = true;
                        break;
                    }
                }
                if (!$updated) {
                    $submissions[] = [
                        'student_id' => $student_id,
                        'file' => base64_encode($file_content),
                        'date_of_submit' => $submission_date
                    ];
                }

                // Update submissions in the database
                $sql = "UPDATE assignments SET submissions = :submissions WHERE id = :assignment_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':submissions' => json_encode($submissions),
                    ':assignment_id' => $assignment_id
                ]);

                header('Location: /student/view_assignment/' . $assignment_id);
                exit;
            } else {
                $error = "Failed to upload file.";
            }
        }

        $assignment = Assignment::getById($conn, $assignment_id);
        require 'views/student/view_assignment.php';
    }

    public function deleteSubmission($assignment_id) {
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

        // Fetch existing submissions
        $sql = "SELECT submissions FROM assignments WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':assignment_id' => $assignment_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = $result ? json_decode($result['submissions'], true) : [];

        // Remove the student's submission
        $submissions = array_filter($submissions, function($submission) use ($student_id) {
            return $submission['student_id'] != $student_id;
        });

        // Update submissions in the database
        $sql = "UPDATE assignments SET submissions = :submissions WHERE id = :assignment_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':submissions' => json_encode(array_values($submissions)),
            ':assignment_id' => $assignment_id
        ]);

        header('Location: /student/view_assignment/' . $assignment_id);
        exit;
    }
    
    public function viewGrades() {
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id'];
        $grades = Assignment::getGradesByStudentId($conn, $student_id);
        require 'views/student/view_grades.php';
    }

    private function ensureUniversityIdInSession() {
        if (!isset($_SESSION['university_id'])) {
            $conn = Database::getConnection();
            $student_id = $_SESSION['student_id']; // Assuming faculty_id is stored in session

            // Fetch the university_id from the faculty table
            $sql = "SELECT university_id FROM students WHERE id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                die("student not found.");
            }

            $_SESSION['university_id'] = $student['university_id'];
        }
    }
    
    public function viewDiscussions() {
        $this->ensureUniversityIdInSession();
        $conn = Database::getConnection();
        $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session
        $discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);
        require 'views/student/discussion_forum.php';
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
            header('Location: /student/discussion_forum');
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
            header('Location: /student/discussion_forum');
            exit();
        }
    }

    
}
?>