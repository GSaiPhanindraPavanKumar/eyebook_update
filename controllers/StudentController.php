<?php
namespace Controllers;

use Models\Course;
use Models\Database;
use Models\Student;
use Models\Assignment;
use Models\VirtualClassroom;
use Models\Discussion;
use Models\feedback;
use Models\Lab;
use Models\Notification;
use Models\Contest;
use Models\Ticket;
use Models\PublicCourse;
use Models\PublicLab;
use Models\PublicFeedback;
use Models\Assessment;
use PDO;
use Razorpay\Api\Api;
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $courseId = base64_decode($hashedId);
        $studentId = $_SESSION['student_id'];

        if (!is_numeric($courseId)) {
            die('Invalid course ID');
        }

        // Check if student is assigned to this course
        if (!Course::isStudentAssigned($conn, $studentId, $courseId)) {
            $_SESSION['error'] = "You are not assigned to this course.";
            header('Location: /student/my_courses');
            exit;
        }
        $course = Course::getById($conn, $courseId);
    
        // Fetch the student data
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
        $assignments = Assignment::getByCourseId($conn, $courseId);

        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }

        usort($assignments, function($a, $b) {
            return strtotime($b['due_date']) - strtotime($a['due_date']);
        });
    
        require 'views/student/view_course.php';
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
            require 'views/student/book_view.php';
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

    public function viewECContent($hashedId) {
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
    
        if (!$course || empty($course['EC_content'])) {
            echo 'EC content not found.';
            exit;
        }
    
        // Ensure EC_content is an array
        if (!is_array($course['EC_content'])) {
            $course['EC_content'] = json_decode($course['EC_content'], true) ?? [];
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['EC_content'][0]['indexPath'];
    
        require 'views/student/book_view.php';
    }

    public function viewMaterial($hashedId) {
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
    
        if (!$course || empty($course['course_materials'])) {
            echo 'Course materials not found.';
            exit;
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['course_materials'][0]['materials'][0]['indexPath'];
    
        require 'views/student/pdf_view.php';
    }
    
    public function viewCoursePlan($hashedId) {
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
    
        if (!$course || empty($course['course_plan'])) {
            echo 'Course plan not found.';
            exit;
        }
    
        // Get the index_path for the course plan
        $index_path = $course['course_plan']['url'];
    
        require 'views/student/pdf_view.php';
    }
    public function profile() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();

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

    // public function submitFeedback() {
    //     $conn = Database::getConnection();
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         $course_id = $_POST['course_id'];
    //         $student_id = $_SESSION['student_id'];
    //         $feedback = $_POST['feedback'];
    
    //         Course::saveFeedback($conn, $course_id, $student_id, $feedback);
    
    //         $_SESSION['message'] = 'Feedback submitted successfully.';
    //         $_SESSION['message_type'] = 'success';
    
    //         header('Location: /student/view_course/' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)));
    //         exit();
    //     }
    // }

    function getCoursesWithProgress($studentId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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

    public function markAsCompleted() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        $courseId = $_POST['course_id'];
        $indexPath = $_POST['indexPath'];

        // Fetch the student's completed books
        $stmt = $conn->prepare("SELECT completed_books FROM students WHERE id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $completedBooks = !empty($student['completed_books']) ? json_decode($student['completed_books'], true) : [];

        // Ensure the course ID exists in the completed books array
        if (!isset($completedBooks[$courseId])) {
            $completedBooks[$courseId] = [];
        }

        // Add the index path to the completed books array if not already present
        if (!in_array($indexPath, $completedBooks[$courseId])) {
            $completedBooks[$courseId][] = $indexPath;
        }

        // Update the student's completed books in the database
        $stmt = $conn->prepare("UPDATE students SET completed_books = :completed_books WHERE id = :student_id");
        $stmt->execute([
            'completed_books' => json_encode($completedBooks),
            'student_id' => $studentId
        ]);

        echo json_encode(['status' => 'success']);
    }

    public function viewCourseBook() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $index_path = $_GET['index_path'] ?? '';
    
        if (empty($index_path)) {
            echo 'Course book content not found.';
            exit;
        }
    
        require 'views/student/book_view.php';
    }

    public function managePublicCourses() {
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        // Fetch enrolled courses
        $enrolledCourses = PublicCourse::getEnrolledCourses($conn, $studentId);

        // Fetch featured courses
        $featuredCourses = PublicCourse::getFeaturedCourses($conn);

        require 'views/student/manage_public_courses.php';
    }

    public function enrollInCourse() {
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courseId = $_POST['course_id'];
            PublicCourse::enrollStudent($conn, $courseId, $studentId);

            header('Location: /student/manage_public_courses');
            exit();
        }
    }

    public function viewPublicCourse($hashedId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        $conn = Database::getConnection();
        $student = Student::getByEmail($conn, $_SESSION['email']);
        $studentId = $_SESSION['student_id'];
    
        $courseId = base64_decode($hashedId);
        if (!is_numeric($courseId)) {
            die('Invalid course ID');
        }
        
        // Check enrollment status
        if (!PublicCourse::isStudentEnrolled($conn, $studentId, $courseId)) {
            $_SESSION['error'] = "You must be enrolled in this course to view its content.";
            header('Location: /student/manage_public_courses');
            exit;
        }
    
        $course = PublicCourse::getById($conn, $courseId);
    
        // Fetch assignments for the course
        $assignments = Assignment::getByCourseId($conn, $courseId);
    
        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }
    
        usort($assignments, function($a, $b) {
            return strtotime($b['due_date']) - strtotime($a['due_date']);
        });
    
        require 'views/student/view_public_course.php';
    }

    public function payForCourse() {
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courseId = $_POST['course_id'];
            $amount = $_POST['amount'];

            // Create Razorpay order
            $api = new Api('rzp_test_CVbypqu6YtbzvT', 'Qi0jllHSrENWlNxGl0QXbJC5');
            $order = $api->order->create([
                'receipt' => 'order_rcptid_' . $courseId,
                'amount' => $amount * 100, // Amount in paise
                'currency' => 'INR'
            ]);

            // Store order details in session
            $_SESSION['razorpay_order_id'] = $order['id'];
            $_SESSION['course_id'] = $courseId;
            $_SESSION['amount'] = $amount;

            // Ensure student_name and email are set
            if (!isset($_SESSION['student_name']) || !isset($_SESSION['email'])) {
                // Fetch student details from the database
                $stmt = $conn->prepare("SELECT name, email FROM students WHERE id = ?");
                $stmt->execute([$studentId]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['student_name'] = $student['name'];
                $_SESSION['email'] = $student['email'];
            }

            // Redirect to Razorpay payment page
            header('Location: /student/razorpay_payment');
            exit();
        }
    }

    public function razorpayPayment() {
        require 'views/student/razorpay_payment.php';
    }

    public function razorpayCallback() {
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        $api = new Api('rzp_test_CVbypqu6YtbzvT', 'Qi0jllHSrENWlNxGl0QXbJC5');

        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];

        try {
            $api->utility->verifyPaymentSignature($attributes);

            // Payment successful, enroll student in course
            $courseId = $_SESSION['course_id'];
            $amount = $_SESSION['amount'];
            PublicCourse::enrollStudent($conn, $courseId, $studentId);

            // Store transaction details
            $stmt = $conn->prepare("INSERT INTO transactions (student_id, course_id, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$studentId, $courseId, $_POST['razorpay_payment_id'], $amount, 'success']);

            // Clear session variables
            unset($_SESSION['razorpay_order_id']);
            unset($_SESSION['course_id']);
            unset($_SESSION['amount']);

            header('Location: /student/manage_public_courses');
            exit();
        } catch (\Exception $e) {
            // Payment failed, store transaction details
            $stmt = $conn->prepare("INSERT INTO transactions (student_id, course_id, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$studentId, $_SESSION['course_id'], $_POST['razorpay_payment_id'], $_SESSION['amount'], 'failed']);

            // Clear session variables
            unset($_SESSION['razorpay_order_id']);
            unset($_SESSION['course_id']);
            unset($_SESSION['amount']);

            echo 'Payment failed: ' . $e->getMessage();
        }
    }

    public function updatePassword() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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

    public function askguru(){
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        require 'views/student/askguru.php';
    }

    public function submitFeedback() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $student_id = $_SESSION['student_id'];
            $feedback = [
                'depth_of_coverage' => $_POST['depth_of_coverage'],
                'emphasis_on_fundamentals' => $_POST['emphasis_on_fundamentals'],
                'coverage_of_modern_topics' => $_POST['coverage_of_modern_topics'],
                'overall_rating' => $_POST['overall_rating'],
                'benefits' => $_POST['benefits'],
                'instructor_assistance' => $_POST['instructor_assistance'],
                'instructor_feedback' => $_POST['instructor_feedback'],
                'motivation' => $_POST['motivation'],
                'sme_help' => $_POST['sme_help'],
                'overall_very_good' => $_POST['overall_very_good']
            ];

            Feedback::saveFeedback($conn, $course_id, $student_id, $feedback);

            $_SESSION['feedback_submitted'] = true;

            header('Location: /student/view_course/' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)));
            exit();
        }
    }
    
    public function submitPublicFeedback() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $student_id = $_SESSION['student_id'];
            $feedback = [
                'depth_of_coverage' => $_POST['depth_of_coverage'],
                'emphasis_on_fundamentals' => $_POST['emphasis_on_fundamentals'],
                'coverage_of_modern_topics' => $_POST['coverage_of_modern_topics'],
                'overall_rating' => $_POST['overall_rating'],
                'benefits' => $_POST['benefits'],
                'instructor_assistance' => $_POST['instructor_assistance'],
                'instructor_feedback' => $_POST['instructor_feedback'],
                'motivation' => $_POST['motivation'],
                'sme_help' => $_POST['sme_help'],
                'overall_very_good' => $_POST['overall_very_good']
            ];

            PublicFeedback::saveFeedback($conn, $course_id, $student_id, $feedback);

            $_SESSION['feedback_submitted'] = true;

            header('Location: /student/view_public_course/' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id)));
            exit();
        }
    }

    public function submitAssignment($assignment_id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
                // Upload file to S3
                $s3Client = new S3Client([
                    'region' => AWS_REGION,
                    'version' => 'latest',
                    'credentials' => [
                        'key' => AWS_ACCESS_KEY_ID,
                        'secret' => AWS_SECRET_ACCESS_KEY,
                    ],
                ]);
    
                $bucketName = AWS_BUCKET_NAME;
                $key = 'assignments/submissions/' . $student_id . '/' . basename($_FILES['submission_file']['name']);
                $filePath = $_FILES['submission_file']['tmp_name'];
    
                // Detect file type and set metadata
                $fileType = mime_content_type($filePath);
                $metadata = [
                    'ContentType' => $fileType,
                ];
    
                try {
                    $result = $s3Client->putObject([
                        'Bucket' => $bucketName,
                        'Key' => $key,
                        'SourceFile' => $filePath,
                        'ACL' => 'public-read',
                        'ContentType' => $fileType,
                        'Metadata' => $metadata,
                    ]);
                    $fileUrl = $result['ObjectURL'];
                } catch (AwsException $e) {
                    error_log($e->getMessage());
                    $error = "Failed to upload file to S3.";
                    $assignment = Assignment::getById($conn, $assignment_id);
                    require 'views/student/view_assignment.php';
                    return;
                }
    
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
                        $submission['file'] = $fileUrl;
                        $submission['date_of_submit'] = $submission_date;
                        $updated = true;
                        break;
                    }
                }
                if (!$updated) {
                    $submissions[] = [
                        'student_id' => $student_id,
                        'file' => $fileUrl,
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
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
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $this->ensureUniversityIdInSession();
        $conn = Database::getConnection();
        $university_id = $_SESSION['university_id']; // Assuming university_id is stored in session
        $discussions = Discussion::getDiscussionsByUniversity($conn, $university_id);
        require 'views/student/discussion_forum.php';
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
            header('Location: /session-timeout');
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
            header('Location: /session-timeout');
            exit();
        }
    }

    public function iLab() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $iLabPath = $basePath . DIRECTORY_SEPARATOR . 'eyebook_update' . DIRECTORY_SEPARATOR . 'i-Lab' . DIRECTORY_SEPARATOR . 'index.html';
        
        // Log the path for debugging
        error_log("Attempting to access i-Lab at: " . $iLabPath);
        
        if (file_exists($iLabPath)) {
            require_once $iLabPath;
        } else {
            error_log("i-Lab file not found at: " . $iLabPath);
            header("HTTP/1.0 404 Not Found");
            echo "i-Lab page not found. Looking for file at: " . $iLabPath;
        }
    }
    
    public function labView($courseId = null) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        if (!$courseId) {
            $courseId = $_GET['id'] ?? null;
        }
        
        if (!$courseId) {
            header('Location: /student/my_courses');
            exit;
        }
    
        $conn = Database::getConnection();
        
        // Fetch lab data
        $sql = "SELECT l.*, c.name as course_name 
                FROM labs l 
                JOIN courses c ON l.course_id = c.id 
                WHERE l.course_id = :course_id AND l.status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        require 'views/student/lab_view.php';
    }
    public function viewLab($hashedId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $courseId = base64_decode($hashedId);
        $student_id = $_SESSION['student_id'];

        $labs = Lab::getAllByCourseId($conn, $courseId);

        // Fetch submissions for each lab and determine the status
        foreach ($labs as &$lab) {
            $submissions = Lab::getSubmissionsByStudent($conn, $lab['id'], $student_id);
            $lab['status'] = !empty($submissions);
        }

        require 'views/student/view_lab.php';
    }

    public function viewPublicLab($hashedId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $courseId = base64_decode($hashedId);
        $student_id = $_SESSION['student_id'];

        $labs = PublicLab::getAllByCourseId($conn, $courseId);

        // Fetch submissions for each lab and determine the status
        foreach ($labs as &$lab) {
            $submissions = PublicLab::getSubmissionsByStudent($conn, $lab['id'], $student_id);
            $lab['status'] = !empty($submissions);
        }

        require 'views/student/view_public_lab.php';
    }
    public function viewLabDetail($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $lab = Lab::getByIds($conn, [$labId])[0];
        require 'views/student/view_lab_detail.php';
    }

    public function viewPublicLabDetail($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $lab = Lab::getByIds($conn, [$labId])[0];
        require 'views/student/view_public_lab_detail.php';
    }

    public function submitLab($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'];
            $submissionDate = date('Y-m-d H:i:s');

            // Fetch existing submissions
            $sql = "SELECT submissions FROM labs WHERE id = :lab_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':lab_id' => $labId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $submissions = $result ? json_decode($result['submissions'], true) : [];

            // Add or update the student's submission
            $updated = false;
            foreach ($submissions as &$submission) {
                if ($submission['student_id'] == $studentId) {
                    $submission['code'] = base64_encode($code);
                    $submission['date_of_submit'] = $submissionDate;
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $submissions[] = [
                    'student_id' => $studentId,
                    'code' => base64_encode($code),
                    'date_of_submit' => $submissionDate
                ];
            }

            // Update submissions in the database
            $sql = "UPDATE labs SET submissions = :submissions WHERE id = :lab_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':submissions' => json_encode($submissions),
                ':lab_id' => $labId
            ]);

            header('Location: /student/view_lab_detail/' . $labId);
            exit;
        }
    }

    public function submitPublicLab($labId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming student_id is stored in session

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $_POST['code'];
            $submissionDate = date('Y-m-d H:i:s');

            // Fetch existing submissions
            $sql = "SELECT submissions FROM public_labs WHERE id = :lab_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':lab_id' => $labId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $submissions = $result ? json_decode($result['submissions'], true) : [];

            // Add or update the student's submission
            $updated = false;
            foreach ($submissions as &$submission) {
                if ($submission['student_id'] == $studentId) {
                    $submission['code'] = base64_encode($code);
                    $submission['date_of_submit'] = $submissionDate;
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $submissions[] = [
                    'student_id' => $studentId,
                    'code' => base64_encode($code),
                    'date_of_submit' => $submissionDate
                ];
            }

            // Update submissions in the database
            $sql = "UPDATE public_labs SET submissions = :submissions WHERE id = :lab_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':submissions' => json_encode($submissions),
                ':lab_id' => $labId
            ]);

            header('Location: /student/view_lab_detail/' . $labId);
            exit;
        }
    }
    public function updateLabSubmission() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $lab_id = $data['lab_id'];
        $student_id = $data['student_id'];
        $submission_date = $data['submission_date'];
        $runtime = $data['runtime'];
    
        // Log incoming data for debugging
        error_log("Lab ID: $lab_id, Student ID: $student_id, Submission Date: $submission_date, Runtime: $runtime");
    
        // Fetch existing submissions
        $sql = "SELECT submissions FROM labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = $result ? json_decode($result['submissions'], true) : [];
    
        // Log existing submissions for debugging
        error_log("Existing Submissions: " . json_encode($submissions));
    
        // Add or update the student's submission with runtime
        $updated = false;
        foreach ($submissions as &$submission) {
            if ($submission['student_id'] == $student_id) {
                $submission['runtime'] = $runtime;
                $submission['submission_date'] = $submission_date;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $submissions[] = [
                'student_id' => $student_id,
                'runtime' => $runtime,
                'submission_date' => $submission_date
            ];
        }
    
        // Log updated submissions for debugging
        error_log("Updated Submissions: " . json_encode($submissions));
    
        // Update submissions in the database
        $sql = "UPDATE labs SET submissions = :submissions WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':submissions' => json_encode($submissions),
            ':lab_id' => $lab_id
        ]);
    
        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            error_log("Failed to update submissions for Lab ID: $lab_id");
            echo json_encode(['status' => 'error', 'message' => 'Failed to update submissions']);
        }
    }

    public function updatePublicLabSubmission() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $lab_id = $data['lab_id'];
        $student_id = $data['student_id'];
        $submission_date = $data['submission_date'];
        $runtime = $data['runtime'];
    
        // Log incoming data for debugging
        error_log("Lab ID: $lab_id, Student ID: $student_id, Submission Date: $submission_date, Runtime: $runtime");
    
        // Fetch existing submissions
        $sql = "SELECT submissions FROM public_labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = $result ? json_decode($result['submissions'], true) : [];
    
        // Log existing submissions for debugging
        error_log("Existing Submissions: " . json_encode($submissions));
    
        // Add or update the student's submission with runtime
        $updated = false;
        foreach ($submissions as &$submission) {
            if ($submission['student_id'] == $student_id) {
                $submission['runtime'] = $runtime;
                $submission['submission_date'] = $submission_date;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $submissions[] = [
                'student_id' => $student_id,
                'runtime' => $runtime,
                'submission_date' => $submission_date
            ];
        }
    
        // Log updated submissions for debugging
        error_log("Updated Submissions: " . json_encode($submissions));
    
        // Update submissions in the database
        $sql = "UPDATE public_labs SET submissions = :submissions WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':submissions' => json_encode($submissions),
            ':lab_id' => $lab_id
        ]);
    
        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            error_log("Failed to update submissions for Lab ID: $lab_id");
            echo json_encode(['status' => 'error', 'message' => 'Failed to update submissions']);
        }
    }
    public function manageContests() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id']; // Assuming the student's ID is stored in the session

        // Fetch the student's university ID
        $sql = "SELECT university_id FROM students WHERE id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $studentUniversityId = $student['university_id'];

        // Fetch contests by university ID
        $contests = Contest::getByUniversityId($conn, $studentUniversityId);
        require 'views/student/manage_contests.php';
    }

    public function viewContest($contestId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $contest = Contest::getById($conn, $contestId);
        require 'views/student/view_contest.php';
    }
    public function viewQuestion($questionId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        $conn = Database::getConnection();
        $data = Contest::getQuestionWithContest($conn, $questionId);
        
        if (!$data) {
            header('Location: /student/manage_contests');
            exit;
        }
        
        $question = $data['question'];
        $contest = $data['contest'];
        
        require 'views/student/view_question.php';
    }
    public function updateQuestionSubmission() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        $questionId = $data['question_id'];
        $studentId = $data['student_id'];
        $submissionDate = $data['submission_date'];
        $runtime = $data['runtime'];

        // Fetch the existing submissions
        $question = Contest::getQuestionById($conn, $questionId);
        $submissions = !empty($question['submissions']) ? json_decode($question['submissions'], true) : [];

        // Update or add the student's submission
        $updated = false;
        foreach ($submissions as &$submission) {
            if ($submission['student_id'] == $studentId) {
                $submission['submission_date'] = $submissionDate;
                $submission['runtime'] = $runtime;
                $submission['status'] = 'passed';
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $submissions[] = [
                'student_id' => $studentId,
                'submission_date' => $submissionDate,
                'runtime' => $runtime,
                'status' => 'passed'
            ];
        }

        // Update the submissions JSON column
        $sql = "UPDATE contest_questions SET submissions = :submissions WHERE id = :question_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':submissions' => json_encode($submissions),
            ':question_id' => $questionId
        ]);

        echo json_encode(['status' => 'success']);
    }

    public function tickets() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $this->ensureUniversityIdInSession();
        
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        // Get active and closed tickets
        $activeTickets = Ticket::getTicketsByStudent($conn, $studentId, 'active');
        $closedTickets = Ticket::getTicketsByStudent($conn, $studentId, 'closed');
        
        require 'views/student/tickets.php';
    }

    public function createTicket() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }
        
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        $universityId = $_SESSION['university_id'];
        
        // Use htmlspecialchars instead of FILTER_SANITIZE_STRING
        $subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        if (empty($subject) || empty($description)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Subject and description are required']);
            exit;
        }
        
        $success = Ticket::create($conn, $studentId, $universityId, $subject, $description);
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    public function getTicketDetails($ticketId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        // Get ticket details
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        
        // Check if ticket exists
        if (!$ticket) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Ticket not found']);
            exit;
        }
        
        // Verify ticket belongs to student
        if ($ticket['ticket']['student_id'] != $studentId) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode($ticket);
        exit;
    }

    public function addTicketReply() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /student/tickets');
            exit;
        }
        
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        if (!$ticketId || empty($message)) {
            $_SESSION['error'] = 'Invalid input';
            header('Location: /student/view_ticket/' . $ticketId);
            exit;
        }
        
        // Verify ticket belongs to student and is active
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        if ($ticket['ticket']['student_id'] != $studentId || $ticket['ticket']['status'] !== 'active') {
            header('Location: /student/tickets');
            exit;
        }
        
        $success = Ticket::addReply($conn, $ticketId, $studentId, 'student', $message);
        
        if ($success) {
            $_SESSION['success'] = 'Reply added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add reply';
        }
        
        header('Location: /student/view_ticket/' . $ticketId);
        exit;
    }

    public function viewTicket($ticket_id) {
        if (!isset($_SESSION['email'])) {
            error_log('Session email not set');
            header('Location: /session-timeout');
            exit;
        }

        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        error_log('Viewing ticket: ' . $ticket_id . ' for student: ' . $studentId);
        
        try {
            // Get ticket details
            $ticketData = Ticket::getTicketDetails($conn, $ticket_id);
            
            error_log('Ticket data: ' . print_r($ticketData, true));
            
            // Check if ticket exists
            if (!$ticketData || !isset($ticketData['ticket'])) {
                error_log('Ticket not found or invalid structure');
                $_SESSION['error'] = 'Ticket not found';
                header('Location: /student/tickets');
                exit;
            }
            
            // Verify ticket belongs to student
            if ($ticketData['ticket']['student_id'] != $studentId) {
                error_log('Unauthorized access - Ticket student_id: ' . $ticketData['ticket']['student_id'] . ' vs Session student_id: ' . $studentId);
                $_SESSION['error'] = 'Unauthorized access';
                header('Location: /student/tickets');
                exit;
            }
            
            // Set variables for the view
            $ticket = $ticketData['ticket'];
            $replies = $ticketData['replies'];
            
            require 'views/student/view_ticket.php';
        } catch (Exception $e) {
            error_log('Error in viewTicket: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while retrieving the ticket';
            header('Location: /student/tickets');
            exit;
        }
    }

    public function closeTicket() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /student/tickets');
            exit;
        }
        
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        
        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
            exit;
        }
        
        // Verify ticket belongs to student and is active
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        if (!$ticket || $ticket['ticket']['student_id'] != $studentId || $ticket['ticket']['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or ticket already closed']);
            exit;
        }
        
        $success = Ticket::closeTicket($conn, $ticketId, $studentId, 'student');
        echo json_encode(['success' => $success]);
        exit;
    }

    public function xpStatus() {
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        // Fetch student data including XP and level
        $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        require 'views/student/xp_status.php';
    }

    public function startContest($contestId) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        
        $conn = Database::getConnection();
        $student_id = $_SESSION['student_id'];
        
        // Record contest start time
        Contest::recordContestStart($conn, $contestId, $student_id);
        
        header('Location: /student/view_contest/' . $contestId);
        exit();
    }

    public function checkIn() {
        if (!isset($_SESSION['email'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        $success = Student::updateCheckIn($conn, $studentId);
        $status = Student::getCheckInStatus($conn, $studentId);
        $history = Student::getCheckInHistory($conn, $studentId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $status,
            'history' => $history
        ]);
        exit;
    }

    public function checkInHistory() {
        if (!isset($_SESSION['email'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
        
        // Get check-in status and history
        $status = Student::getCheckInStatus($conn, $studentId);
        $history = Student::getCheckInHistory($conn, $studentId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $status,
            'history' => $history
        ]);
        exit;
    }

    public function dashboard() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
        $conn = Database::getConnection();
        $email = $_SESSION['email'];
        $studentId = $_SESSION['student_id'];

        // Fetch user data and metrics
        $userData = Student::getUserDataByEmail($conn, $email);
        
        // Initialize metrics with default values
        $metrics = [
            'total_courses' => 0,
            'completed_projects' => 0
        ];
        
        // Get actual metrics if available
        $studentMetrics = Student::getStudentMetrics($conn, $studentId);
        if ($studentMetrics) {
            $metrics = array_merge($metrics, $studentMetrics);
        }

        // Ensure university_short_name is set
        $universityShortName = isset($userData['university_short_name']) ? htmlspecialchars($userData['university_short_name']) : '';

        // Fetch today's classes
        $todaysClasses = $this->getTodaysClasses($userData['id']);

        // Fetch courses and progress
        $courses = Student::getCoursesWithProgress($conn, $studentId);
        $ongoingCourses = array_filter($courses, function($course) {
            return $course['status'] === 'ongoing';
        });

        // Calculate overall progress
        $overallProgress = 0;
        $courseCount = count($ongoingCourses);
        if ($courseCount > 0) {
            $totalProgress = array_sum(array_column($ongoingCourses, 'progress'));
            $overallProgress = $totalProgress / $courseCount;
        }

        // Get least progress courses
        $leastProgressCourses = array_filter($ongoingCourses, function($course) {
            return !empty($course['course_book']) && $course['progress'] < 100;
        });
        $leastProgressCourses = array_slice($leastProgressCourses, 0, 5);

        // Fetch all virtual classes for the calendar
        $virtualClasses = $this->getAllVirtualClasses($studentId);

        // Fetch assignments for the assigned courses
        $assignments = Assignment::getAssignmentsByStudentId($conn, $email);

        // Filter assignments to exclude those with passed due dates
        $upcomingAssignments = array_filter($assignments, function($assignment) {
            return strtotime($assignment['due_date']) >= time();
        });

        // Filter virtual classes for the upcoming week
        $upcomingClasses = array_filter($virtualClasses, function($class) {
            $startTime = strtotime($class['start_time']);
            $now = time();
            $oneWeekLater = strtotime('+1 week', $now);
            return $startTime >= $now && $startTime <= $oneWeekLater;
        });

        // Fetch contests
        $student = Student::getById($conn, $studentId);
        $universityId = $student['university_id'];
        $contests = Contest::getByUniversityId($conn, $universityId);

        // Curated list of quotes
        $quotes = [
            // ... existing quotes array ...
        ];

        // Select a random quote based on the current date
        $thoughtOfTheDay = $quotes[date('z') % count($quotes)];

        // Pass all variables to the view
        require 'views/student/dashboard.php';
    }

    private function getTodaysClasses($studentId) {
        $conn = Database::getConnection();
        // ... rest of the getTodaysClasses implementation ...
    }

    private function getAllVirtualClasses($studentId) {
        $conn = Database::getConnection();
        // ... rest of the getAllVirtualClasses implementation ...
    }
    public function manageAssessments() {
        $conn = Database::getConnection();
        $assessments = Assessment::getAll($conn);
        require 'views/student/manage_assessments.php';
    }
    public function viewAssessments() {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
    
        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];
    
        // Fetch assessments
        $assessments = Assessment::getAll($conn);
    
        require 'views/student/view_assessments.php';
    }
    public function viewAssessment($id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
    
        $conn = Database::getConnection();
        $assessment = Assessment::getById($conn, $id);
    
        if (!$assessment) {
            $_SESSION['error'] = "Assessment not found.";
            header('Location: /student/view_assessments');
            exit;
        }
    
        // Check if student has already taken this assessment
        $stmt = $conn->prepare("SELECT * FROM assessment_results WHERE student_email = ? AND assessment_id = ?");
        $stmt->execute([$_SESSION['email'], $id]);
        $previousAttempt = $stmt->fetch();
        
        if ($previousAttempt) {
            $_SESSION['error'] = "You have already completed this assessment. Score: " . $previousAttempt['score'] . "%";
            header('Location: /student/view_assessments');
            exit;
        }
    
        require 'views/student/view_assessment.php';
    }

    public function submitAssessmentResult() {
        if (!isset($_SESSION['email'])) {
            error_log("Session email not set");
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }

        $conn = Database::getConnection();
        
        // Get and log raw POST data
        $raw_data = file_get_contents('php://input');
        error_log("Raw assessment data received: " . $raw_data);
        
        // Decode JSON data
        $data = json_decode($raw_data, true);
        
        // Log decoded data
        error_log("Decoded assessment data: " . print_r($data, true));
        
        // Check if the required data is present
        if (!isset($data['assessment_id']) || !isset($data['score']) || 
            !isset($data['total_questions']) || !isset($data['correct_answers'])) {
            error_log("Missing assessment data fields. Required fields not present in: " . json_encode($data));
            echo json_encode(['success' => false, 'error' => 'Missing assessment data']);
            exit;
        }
        
        // Prepare the result data
        $result = [
            'student_email' => $_SESSION['email'],
            'assessment_id' => $data['assessment_id'],
            'score' => $data['score'],
            'total_questions' => $data['total_questions'],
            'correct_answers' => $data['correct_answers'],
            'submission_date' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Insert into assessment_results table
            $sql = "INSERT INTO assessment_results (student_email, assessment_id, score, total_questions, correct_answers, submission_date) 
                    VALUES (:student_email, :assessment_id, :score, :total_questions, :correct_answers, :submission_date)";
            
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute($result);
            
            if ($success) {
                error_log("Assessment result successfully saved for student: " . $_SESSION['email']);
            } else {
                error_log("Failed to save assessment result for student: " . $_SESSION['email']);
            }
            
            echo json_encode(['success' => $success]);
        } catch (PDOException $e) {
            error_log("Assessment submission error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    }

    public function updateProfileField() {

        $conn = Database::getConnection();
        $studentId = $_SESSION['student_id'];

        // Get the field and value from POST data
        $allowedFields = ['name', 'email', 'regd_no', 'section', 'stream', 'year', 'dept'];
        
        // Get the first key from POST data as the field name
        $field = array_keys($_POST)[0] ?? null;
        $value = $_POST[$field] ?? null;

        if (!$field || !$value) {
            echo json_encode(['success' => false, 'message' => 'Missing field or value']);
            exit;
        }

        if (!in_array($field, $allowedFields)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field']);
            exit;
        }

        // Special validation for email
        if ($field === 'email') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit;
            }

            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
            $stmt->execute([$value, $studentId]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already in use']);
                exit;
            }
        }

        try {
            // Prepare and execute the update query
            $sql = "UPDATE students SET $field = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([$value, $studentId]);

            if ($success) {
                // If email was updated, update the session
                if ($field === 'email') {
                    $_SESSION['email'] = $value;
                }

                echo json_encode([
                    'success' => true,
                    'message' => ucfirst($field) . ' updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update ' . $field
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error updating profile field: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        }
    }
}
?>