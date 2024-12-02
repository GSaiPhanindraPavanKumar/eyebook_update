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

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

use Exception;
use PDOException;
use ZoomAPI;


require_once 'vendor/autoload.php';
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

class AdminController {
    public function index() {
        $admin = new AdminModel();
        require 'views/admin/index.php';
    }

    public function userProfile(){
        $conn = Database::getConnection();
        $adminModel = new AdminModel();
        $admin = $adminModel->getUserProfile($conn);
        
        // Debugging: Check if last_login is set in $admin
        error_log('Last login in controller: ' . $admin['last_login']);
        
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
    
        // Fetch usage data
        $usageData = $this->fetchUsageData($conn, 'today');
        $usageLabels = array_keys($usageData);
        $usageValues = array_values($usageData);
    
        require 'views/admin/dashboard.php';
    }

    public function getUsageData() {
        $conn = Database::getConnection();
        $timeRange = $_GET['timeRange'] ?? 'today';
    
        $usageData = $this->fetchUsageData($conn, $timeRange);
        $labels = array_keys($usageData);
        $counts = array_values($usageData);
    
        echo json_encode(['labels' => $labels, 'data' => $counts]);
    }

    private function fetchUsageData($conn, $timeRange) {
        switch ($timeRange) {
            case 'today':
                $sql = "SELECT 'Admin' as user_type, COUNT(*) as count FROM admins WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'SPOC' as user_type, COUNT(*) as count FROM spocs WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'Faculty' as user_type, COUNT(*) as count FROM faculty WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'Student' as user_type, COUNT(*) as count FROM students WHERE DATE(last_login) = CURDATE()";
                break;
            case 'month':
                $sql = "SELECT 'Admin' as user_type, COUNT(*) as count FROM admins WHERE MONTH(last_login) = MONTH(CURDATE())
                        UNION ALL
                        SELECT 'SPOC' as user_type, COUNT(*) as count FROM spocs WHERE MONTH(last_login) = MONTH(CURDATE())
                        UNION ALL
                        SELECT 'Faculty' as user_type, COUNT(*) as count FROM faculty WHERE MONTH(last_login) = MONTH(CURDATE())
                        UNION ALL
                        SELECT 'Student' as user_type, COUNT(*) as count FROM students WHERE MONTH(last_login) = MONTH(CURDATE())";
                break;
            case 'quarter':
                $sql = "SELECT 'Admin' as user_type, COUNT(*) as count FROM admins WHERE QUARTER(last_login) = QUARTER(CURDATE())
                        UNION ALL
                        SELECT 'SPOC' as user_type, COUNT(*) as count FROM spocs WHERE QUARTER(last_login) = QUARTER(CURDATE())
                        UNION ALL
                        SELECT 'Faculty' as user_type, COUNT(*) as count FROM faculty WHERE QUARTER(last_login) = QUARTER(CURDATE())
                        UNION ALL
                        SELECT 'Student' as user_type, COUNT(*) as count FROM students WHERE QUARTER(last_login) = QUARTER(CURDATE())";
                break;
            case 'year':
                $sql = "SELECT 'Admin' as user_type, COUNT(*) as count FROM admins WHERE YEAR(last_login) = YEAR(CURDATE())
                        UNION ALL
                        SELECT 'SPOC' as user_type, COUNT(*) as count FROM spocs WHERE YEAR(last_login) = YEAR(CURDATE())
                        UNION ALL
                        SELECT 'Faculty' as user_type, COUNT(*) as count FROM faculty WHERE YEAR(last_login) = YEAR(CURDATE())
                        UNION ALL
                        SELECT 'Student' as user_type, COUNT(*) as count FROM students WHERE YEAR(last_login) = YEAR(CURDATE())";
                break;
            default:
                $sql = "SELECT 'Admin' as user_type, COUNT(*) as count FROM admins WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'SPOC' as user_type, COUNT(*) as count FROM spocs WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'Faculty' as user_type, COUNT(*) as count FROM faculty WHERE DATE(last_login) = CURDATE()
                        UNION ALL
                        SELECT 'Student' as user_type, COUNT(*) as count FROM students WHERE DATE(last_login) = CURDATE()";
                break;
        }
    
        $stmt = $conn->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $usageData = [];
        foreach ($data as $row) {
            $usageData[$row['user_type']] = $row['count'];
        }
    
        return $usageData;
    }

