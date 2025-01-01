<?php 
namespace Controllers;

use Models\Admin as AdminModel;
use Models\Student;
use Models\Spoc;
use Models\Course;
use Models\University;
use Models\Database;
use Models\Faculty;
use Models\Todo;
use Models\Mailer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Models\Discussion;
use Models\Meetings;
use Models\Notification;
use Models\Assignment;
use Models\feedback;
use Models\Cohort;
use Models\Company;
use PDO;
use ZipArchive;
use Models\VirtualClassroom;
use SimpleXMLElement;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

use Exception;
use PDOException;
use ZoomAPI;

require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/Database.php';

// Include Zoom integration
require_once __DIR__ . '/../models/zoom_integration.php';

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
    
        // Fetch data for students
        $stmt = $conn->query("SELECT id, name, email, created_at, last_login, first_login, login_count FROM students");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Fetch data for faculty
        $stmt = $conn->query("SELECT id, name, email, created_at, last_login, first_login, login_count FROM faculty");
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Fetch data for spocs
        $stmt = $conn->query("SELECT id, name, email, created_at, last_login, first_login, login_count FROM spocs");
        $spocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
    
        // Populate data for students
        $studentSheet = $spreadsheet->getActiveSheet();
        $studentSheet->setTitle('Students');
        $headers = ['ID', 'Name', 'Email', 'Created At', 'Last Login', 'First Login', 'Login Count'];
        $studentSheet->fromArray($headers, NULL, 'A1');
        $row = 2;
        foreach ($students as $student) {
            $studentSheet->fromArray(array_values($student), NULL, 'A' . $row);
            $row++;
        }
    
        // Create and populate data for faculty
        $facultySheet = $spreadsheet->createSheet();
        $facultySheet->setTitle('Faculty');
        $facultySheet->fromArray($headers, NULL, 'A1');
        $row = 2;
        foreach ($faculty as $member) {
            $facultySheet->fromArray(array_values($member), NULL, 'A' . $row);
            $row++;
        }
    
        // Create and populate data for spocs
        $spocSheet = $spreadsheet->createSheet();
        $spocSheet->setTitle('SPOCs');
        $spocSheet->fromArray($headers, NULL, 'A1');
        $row = 2;
        foreach ($spocs as $spoc) {
            $spocSheet->fromArray(array_values($spoc), NULL, 'A' . $row);
            $row++;
        }
    
        // Ensure the reports directory exists
        $reportsDir = __DIR__ . '/../../reports';
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }
    
        // Write the spreadsheet to a file
        $fileName = 'usage_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = $reportsDir . '/' . $fileName;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        // Output the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    
        // Delete the file after download
        unlink($filePath);
    }
    
    private function fetchUserData($conn, $userType, $timeRange) {
        switch ($userType) {
            case 'admins':
                $sql = "SELECT name, username, last_login as Last_Usage FROM admins WHERE DATE(last_login) = CURDATE()";
                break;
            case 'spocs':
                $sql = "SELECT spocs.name, spocs.email, universities.long_name as university, last_login as Last_Usage 
                        FROM spocs 
                        JOIN universities ON spocs.university_id = universities.id 
                        WHERE DATE(spocs.last_login) = CURDATE()";
                break;
            case 'faculty':
                $sql = "SELECT faculty.name, faculty.email, universities.long_name as university, faculty.section, last_login as Last_Usage 
                        FROM faculty 
                        JOIN universities ON faculty.university_id = universities.id 
                        WHERE DATE(faculty.last_login) = CURDATE()";
                break;
            case 'students':
                $sql = "SELECT students.name, students.email, universities.long_name as university, students.section, last_login as Last_Usage 
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
        
        // Fetch companies
        $companies = Company::getAll($conn);
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $long_name = $_POST['long_name'];
            $short_name = $_POST['short_name'];
            $location = $_POST['location'];
            $country = $_POST['country'];
            $company_id = $_POST['company_id'];
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
                $result = $university->addUniversity($conn, $long_name, $short_name, $location, $country, $company_id, $spoc_name, $spoc_email, $spoc_phone, $spoc_password);
                $message = $result['message'];
                $message_type = $result['message_type'];
    
                // Update the company's university_ids field
                if ($result['message_type'] == 'success') {
                    $company = Company::getById($conn, $company_id);
                    $university_ids = json_decode($company['university_ids'], true) ?? [];
                    $university_ids[] = $result['university_id'];
                    Company::updateUniversityIds($conn, $company_id, json_encode($university_ids));
                }
    
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

    public function addCompany() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            $conn = Database::getConnection();
            $sql = "INSERT INTO company (name, description) VALUES (:name, :description)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':description', $description);

            if ($stmt->execute()) {
                header("Location: /admin/manage_company");
                exit();
            } else {
                echo "Error: Unable to create company.";
            }
        }

        require 'views/admin/addCompany.php';
    }

    public function manageCompany() {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM company";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require 'views/admin/manageCompany.php';
    }

    public function deleteCompany() {
        $id = $_POST['id'];

        $conn = Database::getConnection();
        $sql = "DELETE FROM company WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Company deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete company']);
        }
    }

    public function editCompany($id) {
        $conn = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            if (Company::update($conn, $id, $name, $description)) {
                header("Location: /admin/manage_company");
                exit();
            } else {
                echo "Error: Unable to update company.";
            }
        } else {
            $company = Company::getById($conn, $id);

            if (!$company) {
                echo "Company not found.";
                return;
            }

            require 'views/admin/editCompany.php';
        }
    }
    public function viewCompany($id) {
        $conn = Database::getConnection();
        $company = Company::getById($conn, $id);
    
        if (!$company) {
            echo "Company not found.";
            return;
        }
    
        // Fetch universities associated with the company
        $university_ids = json_decode($company['university_ids'], true) ?? [];
        $universities = [];
        foreach ($university_ids as $university_id) {
            $universities[] = University::getById($conn, $university_id);
        }

        // Fetch all universities
        $allUniversities = University::getAll($conn);

        require 'views/admin/viewCompany.php';
    }

    public function removeUniversities() {
        $conn = Database::getConnection();
        $university_ids = $_POST['university_ids'];
    
        foreach ($university_ids as $university_id) {
            // Get the university details
            $university = University::getById($conn, $university_id);
            if ($university) {
                // Remove the university_id from the company's university_ids
                $company_id = $university['company_id'];
                $company = Company::getById($conn, $company_id);
                if ($company) {
                    $university_ids = json_decode($company['university_ids'], true) ?? [];
                    $university_ids = array_diff($university_ids, [$university_id]);
                    Company::updateUniversityIds($conn, $company_id, json_encode($university_ids));
                }
    
                // Set the company_id in the university to NULL
                University::updateCompanyId($conn, $university_id, NULL);
            }
        }
    
        echo json_encode(['message' => 'Selected universities have been removed successfully.']);
    }

    public function addUniversityToCompany() {
        $conn = Database::getConnection();
        $university_ids = $_POST['university_ids'];
        $company_id = $_POST['company_id'];

        // Update the university's company_id
        foreach ($university_ids as $university_id) {
            University::updateCompanyId($conn, $university_id, $company_id);
        }

        // Update the company's university_ids
        $company = Company::getById($conn, $company_id);
        $existing_university_ids = json_decode($company['university_ids'], true) ?? [];
        $updated_university_ids = array_merge($existing_university_ids, $university_ids);
        Company::updateUniversityIds($conn, $company_id, json_encode($updated_university_ids));

        echo json_encode(['message' => 'Universities added to company successfully']);
    }
    public function deleteStudents() {
        $conn = Database::getConnection();
        $student_ids = $_POST['selected'];
    
        if (!empty($student_ids)) {
            $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
            $sql = "DELETE FROM students WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($student_ids)) {
                $_SESSION['message'] = 'Selected students deleted successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to delete selected students.';
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'No students selected for deletion.';
            $_SESSION['message_type'] = 'warning';
        }
    
        header('Location: /admin/manage_students');
        exit();
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
                    $mailer = new Mailer();
                    $subject = 'Welcome to EyeBook!';
                    $body = "Dear {$data['name']},<br><br>Your account has been created successfully.<br><br>Username: {$data['email']}<br>Password: {$data['password']}<br><br>Best Regards,<br>EyeBook Team";
                    $mailer->sendMail($data['email'], $subject, $body);
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
        if (isset($_POST['selected'])) {
            $selectedStudents = $_POST['selected'];
            foreach ($selectedStudents as $studentId) {
                $newPassword = 'newpassword123'; // Generate or set a new password
                Student::updatePassword($conn, $studentId, password_hash($newPassword, PASSWORD_BCRYPT));
            }
            $_SESSION['message'] = 'Passwords have been reset successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'No students selected for password reset.';
            $_SESSION['message_type'] = 'warning';
        }
        header('Location: /admin/manage_students');
        exit();
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
        $assignedUniversities = is_array($course['university_id']) ? $course['university_id'] : json_decode($course['university_id'], true);

        // Fetch universities details
        $universities = University::getAll($conn);
        $university_id = $course['university_id'];
        $allFaculty = [];
        $allStudents = [];
        foreach ($assignedUniversities as $university_id) {
            $faculty = Faculty::getAllByUniversity($conn, $university_id);
            $students = Student::getAllByUniversity($conn, $university_id);
            $allFaculty = array_merge($allFaculty, $faculty);
            $allStudents = array_merge($allStudents, $students);
        }

        $assignedFaculty = array_filter($allFaculty, function($faculty) use ($course_id) {
            $assigned_courses = $faculty['assigned_courses'] ? json_decode($faculty['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });
    
        $assignedStudents = array_filter($allStudents, function($student) use ($course_id) {
            $assigned_courses = $student['assigned_courses'] ? json_decode($student['assigned_courses'], true) : [];
            return in_array($course_id, $assigned_courses);
        });

    
        require 'views/admin/view_course.php';
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
    
        require 'views/admin/book_view.php';
    }

    public function viewECBook($hashedId) {
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
        $ec_content = json_decode($course['EC_content'], true);
        if (!is_array($ec_content)) {
            echo 'Invalid EC content format.';
            exit;
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $ec_content[0]['indexPath'];
    
        require 'views/admin/book_view.php';
    }

    public function uploadSingleFaculty() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        $message = '';
        $message_type = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $university_id = $_POST['university_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $section = $_POST['section'];
            $stream = $_POST['stream'];
            $department = $_POST['department'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
            // Check for duplicate email
            $sql = "SELECT COUNT(*) as count FROM faculty WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result['count'] > 0) {
                $message = "Email already exists.";
                $message_type = "danger";
            } else {
                // Insert faculty record
                $sql = "INSERT INTO faculty (name, email, phone, section, stream, department, university_id, password) VALUES (:name, :email, :phone, :section, :stream, :department, :university_id, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':section' => $section,
                    ':stream' => $stream,
                    ':department' => $department,
                    ':university_id' => $university_id,
                    ':password' => $password
                ]);
    
                $message = "Faculty uploaded successfully.";
                $message_type = "success";
            }
        }
    
        require 'views/admin/upload_faculty.php';
    }
    
    public function uploadFaculty() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        $duplicateRecords = [];
        $message = '';
        $message_type = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file']['tmp_name'];
            $university_id = $_POST['university_id'];
    
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
    
            $duplicateRecords = [];
            $successCount = 0;
    
            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    // Skip header row
                    continue;
                }
    
                $name = $row[0];
                $email = $row[1];
                $phone = $row[2];
                $section = $row[3];
                $stream = $row[4];
                $department = $row[5];
                $password = password_hash($row[6], PASSWORD_BCRYPT);
    
                // Check for duplicate email
                if (Faculty::existsByEmail($conn, $email)) {
                    $duplicateRecords[] = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'section' => $section,
                        'stream' => $stream,
                        'department' => $department,
                        'university_id' => $university_id
                    ];
                    continue;
                }
    
                // Insert faculty record
                $sql = "INSERT INTO faculty (name, email, phone, section, stream, department, university_id, password) VALUES (:name, :email, :phone, :section, :stream, :department, :university_id, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':section' => $section,
                    ':stream' => $stream,
                    ':department' => $department,
                    ':university_id' => $university_id,
                    ':password' => $password
                ]);
    
                $successCount++;
            }
    
            if (empty($duplicateRecords)) {
                $message = "Faculty members uploaded successfully.";
                $message_type = "success";
            } else {
                $message = "Some records were not uploaded due to duplicates.";
                $message_type = "warning";
            }
        }
    
        require 'views/admin/uploadFaculty.php';
    }
    
    public function manageFaculty() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        $duplicateRecords = [];
        $message = '';
        $message_type = '';

        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        function fetchFaculty($conn, $search = '', $limit = 10, $offset = 0) {
            $sql = "SELECT * FROM faculty WHERE name LIKE :search OR email LIKE :search LIMIT :limit OFFSET :offset";
            $stmt = $conn->prepare($sql);
            $likeSearch = "%$search%";
            $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        function countFaculty($conn, $search = '') {
            $sql = "SELECT COUNT(*) as count FROM faculty WHERE name LIKE :search OR email LIKE :search";
            $stmt = $conn->prepare($sql);
            $likeSearch = "%$search%";
            $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }

        $faculty = fetchFaculty($conn, $search, $limit, $offset);
        $totalFaculty = countFaculty($conn, $search);
        $totalPages = ceil($totalFaculty / $limit);

        include 'views/admin/manage_faculty.php';
    }

    public function viewFacultyProfile($faculty_id) {
        $conn = Database::getConnection();
        $sql = "SELECT faculty.*, universities.long_name as university FROM faculty 
                JOIN universities ON faculty.university_id = universities.id 
                WHERE faculty.id = :faculty_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            die("Faculty not found.");
        }

        include 'views/admin/viewFacultyProfile.php';
    }

    public function editFaculty($faculty_id) {
        $conn = Database::getConnection();
        $message = '';
        $message_type = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $department = $_POST['department'];

            $sql = "UPDATE faculty SET name = ?, email = ?, phone = ?, department = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $phone, $department, $faculty_id]);

            $message = "Faculty details updated successfully!";
            $message_type = "success";

            // Redirect to view faculty profile page after successful update
            header("Location: /admin/viewFacultyProfile/$faculty_id");
            exit();
        }

        $sql = "SELECT * FROM faculty WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$faculty_id]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            die("Faculty not found.");
        }

        include 'views/admin/edit_faculty.php';
    }

    public function resetFacultyPasswords() {
        $conn = Database::getConnection();

        if (isset($_POST['bulk_reset_password'])) {
            $selectedFaculty = $_POST['selected'] ?? [];

            foreach ($selectedFaculty as $facultyId) {
                $faculty = Faculty::getById($conn, $facultyId);
                if ($faculty) {
                    $newPassword = $faculty['email']; // Reset password to email
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    $sql = "UPDATE faculty SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$hashedPassword, $facultyId]);
                }
            }

            header('Location: /admin/manage_faculty');
            exit();
        }
    }

    public function deleteFaculty($faculty_id) {
        $conn = Database::getConnection();

        $sql = "DELETE FROM faculty WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$faculty_id]);

        header('Location: /admin/manage_faculty');
        exit();
    }

    public function resetFacultyPassword($facultyId) {
        $conn = Database::getConnection();
        $faculty = Faculty::getById($conn, $facultyId);
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $newPassword = $data['new_password'];
            $confirmPassword = $data['confirm_password'];
    
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                Faculty::updatePassword($conn, $facultyId, $hashedPassword);
    
                // Send email notification
                $mailer = new Mailer();
                $subject = 'Password Reset Successful';
                $body = "Dear {$faculty['name']},<br><br>Your password has been successfully changed.<br><br>Your new password is: <strong>{$newPassword}</strong><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($faculty['email'], $subject, $body);
    
                $_SESSION['message'] = 'Password reset successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Passwords do not match.';
                $_SESSION['message_type'] = 'error';
            }
    
            echo json_encode(['status' => 'success']);
            exit();
        }
    }

    public function addUnit() {
        set_time_limit(600); // Allow enough time for large uploads.

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course_id = $_POST['course_id'] ?? null;
            $unit_name = $_POST['unit_name'] ?? null;
            $scorm_file = $_FILES['scorm_file'] ?? null;

            if (!$unit_name || !$scorm_file) {
                echo json_encode(['message' => 'Unit name and SCORM package file are required']);
                exit;
            }

            // AWS S3 Configuration
            $bucketName = AWS_BUCKET_NAME;
            $region = AWS_REGION;
            $accessKey = AWS_ACCESS_KEY_ID;
            $secretKey = AWS_SECRET_ACCESS_KEY;

            // Initialize AWS S3 Client
            $s3Client = new S3Client([
                'region' => $region,
                'version' => 'latest',
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            $zipFilePath = $scorm_file['tmp_name'];
            $timestamp = time();
            $uploadPrefix = "scorm_courses/{$course_id}/{$timestamp}/";

            // Process and Upload SCORM Package
            $uploadResult = $this->processScormPackage($s3Client, $bucketName, $zipFilePath, $uploadPrefix, $region);

            if (!$uploadResult['success']) {
                echo json_encode(['message' => $uploadResult['message']]);
                exit;
            }

            echo json_encode([
                'message' => 'SCORM package uploaded successfully',
                'scorm_url' => $uploadResult['index_url']
            ]);
            exit;
        }
    }

    private function processScormPackage($s3Client, $bucketName, $zipFilePath, $uploadPrefix, $region) {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('scorm_');
        mkdir($tempDir);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) !== TRUE) {
            return ['success' => false, 'message' => 'Unable to open SCORM package'];
        }

        $zip->extractTo($tempDir);
        $zip->close();

        $manifestFile = $tempDir . DIRECTORY_SEPARATOR . 'imsmanifest.xml';
        if (!file_exists($manifestFile)) {
            return ['success' => false, 'message' => 'SCORM package missing imsmanifest.xml'];
        }

        $manifestContent = file_get_contents($manifestFile);
        $parsedManifest = $this->parseManifest($manifestContent);

        if (!$parsedManifest['success']) {
            return ['success' => false, 'message' => $parsedManifest['message']];
        }

        // Upload the package to S3
        $indexUrl = null;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            $filePath = $file->getPathname();
            $key = $uploadPrefix . str_replace($tempDir . DIRECTORY_SEPARATOR, '', $filePath);

            $mimeType = $this->getMimeType($filePath);
            try {
                $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $filePath,
                    'ContentType' => $mimeType,
                ]);
            } catch (AwsException $e) {
                return ['success' => false, 'message' => 'Error uploading file to S3: ' . $e->getMessage()];
            }

            if (basename($filePath) === $parsedManifest['launch_file']) {
                $indexUrl = "https://{$bucketName}.s3.{$region}.amazonaws.com/{$key}";
            }
        }

        $this->deleteDir($tempDir);

        if ($indexUrl) {
            return ['success' => true, 'index_url' => $indexUrl];
        } else {
            return ['success' => false, 'message' => 'Failed to upload SCORM package'];
        }
    }

    private function parseManifest($manifestContent) {
        try {
            $xml = new SimpleXMLElement($manifestContent);

            $resources = $xml->xpath('//resources/resource');
            foreach ($resources as $resource) {
                $launchFile = (string)$resource['href'];
                if ($launchFile) {
                    return ['success' => true, 'launch_file' => $launchFile];
                }
            }

            return ['success' => false, 'message' => 'Launch file not found in imsmanifest.xml'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error parsing imsmanifest.xml: ' . $e->getMessage()];
        }
    }

    private function getMimeType($filePath) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        return $mimeType;
    }

    private function deleteDir($dirPath) {
        $files = array_diff(scandir($dirPath), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? $this->deleteDir($filePath) : unlink($filePath);
        }
        rmdir($dirPath);
    }

    public function bulkResetStudentPassword() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $selected = $data['selected'] ?? [];
            $newPassword = $data['new_password'];
            $confirmPassword = $data['confirm_password'];
    
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $students = Student::getByIds($conn, $selected);
    
                foreach ($students as $student) {
                    Student::updatePassword($conn, $student['id'], $hashedPassword);
    
                    // Send email notification
                    $mailer = new Mailer();
                    $subject = 'Password Reset Successful';
                    $body = "Dear {$student['name']},<br><br>Your password has been successfully changed.<br><br>Your new password is: <strong>{$newPassword}</strong><br><br>Best Regards,<br>EyeBook Team";
                    $mailer->sendMail($student['email'], $subject, $body);
                }
    
                $_SESSION['message'] = 'Passwords reset successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Passwords do not match.';
                $_SESSION['message_type'] = 'error';
            }
    
            echo json_encode(['status' => 'success']);
            exit();
        }
    }

    public function bulkResetFacultyPassword() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $selected = $data['selected'] ?? [];
            $newPassword = $data['new_password'];
            $confirmPassword = $data['confirm_password'];
    
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $facultyMembers = Faculty::getByIds($conn, $selected);
    
                foreach ($facultyMembers as $member) {
                    Faculty::updatePassword($conn, $member['id'], $hashedPassword);
    
                    // Send email notification
                    $mailer = new Mailer();
                    $subject = 'Password Reset Successful';
                    $body = "Dear {$member['name']},<br><br>Your password has been successfully changed.<br><br>Your new password is: <strong>{$newPassword}</strong><br><br>Best Regards,<br>EyeBook Team";
                    $mailer->sendMail($member['email'], $subject, $body);
                }
    
                $_SESSION['message'] = 'Passwords reset successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Passwords do not match.';
                $_SESSION['message_type'] = 'error';
            }
    
            echo json_encode(['status' => 'success']);
            exit();
        }
    }

    public function uploadEcContent() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $title = $_POST['ec_content_title'];
            $file = $_FILES['ec_content_file'];
            if (!$title || !$file) {
                echo json_encode(['message' => 'Unit name and EC Content file are required']);
                exit;
            }
            $result = Course::addEcContent($conn, $course_id, $title, $file);
            if (isset($result['indexPath'])) {
                header("Location: /admin/view_course/$course_id");
                exit;
            } else {
                echo json_encode(['message' => $result['message']]);
                exit;
            }
        }
    }

    public function addAdditionalContent() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $title = $_POST['content_title'];
            $content_type = $_POST['content_type'];
    
            if ($content_type == 'link') {
                $link = $_POST['content_link'];
            } else {
                $file = $_FILES['content_file'];
                $upload_dir = 'uploads/additional_content/';
                $file_path = $upload_dir . basename($file['name']);
                $link = $this->uploadFileToS3($file);
            }
    
            $course = Course::getById($conn, $course_id);
            $additional_content = !empty($course['additional_content']) ? json_decode($course['additional_content'], true) : [];
    
            $new_content = [
                'title' => $title,
                'link' => $link
            ];
    
            $additional_content[] = $new_content;
    
            Course::updateAdditionalContent($conn, $course_id, $additional_content);
    
            $_SESSION['message'] = 'Additional content added successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/view_course/' . $course_id);
            exit();
        }
    }
    
    private function uploadFileToS3($file) {
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
    
        $filePath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $key = "additional_content/{$fileName}";
    
        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => $file['type'],
                'ACL' => 'public-read',
            ]);
    
            return $result['ObjectURL'];
        } catch (AwsException $e) {
            error_log('AWS S3 Upload Error: ' . $e->getMessage());
            return null;
        }
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
    
    public function resetStudentPassword($studentId) {
        $conn = Database::getConnection();
        $student = Student::getById($conn, $studentId);
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
    
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                Student::updatePassword($conn, $studentId, $hashedPassword);
    
                // Send email notification
                $mailer = new Mailer();
                $subject = 'Password Reset Successful';
                $body = "Dear {$student['name']},<br><br>Your password has been successfully changed.<br><br>Your new password is: <strong>{$newPassword}</strong><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($student['email'], $subject, $body);
    
                $_SESSION['message'] = 'Password reset successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Passwords do not match.';
                $_SESSION['message_type'] = 'error';
            }
    
            header('Location: /admin/viewStudentProfile/' . $studentId);
            exit();
        }
    }
    

    public function assignCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $university_ids = $_POST['university_ids']; // Expecting an array of university IDs
    
            $course = Course::getById($conn, $course_id);
            $assigned_universities = is_string($course['university_id']) ? json_decode($course['university_id'], true) : $course['university_id'];
            $assigned_universities = is_array($assigned_universities) ? $assigned_universities : [];
    
            foreach ($university_ids as $university_id) {
                if (!in_array($university_id, $assigned_universities)) {
                    $assigned_universities[] = $university_id;
                }
            }
    
            $sql = "UPDATE courses SET university_id = :university_id WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':university_id' => json_encode($assigned_universities),
                ':id' => $course_id
            ]);
    
            echo json_encode(['message' => 'Course assigned to universities successfully', 'success' => true]);
            exit();
        }
    }
    
    public function unassignCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $university_ids = $_POST['university_ids']; // Expecting an array of university IDs
    
            foreach ($university_ids as $university_id) {
                // Unassign the university from the course
                Course::unassignCourseFromUniversity($conn, $course_id, $university_id);
    
                // Fetch all faculty and students associated with the university
                $faculty = Faculty::getAllByUniversity($conn, $university_id);
                $students = Student::getAllByUniversity($conn, $university_id);
    
                // Unassign each faculty from the course
                foreach ($faculty as $faculty_member) {
                    Faculty::unassignCourse($conn, $faculty_member['id'], $course_id);
                }
    
                // Unassign each student from the course
                foreach ($students as $student) {
                    Student::unassignCourse($conn, $student['id'], $course_id);
                }
                
            }
    
            echo json_encode(['message' => 'Course unassigned from universities, faculty, and students successfully', 'success' => true]);
            exit();
        }
    }

    public function uploadSingleStudent() {
        $conn = Database::getConnection();
        $data = [
            'regd_no' => $_POST['regd_no'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'section' => $_POST['section'],
            'stream' => $_POST['stream'],
            'year' => $_POST['year'],
            'dept' => $_POST['dept'],
            'password' => $_POST['password']
        ];
        $university_id = $_POST['university_id'];
    
        $result = Student::uploadStudents($conn, $data, $university_id);
    
        if ($result['duplicate']) {
            $message = "Duplicate record found: " . htmlspecialchars($data['regd_no']) . " - " . htmlspecialchars($data['email']);
            $message_type = "warning";
        } else {
            $message = "Student uploaded successfully.";
            $message_type = "success";
        }
    
        require 'views/admin/uploadStudents.php';
    }

    public function assignFaculty() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::assignCourse($conn, $faculty_id, $course_id);
            Course::assignFaculty($conn, $course_id, $faculty_id);
        }
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header("Location: /admin/view_course/$course_id");
        exit();
    }

    public function assignStudents() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $student_ids = $_POST['student_ids'];
    
        foreach ($student_ids as $student_id) {
            Student::assignCourse($conn, $student_id, $course_id);
        }
        Course::assignStudents($conn, $course_id, $student_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header("Location: /admin/view_course/$course_id");
        exit();
    }

    public function unassignFaculty() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $faculty_ids = $_POST['faculty_ids'];
    
        foreach ($faculty_ids as $faculty_id) {
            Faculty::unassignCourse($conn, $faculty_id, $course_id);
        }
        Course::unassignFaculty($conn, $course_id, $faculty_ids);
    
        $encoded_course_id = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($course_id));
        header("Location: /admin/view_course/$course_id");
        exit();
    }
    
    public function unassignStudents() {
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
        header("Location: /admin/view_course/$course_id");
        exit();
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
        $conn = Database::getConnection();
        $courses = Course::getAllWithUniversity($conn);
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $topic = $_POST['topic'];
            $start_time_local = $_POST['start_time'];
            $duration = $_POST['duration'];
            $selectedCourses = $_POST['courses'];
    
            // Convert local time to UTC for Zoom
            $start_time_local_dt = new \DateTime($start_time_local, new \DateTimeZone('Asia/Kolkata')); // Set the local time zone
            $start_time_utc = clone $start_time_local_dt;
            $start_time_utc->setTimezone(new \DateTimeZone('UTC'));
            $start_time_iso8601 = $start_time_utc->format(\DateTime::ATOM);
    
            $zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);
            $classroom = $zoom->createVirtualClassroom($topic, $start_time_iso8601, $duration);
    
            if (isset($classroom['id'])) {
                // Save the virtual classroom to the database and get the virtual class ID
                $virtualClassId = $zoom->saveVirtualClassroomToDatabase($classroom, $selectedCourses, $start_time_local_dt->format('Y-m-d H:i:s'));
    
                // Update the courses with the virtual class ID
                foreach ($selectedCourses as $courseId) {
                    $course = Course::getById($conn, $courseId);
                    $virtualClassIds = !empty($course['virtual_class_id']) ? json_decode($course['virtual_class_id'], true) : [];
                    $virtualClassIds[] = $virtualClassId;
                    $stmt = $conn->prepare("UPDATE courses SET virtual_class_id = ? WHERE id = ?");
                    $stmt->execute([json_encode($virtualClassIds), $courseId]);
                }
    
                // Redirect to the admin virtual classroom dashboard
                header('Location: /admin/virtual_classroom');
                exit();
            } else {
                echo "Error creating virtual classroom.";
            }
        }
    
        require 'views/admin/create_virtual_classroom.php';
    }

    public function virtualClassroom() {
        $conn = Database::getConnection();
        $adminClassrooms = (new VirtualClassroom($conn))->getAll();
        $courses = Course::getAllWithUniversity($conn);
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

    public function manageAssignments() {
        $conn = Database::getConnection();
        $assignments = Assignment::getAll($conn);
        foreach ($assignments as &$assignment) {
            $assignment['submission_count'] = Assignment::getSubmissionCount($conn, $assignment['id']);
        }
        usort($assignments, function($a, $b) {
            return strtotime($b['due_date']) - strtotime($a['due_date']);
        });
        require 'views/admin/manage_assignments.php';
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
            
                header('Location: /admin/manage_assignments');
                exit;
            } catch (PDOException $e) {
                $messages[] = "Error creating assignment: " . $e->getMessage();
            }
        }

        $courses = Course::getAll($conn);
        require 'views/admin/assignment_create.php';
    }
    public function archiveCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['archive_course_id'];
            Course::archiveCourse($conn, $course_id);
    
            $_SESSION['message'] = 'Course archived successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/manage_courses');
            exit();
        }
    }
    public function unarchiveCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['archive_course_id'];
            Course::unarchiveCourse($conn, $course_id);
    
            $_SESSION['message'] = 'Course unarchived successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/manage_courses');
            exit();
        }
    }

    public function toggleFeedback() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $feedback_enabled = $_POST['enabled'] === 'true' ? 1 : 0;

        Course::updateFeedbackStatus($conn, $course_id, $feedback_enabled);

        header('Location: /admin/view_course/' . $course_id);
        exit();
    }

    public function viewAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        $course_id = json_decode($assignment['course_id'], true)[0];

        require 'views/admin/view_assignment.php';
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

        require 'views/admin/grade_submission.php';
    }

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
    public function gradeAssignment($assignmentId, $studentId) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $marks = $_POST['marks'];
            $feedback = $_POST['feedback'];
            $grade = $this->calculateGrade($marks);

            Assignment::grade($conn, $assignmentId, $studentId, $grade, $feedback);
            header('Location: /admin/manage_assignments');
            exit;
        }
        $submission = Assignment::getSubmission($conn, $assignmentId, $studentId);
        require 'views/admin/grade_assignment.php';
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

    public function createCohort() {
        $conn = Database::getConnection();
        $students = Student::getAllWithUniversity($conn); // Fetch all students with university
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];
            Cohort::create($conn, $name, $student_ids);
            $message = "Cohort created successfully.";
        }
        require 'views/admin/create_cohort.php';
    }

    public function manageCohort() {
        $conn = Database::getConnection();
        $cohorts = Cohort::getAll($conn);
        foreach ($cohorts as &$cohort) {
            $cohort['student_count'] = Cohort::getStudentCount($conn, $cohort['id']);
        }
        require 'views/admin/manage_cohort.php';
    }

    public function editCohort($cohort_id) {
        $conn = Database::getConnection();
        $message = '';
        $message_type = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
    
            // Update the cohort with only the name
            Cohort::update($conn, $cohort_id, $name);
    
            $message = "Cohort updated successfully!";
            $message_type = "success";
    
            // Redirect to view cohort page after successful update
            header("Location: /admin/view_cohort/$cohort_id");
            exit();
        }
    
        $cohort = Cohort::getById($conn, $cohort_id);
        require 'views/admin/edit_cohort.php';
    }

    public function deleteCohort($id) {
        $conn = Database::getConnection();
        Cohort::delete($conn, $id);
        header("Location: /admin/manage_cohort");
        exit();
    }

    public function viewCohort($id) {
        $conn = Database::getConnection();
        $cohort = Cohort::getById($conn, $id);
        $student_ids = json_decode($cohort['student_ids'], true) ?? [];
        $students = Student::getByIds($conn, $student_ids);
        $courses = Course::getAll($conn);
        $allCourses = Course::getAll($conn);
        $allStudents = Student::getAll($conn);
        $universities = University::getAll($conn); // Fetch all universities
        $existing_student_ids = $student_ids; // Initialize existing student IDs
    
        require 'views/admin/view_cohort.php';
    }

    public function addStudentsToCohort($cohort_id) {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_student_ids = $_POST['student_ids'] ?? [];
    
            // Get the current cohort data
            $cohort = Cohort::getById($conn, $cohort_id);
            $existing_student_ids = json_decode($cohort['student_ids'], true) ?? [];
            $course_ids = json_decode($cohort['course_ids'], true) ?? [];
    
            // Merge new student IDs with existing ones
            $updated_student_ids = array_unique(array_merge($existing_student_ids, $new_student_ids));
    
            // Update the cohort with the new student IDs
            Cohort::updateStudentIds($conn, $cohort_id, $updated_student_ids);
    
            // Assign courses to new students
            Student::assignCoursesToStudents($conn, $new_student_ids, $course_ids);

            // Update courses with new student IDs
            foreach ($course_ids as $course_id) {
                Course::assigncohortstudents($conn, $course_id, $new_student_ids);
            }

            // Redirect to view cohort page after successful update
            header("Location: /admin/view_cohort/$cohort_id");
            exit();
        }
    
        $cohort = Cohort::getById($conn, $cohort_id);
        $allStudents = Student::getAll($conn);
        $universities = University::getAll($conn); // Fetch all universities
        $existing_student_ids = json_decode($cohort['student_ids'], true) ?? []; // Initialize existing student IDs
    
        require 'views/admin/add_students_to_cohort.php';
    }

    public function assignCoursesToCohort() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cohort_id = $_POST['cohort_id'];
            $course_ids = $_POST['course_ids'] ?? [];
    
            foreach ($course_ids as $course_id) {
                // Add course to cohort
                Cohort::addCourse($conn, $cohort_id, $course_id);
    
                // Get student IDs from cohort
                $cohort = Cohort::getById($conn, $cohort_id);
                $student_ids = json_decode($cohort['student_ids'], true) ?? [];
    
                // Assign course to students
                Student::assignCourseToStudents($conn, $student_ids, $course_id);
    
                // Assign students to course
                Course::assignStudentsToCourse($conn, $course_id, $student_ids);
            }
    
            $message = "Courses assigned to cohort successfully!";
            $message_type = "success";
    
            // Redirect to view cohort page after successful assignment
            header("Location: /admin/view_cohort/$cohort_id");
            exit();
        }
    }

    public function unassignCourseFromCohort($cohort_id, $course_id) {
        $conn = Database::getConnection();
    
        // Unassign course from cohort
        Cohort::unassignCourse($conn, $cohort_id, $course_id);
    
        // Get student IDs from cohort
        $cohort = Cohort::getById($conn, $cohort_id);
        $student_ids = json_decode($cohort['student_ids'], true) ?? [];
    
        // Unassign course from students
        Student::unassignCourseFromStudents($conn, $student_ids, $course_id);
    
        // Unassign students from course
        Course::unassignStudentsFromCourse($conn, $course_id, $student_ids);
    
        // Redirect to view cohort page after successful unassignment
        header("Location: /admin/view_cohort/$cohort_id");
        exit();
    }

    public function unassignStudentsFromCohort() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cohort_id = $_POST['cohort_id'];
            $student_ids = $_POST['student_ids'] ?? [];
    
            // Get the current cohort data
            $cohort = Cohort::getById($conn, $cohort_id);
            $existing_student_ids = json_decode($cohort['student_ids'], true) ?? [];
            $course_ids = json_decode($cohort['course_ids'], true) ?? [];
    
            // Remove the student IDs from the courses.assigned_students column
            foreach ($course_ids as $course_id) {
                Course::unassigncohortStudentsFromCourse($conn, $course_id, $student_ids);
            }
    
            // Remove the cohorts.course_ids from the selected students.assigned_courses column
            Student::unassignCoursesFromStudents($conn, $student_ids, $course_ids);
    
            // Remove the selected students from the cohorts.student_ids column
            $updated_student_ids = array_values(array_diff($existing_student_ids, $student_ids)); // Reindex the array
            Cohort::updateStudentIds($conn, $cohort_id, $updated_student_ids);
    
            // Redirect to view cohort page after successful update
            header("Location: /admin/view_cohort/$cohort_id");
            exit();
        }
    }

    public function removeContent() {
        $conn = Database::getConnection();
        $type = $_POST['type'];
        $index = $_POST['index'];
        $course_id = $_POST['course_id'];

        // Fetch the course data
        $sql = "SELECT * FROM courses WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
            return;
        }

        // Decode the JSON column
        $content = json_decode($course[$type], true);

        // Remove the specific content based on the index
        if (isset($content[$index])) {
            unset($content[$index]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Content not found']);
            return;
        }

        // Re-index the array and encode it back to JSON
        $content = array_values($content);
        $jsonContent = json_encode($content);

        // Update the course data
        $sql = "UPDATE courses SET $type = :content WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':content', $jsonContent, PDO::PARAM_STR);
        $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Content removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove content']);
        }
    }

}
?>