    public function downloadUsageReport() {
        $conn = Database::getConnection();
    
        // Fetch data for each user type
        $admins = $this->fetchUserData($conn, 'admins', 'today');
        $spocs = $this->fetchUserData($conn, 'spocs', 'today');
        $faculty = $this->fetchUserData($conn, 'faculty', 'today');
        $students = $this->fetchUserData($conn, 'students', 'today');
    
        // Fetch usage summary data
        $usageData = $this->fetchUsageData($conn, 'today');
        $usageSummary = [];
        foreach ($usageData as $userType => $count) {
            $usageSummary[] = [$userType, $count];
        }
    
        // Create a new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
        // Add summary data to the first sheet
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');
        $summarySheet->fromArray(['User Type', 'Count'], NULL, 'A1');
        $summarySheet->fromArray($usageSummary, NULL, 'A2');
    
        // Add data to the Admin sheet
        $adminSheet = $spreadsheet->createSheet();
        $adminSheet->setTitle('Admins');
        $adminSheet->fromArray(['Name', 'Username'], NULL, 'A1'); // Updated header
        $adminSheet->fromArray($admins, NULL, 'A2');
    
        // Add data to the SPOC sheet
        $spocSheet = $spreadsheet->createSheet();
        $spocSheet->setTitle('SPOCs');
        $spocSheet->fromArray(['Name', 'Email', 'University'], NULL, 'A1');
        $spocSheet->fromArray($spocs, NULL, 'A2');
    
        // Add data to the Faculty sheet
        $facultySheet = $spreadsheet->createSheet();
        $facultySheet->setTitle('Faculty');
        $facultySheet->fromArray(['Name', 'Email', 'University', 'Section'], NULL, 'A1');
        $facultySheet->fromArray($faculty, NULL, 'A2');
    
        // Add data to the Student sheet
        $studentSheet = $spreadsheet->createSheet();
        $studentSheet->setTitle('Students');
        $studentSheet->fromArray(['Name', 'Email', 'University', 'Section'], NULL, 'A1');
        $studentSheet->fromArray($students, NULL, 'A2');
    
        // Set the first sheet as the active sheet
        $spreadsheet->setActiveSheetIndex(0);
    
        // Generate the Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'user_usage_report_' . date('Ymd') . '.xlsx';
    
        // Send the file to the browser for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    }
    
    private function fetchUserData($conn, $userType, $timeRange) {
        switch ($userType) {
            case 'admins':
                $sql = "SELECT name, username FROM admins WHERE DATE(last_login) = CURDATE()";
                break;
            case 'spocs':
                $sql = "SELECT spocs.name, spocs.email, universities.long_name as university 
                        FROM spocs 
                        JOIN universities ON spocs.university_id = universities.id 
                        WHERE DATE(spocs.last_login) = CURDATE()";
                break;
            case 'faculty':
                $sql = "SELECT faculty.name, faculty.email, universities.long_name as university, faculty.section 
                        FROM faculty 
                        JOIN universities ON faculty.university_id = universities.id 
                        WHERE DATE(faculty.last_login) = CURDATE()";
                break;
            case 'students':
                $sql = "SELECT students.name, students.email, universities.long_name as university, students.section 
                        FROM students 
                        JOIN universities ON students.university_id = universities.id 
                        WHERE DATE(students.last_login) = CURDATE()";
                break;
            default:
                return [];
        }
    
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $spoc_password = !empty($spoc_pass) ? password_hash($spoc_pass, PASSWORD_BCRYPT) : null;
    
            if (University::existsByShortName($conn, $short_name)) {
                $message = "Duplicate entry for short name: " . $short_name;
                $message_type = "warning";
            } else if (!empty($spoc_email) && !filter_var($spoc_email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email address: " . $spoc_email;
                $message_type = "error";
            } else if (!empty($spoc_email) && Spoc::existsByEmail($conn, $spoc_email)) {
                $message = "Duplicate entry for email: " . $spoc_email;
                $message_type = "warning";
            } else {
                $university = new University($conn);
                $result = $university->addUniversity($conn, $long_name, $short_name, $location, $country, $spoc_name, $spoc_email, $spoc_phone, $spoc_password);
                $message = $result['message'];
                $message_type = $result['message_type'];
    
                // Validate email address before sending
                if (!empty($spoc_email) && filter_var($spoc_email, FILTER_VALIDATE_EMAIL)) {
                    $mailer = new Mailer();
                    $subject = 'Welcome to EyeBook!';
                    $body = "Dear $spoc_name,<br><br>Your account has been created successfully as an SPOC for <b>$long_name<b>.<br><br>Username: $spoc_email <br>Password: $spoc_pass<br><br>Best Regards,<br>EyeBook Team";
                    $mailer->sendMail($spoc_email, $subject, $body);
                }
            }
        }
    
        require 'views/admin/addUniversity.php';
    }
    
    private function uploadLogoToS3($logo, $short_name) {
        $bucketName = AWS_BUCKET_NAME;
        $region = AWS_REGION;
        $accessKey = AWS_ACCESS_KEY_ID;
        $secretKey = AWS_SECRET_ACCESS_KEY;
    
        $s3Client = new S3Client([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
        ]);
    
        $filePath = $logo['tmp_name'];
        $fileName = basename($logo['name']);
        $key = "logo/university/{$short_name}/{$fileName}";
    
        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => $logo['type'],
                'ACL' => 'public-read',
            ]);
    
            return $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log('AWS S3 Upload Error: ' . $e->getMessage());
            return null;
        }
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

    public function editUniversity($university_id) {
        $conn = Database::getConnection();
        $message = '';
        $message_type = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $long_name = $_POST['long_name'];
            $short_name = $_POST['short_name'];
            $location = $_POST['location'];
            $country = $_POST['country'];
            $spoc_name = $_POST['spoc_name'];
            $spoc_email = $_POST['spoc_email'];
            $spoc_phone = $_POST['spoc_phone'];
            $spoc_pass = $_POST['spoc_password'];
            $spoc_password = !empty($spoc_pass) ? password_hash($spoc_pass, PASSWORD_BCRYPT) : null;
            $logo_url = null;
    
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                $logo = $_FILES['logo'];
                $logo_path = $this->uploadLogoToS3($logo, $short_name);
                if ($logo_path) {
                    $logo_url = $logo_path;
                }
            }
    
            // Fetch the current logo URL if a new logo is not uploaded
            if (!$logo_url) {
                $current_university = University::getById($conn, $university_id);
                $logo_url = $current_university['logo_url'];
            }
    
            $sql = "UPDATE universities SET long_name = ?, short_name = ?, location = ?, country = ?, logo_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$long_name, $short_name, $location, $country, $logo_url, $university_id]);
    
            // Update SPOC details if provided
            if (!empty($spoc_email) || !empty($spoc_name) || !empty($spoc_phone) || !empty($spoc_password)) {
                // Check if SPOC already exists for the university
                $spoc_exists = Spoc::getByUniversityId($conn, $university_id);
                if ($spoc_exists) {
                    $sql = "UPDATE spocs SET name = ?, email = ?, phone = ?, password = IFNULL(?, password) WHERE university_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$spoc_name, $spoc_email, $spoc_phone, $spoc_password, $university_id]);
                } else {
                    $sql = "INSERT INTO spocs (name, email, phone, password, university_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$spoc_name, $spoc_email, $spoc_phone, $spoc_password, $university_id]);
                }
            }
    
            $message = "University and SPOC details updated successfully!";
            $message_type = "success";
    
            // Redirect to view university page after successful update
            header("Location: /admin/view_university/$university_id");
            exit();
        }
    
        $university = University::getById($conn, $university_id);
        $spoc = Spoc::getByUniversityId($conn, $university_id);
        require 'views/admin/edit_university.php';
    }
    
    public function deleteUniversity($university_id) {
        $conn = Database::getConnection();
    
        $sql = "DELETE FROM universities WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$university_id]);
    
        header('Location: /admin/manage_university');
        exit();
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

    public function resetStudentPasswords() {
        $conn = Database::getConnection();
    
        if (isset($_POST['bulk_reset_password'])) {
            $selectedStudents = $_POST['selected'] ?? [];
    
            foreach ($selectedStudents as $studentId) {
                $student = Student::getById($conn, $studentId);
                if ($student) {
                    $newPassword = $student['email']; // Reset password to email
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
                    $sql = "UPDATE students SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$hashedPassword, $studentId]);
                }
            }
    
            header('Location: /admin/manage_students');
            exit();
        }
    }
    
    public function addCourse() {
        $conn = Database::getConnection();
        $message = '';
        $message_type = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $message = Course::create($conn, $name, $description);
            if ($message === "Course created successfully!") {
                $message_type = 'success';
            } else {
                $message_type = 'error';
            }
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
        set_time_limit(600); // Set the maximum execution time to 600 seconds
    
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
    
            // AWS S3 configuration
            $bucketName = AWS_BUCKET_NAME;
            $region = AWS_REGION;
            $accessKey = AWS_ACCESS_KEY_ID;
            $secretKey = AWS_SECRET_ACCESS_KEY;
    
            // Initialize S3 client
            $s3Client = new S3Client([
                'region' => $region,
                'version' => 'latest',
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);
    
            // Upload SCORM file to S3
            $filePath = $scorm_file['tmp_name'];
            $fileName = basename($scorm_file['name']);
            $timestamp = time();
            $key = "course_documents/{$course_id}/course_book/{$timestamp}-{$fileName}";
    
            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ContentType' => 'application/zip',
                ]);
    
                // Get the URL of the uploaded file
                $fileUrl = $result['ObjectURL'];
    
                // Unzip the uploaded SCORM package in the S3 bucket
                $unzipFolderName = pathinfo($fileName, PATHINFO_FILENAME);
                $unzipKey = "course_documents/{$course_id}/course_book/{$timestamp}-{$unzipFolderName}/";
                $this->unzipS3Object($s3Client, $bucketName, $key, $unzipKey);
    
                // Save the public access link of the index.html file in the database
                $indexUrl = "https://{$bucketName}.s3.{$region}.amazonaws.com/{$unzipKey}/index.html";
                $indexUrl = preg_replace('#/+#', '/', $indexUrl);  // Replace multiple slashes with a single one
    
                // Update the course_book column in the courses table
                $course_book = json_decode($course['course_book'], true) ?? [];
                $course_book[] = [
                    'unit_name' => $unit_name,
                    'scorm_url' => $indexUrl,
                ];
                $course_book_json = json_encode($course_book);
    
                $sql = "UPDATE courses SET course_book = :course_book WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'course_book' => $course_book_json,
                    'id' => $course_id,
                ]);
    
                echo json_encode(['message' => 'Unit added successfully', 'scorm_url' => $indexUrl]);
    
                header("Location: /admin/view_course/$course_id");
                exit;
            } catch (AwsException $e) {
                echo json_encode(['message' => 'Error uploading file to S3: ' . $e->getMessage()]);
            }
        }
    }
    
    private function unzipS3Object($s3Client, $bucketName, $key, $unzipKey) {
        // Download the zip file to a temporary location
        $tempFile = tempnam(sys_get_temp_dir(), 'scorm');
        $s3Client->getObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'SaveAs' => $tempFile,
        ]);
    
        // Unzip the file
        $zip = new ZipArchive;
        if ($zip->open($tempFile) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($filename);
    
                // Extract the file to a temporary location
                $extractTo = tempnam(sys_get_temp_dir(), 'scorm');
                file_put_contents($extractTo, $zip->getFromIndex($i));
    
                // Determine correct Content-Type
                $mimeType = $this->getMimeType($extractTo);
    
                // Upload the extracted file to S3
                $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $unzipKey . $filename,
                    'SourceFile' => $extractTo,
                    'ContentType' => $mimeType,
                ]);
    
                // Delete the temporary file
                unlink($extractTo);
            }
            $zip->close();
        }
    
        // Delete the temporary zip file
        unlink($tempFile);
    }
    
    private function getMimeType($filePath) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
    
        // Normalize file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
        // Fallback MIME types
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'html' => 'text/html',
            'json' => 'application/json',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'zip' => 'application/zip',
            'xml' => 'application/xml',
        ];
    
        // Fallback if incorrect detection
        return $mimeTypes[$extension] ?? $mimeType;
    }    
    

    public function editStudent($student_id) {
        $conn = Database::getConnection();
        $message = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $regd_no = $_POST['regd_no'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $section = $_POST['section'];
            $stream = $_POST['stream'];
            $year = $_POST['year'];
            $dept = $_POST['dept'];
    
            $sql = "UPDATE students SET regd_no = ?, name = ?, email = ?, section = ?, stream = ?, year = ?, dept = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$regd_no, $name, $email, $section, $stream, $year, $dept, $student_id]);
    
            $message = "Student details updated successfully!";
        }
    
        $student = Student::getById($conn, $student_id);
        require 'views/admin/edit_student.php';
    }
    
    public function deleteStudent($student_id) {
        $conn = Database::getConnection();
    
        $sql = "DELETE FROM students WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id]);
    
        header('Location: /admin/manage_students');
        exit();
    }
    
    public function resetStudentPassword($student_id) {
        $conn = Database::getConnection();
    
        $student = Student::getById($conn, $student_id);
        if ($student) {
            $newPassword = $student['email']; // Reset password to email
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $sql = "UPDATE students SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$hashedPassword, $student_id]);
        }
    
        header('Location: /admin/viewStudentProfile/' . $student_id);
        exit();
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

    public function viewStudentProfile($student_id) {
        $conn = Database::getConnection();
        $student = Student::getById($conn, $student_id);
        $university = University::getById($conn, $student['university_id']);
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

    public function createVirtualClassroom() {
        require 'views/admin/create_virtual_classroom.php';
    }

    public function virtualClassroom() {
        require 'views/admin/virtual_classroom_dashboard.php';
    }
    public function editCourse($course_id) {
        $conn = Database::getConnection();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            $sql = "UPDATE courses SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $course_id]);

            $message = "Course updated successfully!";
        }

        $course = Course::getById($conn, $course_id);
        require 'views/admin/edit_course.php';
    }

    public function deleteCourse($course_id) {
        $conn = Database::getConnection();

        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id]);

        header('Location: /admin/manage_courses');
        exit();
    }

}
?>
