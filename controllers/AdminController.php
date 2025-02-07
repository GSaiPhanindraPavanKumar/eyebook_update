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
use Models\Notification;
use Models\Assignment;
use Models\feedback;
use Models\Cohort;
use Models\Company;
use Models\Lab;
use Models\Contest;
use Models\PublicCourse;
use Models\PublicLab;
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

use Models\Ticket;

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
        $publiccourse_count = PublicCourse::getCount($conn);
        $transactions_count = PublicCourse::getTransactionsCount($conn);
    
        $spocs = Spoc::getAll($conn);
        $universities = University::getAll($conn);
        $courses = Course::getAll($conn);
        $todos = Todo::getAll($conn);
    
        // Fetch usage data
        $usageData = $this->fetchUsageData($conn, 'today');
        $usageLabels = array_keys($usageData);
        $usageValues = array_values($usageData);

        // Fetch all virtual classes
        $virtualClassroomModel = new VirtualClassroom($conn);
        $meeting_count = $virtualClassroomModel->getCount();
        $virtualClasses = $virtualClassroomModel->getAll();
        $upcomingClasses = array_filter($virtualClasses, function($class) {
            return strtotime($class['start_time']) > time();
        });

        // Fetch all assignments
        $assignmentModel = new Assignment();
        $assignments = $assignmentModel->getAll($conn);
        $upcomingAssignments = array_filter($assignments, function($assignment) {
            return strtotime($assignment['due_date']) > time();
        });

        $assignmentModel = new Assignment();
        $assignments = $assignmentModel->getAll($conn);
        $upcomingAssignments = array_filter($assignments, function($assignment) {
            return strtotime($assignment['start_time']) > time();
        });

        // Fetch all contests
        $contestModel = new Contest();
        $contests = $contestModel->getAll($conn);
        $upcomingContests = array_filter($contests, function($contest) {
            return strtotime($contest['start_date']) > time();
        });

        $contestModel = new Contest();
        $contests = $contestModel->getAll($conn);
        $upcomingContests = array_filter($contests, function($contest) {
            return strtotime($contest['end_date']) > time();
        });
    
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
                    $university_ids = json_decode($company['university_ids'] ?? '[]', true);
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
            header('Location: /admin/manage_company');
            exit(); // Ensure no further code is executed
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
        $university_ids = !empty($company['university_ids']) ? json_decode($company['university_ids'], true) : [];
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
    
    public function deleteFacultys() {
        $conn = Database::getConnection();
        $faculty_ids = $_POST['selected'];
    
        if (!empty($faculty_ids)) {
            $placeholders = implode(',', array_fill(0, count($faculty_ids), '?'));
            $sql = "DELETE FROM faculty WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($faculty_ids)) {
                $_SESSION['message'] = 'Selected facultys deleted successfully.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to delete selected faculty.';
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'No facultys selected for deletion.';
            $_SESSION['message_type'] = 'warning';
        }
    
        header('Location: /admin/manage_faculty');
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
        $admin_id = $_SESSION['admin']['id']; // Assuming admin ID is stored in session

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['currentPassword'] ?? null;
            $newPassword = $_POST['newPassword'] ?? null;
            $confirmPassword = $_POST['confirmPassword'] ?? null;

            // Fetch the current password from the database
            $admin = AdminModel::getById($conn, $admin_id);
            if (!$admin) {
                $message = "Admin not found.";
                $message_type = "danger";
            } elseif (!password_verify($currentPassword, $admin['password'])) {
                $message = "Current password is incorrect.";
                $message_type = "danger";
            } elseif ($newPassword !== $confirmPassword) {
                $message = "New password and re-type password do not match.";
                $message_type = "warning";
            } else {
                // Update the password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                AdminModel::updatePassword($conn, $admin_id, $hashedPassword);
                $message = "Password updated successfully.";
                $message_type = "success";
            }

            // Pass the message to the view
            require 'views/admin/updatePassword.php';
        } else {
            // If not a POST request, just show the form
            require 'views/admin/updatePassword.php';
        }
    }


    public function uploadStudents() {
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
    
                $regd_no = $row[0];
                $name = $row[1];
                $email = $row[2];
                $phone = $row[3] ?? null;
                $section = $row[4] ?? null;
                $stream = $row[5] ?? null;
                $year = $row[6] ?? null;
                $dept = $row[7] ?? null;
                $password = $row[8] ?? null;
    
                // Validate required fields
                if (empty($regd_no) || empty($name) || empty($email) || empty($password)) {
                    continue; // Skip invalid records
                }
    
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
                // Check for duplicate email or registration number
                if (Student::existsByEmail($conn, $email) || Student::existsByRegdNo($conn, $regd_no)) {
                    $duplicateRecords[] = [
                        'regd_no' => $regd_no,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'section' => $section,
                        'stream' => $stream,
                        'year' => $year,
                        'dept' => $dept,
                        'university_id' => $university_id
                    ];
                    continue;
                }
    
                // Insert student record
                $sql = "INSERT INTO students (regd_no, name, email, phone, section, stream, year, dept, university_id, password) 
                        VALUES (:regd_no, :name, :email, :phone, :section, :stream, :year, :dept, :university_id, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':regd_no' => $regd_no,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':section' => $section,
                    ':stream' => $stream,
                    ':year' => $year,
                    ':dept' => $dept,
                    ':university_id' => $university_id,
                    ':password' => $passwordHash
                ]);
    
                // Send email to the new student
                $mailer = new Mailer();
                $subject = 'Welcome to EyeBook!';
                $body = "Dear $name,<br><br>Your account has been created successfully.<br><br>Username: $email <br>Password: $password<br><br> You can log in at <a href='https://eyebook.phemesoft.com/'>https://eyebook.phemesoft.com/</a><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($email, $subject, $body);
    
                $successCount++;
            }
    
            if (empty($duplicateRecords)) {
                $message = "Students uploaded successfully.";
                $message_type = "success";
            } else {
                $message = "Some records were not uploaded due to duplicates.";
                $message_type = "warning";
            }
        }
    
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $is_public_course = isset($_POST['public_course']) ? true : false;
            $price = $is_public_course ? $_POST['price'] : null;

            if ($is_public_course) {
                $message = PublicCourse::create($conn, $name, $description, $price);
            } else {
                $message = Course::create($conn, $name, $description);
            }

            $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
        }

        require 'views/admin/add_courses.php';
    }

    public function manageCourse() {
        $conn = Database::getConnection();
        $courses = Course::getAllWithUniversity($conn);
        require 'views/admin/manage_courses.php';
    }

    public function managepublicCourse() {
        $conn = Database::getConnection();
        $courses = publicCourse::getAll($conn);
        require 'views/admin/manage_public_courses.php';
    }

    public function downloadFeedback($courseId) {
        $conn = Database::getConnection();
        $feedbacks = feedback::getFeedbackByCourseId($conn, $courseId);

        if (empty($feedbacks)) {
            die('No feedback available for this course.');
        }

        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Feedback');

        // Set the headers
        $headers = [
            'Student Name', 'Depth of Coverage', 'Emphasis on Fundamentals', 'Coverage of Modern Topics',
            'Overall Rating', 'Benefits', 'Instructor Assistance', 'Instructor Feedback', 'Motivation',
            'SME Help', 'Overall Very Good'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate the feedback data
        $row = 2;
        foreach ($feedbacks as $feedback) {
            $student = Student::getById($conn, $feedback['student_id']);
            $data = [
                $student['name'] ?? 'N/A',
                $feedback['depth_of_coverage'],
                $feedback['emphasis_on_fundamentals'],
                $feedback['coverage_of_modern_topics'],
                $feedback['overall_rating'],
                $feedback['benefits'],
                $feedback['instructor_assistance'],
                $feedback['instructor_feedback'],
                $feedback['motivation'],
                $feedback['sme_help'],
                $feedback['overall_very_good']
            ];
            $sheet->fromArray($data, NULL, 'A' . $row);
            $row++;
        }

        // Write the spreadsheet to a file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'feedback_course_' . $courseId . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $fileName;
        $writer->save($filePath);

        // Output the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);

        // Delete the temporary file
        unlink($filePath);
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
        $allCohorts = Cohort::getAll($conn); // Fetch all cohorts
        $assignedCohorts = Course::getAssignedCohorts($conn, $course_id);

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

        require 'views/admin/view_course.php';
    }

    public function viewPublicCourse($course_id) {
        $conn = Database::getConnection();
        $course = PublicCourse::getById($conn, $course_id);


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
        require 'views/admin/view_public_course.php';
    }

    public function uploadpublicEcContent() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $title = $_POST['ec_content_title'];
            $file = $_FILES['ec_content_file'];
            if (!$title || !$file) {
                echo json_encode(['message' => 'Unit name and EC Content file are required']);
                exit;
            }
            $result = PublicCourse::addEcContent($conn, $course_id, $title, $file);
            if (isset($result['indexPath'])) {
                header("Location: /admin/view_public_course/$course_id");
                exit;
            } else {
                echo json_encode(['message' => $result['message']]);
                exit;
            }
        }
    }
    
    public function uploadpublicCourseBook() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $unit_name = $_POST['unit_name'];
            $scorm_file = $_FILES['scorm_file'];

            if (!$unit_name || !$scorm_file) {
                echo json_encode(['message' => 'Unit name and SCORM package file are required']);
                exit;
            }

            // Fetch the course
            $sql = "SELECT * FROM public_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$course) {
                echo json_encode(['message' => 'Course not found']);
                exit;
            }

            // Validate SCORM package
            $zip = new ZipArchive();
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $scormVersion = 'Unknown';
                if ($zip->locateName('imsmanifest.xml') !== false) {
                    $manifest = $zip->getFromName('imsmanifest.xml');
                    if (strpos($manifest, 'ADL SCORM') !== false) {
                        if (strpos($manifest, '2004') !== false) {
                            $scormVersion = 'SCORM 2004';
                        } elseif (strpos($manifest, '1.2') !== false) {
                            $scormVersion = 'SCORM 1.2';
                        } else {
                            $scormVersion = 'Other SCORM Version';
                        }
                    } else {
                        $scormVersion = 'Non-SCORM Package';
                    }
                }
                $zip->close();

                if ($scormVersion === 'Unknown' || $scormVersion === 'Non-SCORM Package') {
                    echo json_encode(['message' => 'Invalid SCORM package']);
                    exit;
                }
            } else {
                echo json_encode(['message' => 'Failed to open SCORM package']);
                exit;
            }

            // Define the upload path
            $uploadPath = __DIR__ . '/../uploads/public_courses/' . $course_id . '/' . $unit_name;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Extract and save SCORM package locally
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $zip->extractTo($uploadPath);
                $zip->close();
            } else {
                echo json_encode(['message' => 'Failed to extract SCORM package']);
                exit;
            }

            // Get the base URL of the uploaded SCORM package
            $baseUrl = '/uploads/public_courses/' . $course_id . '/' . $unit_name . '/';

            // Update the SCORM details in the courses table
            $existingCourseBook = json_decode($course['course_book'], true);
            if (!is_array($existingCourseBook)) {
                $existingCourseBook = [];
            }
            $existingCourseBook[] = [
                'unit_name' => $unit_name,
                'scorm_url' => $baseUrl,
                'scorm_version' => $scormVersion
            ];
            $updatedCourseBook = json_encode($existingCourseBook);

            $sql = "UPDATE public_courses SET course_book = :course_book, scorm_version = :scorm_version WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'course_book' => $updatedCourseBook,
                'scorm_version' => $scormVersion,
                'id' => $course_id,
            ]);

            echo '<script>
                    alert("Unit added successfully!");
                    window.location.href = "/admin/manage_public_courses";
                </script>';
            exit;
        }
    }
    
    public function uploadpublicAdditionalContent() {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $title = $_POST['content_title'];
            $content_type = $_POST['content_type'];
    
            if ($content_type == 'link') {
                $link = $_POST['content_link'];
            } else {
                $file = $_FILES['content_file'];
                $upload_dir = 'uploads/public_additional_content/';
                $file_path = $upload_dir . basename($file['name']);
                $link = $this->uploadFileToS3($file);
            }
    
            $course = PublicCourse::getById($conn, $course_id);
            $additional_content = !empty($course['additional_content']) ? json_decode($course['additional_content'], true) : [];
    
            $new_content = [
                'title' => $title,
                'link' => $link
            ];
    
            $additional_content[] = $new_content;
    
            PublicCourse::updateAdditionalContent($conn, $course_id, $additional_content);
    
            $_SESSION['message'] = 'Additional content added successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/view_public_course/' . $course_id);
            exit();
        }
    }

    public function viewBook($hashedId) {
        if (!isset($_SESSION['admin'])) {
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
            require 'views/admin/book_view.php';
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
    
        // Ensure course_book is an array
        if (!is_array($course['EC_content'])) {
            $course['EC_content'] = json_decode($course['EC_content'], true) ?? [];
        }
    
        // Get the index_path from the query parameter
        $index_path = $_GET['index_path'] ?? $course['EC_content'][0]['indexPath'];
    
        require 'views/admin/book_view.php';
    }

    public function viewECContent($hashedId) {
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
    
        require 'views/admin/book_view.php';
    }


    // public function viewECBook($hashedId) {
    //     $conn = Database::getConnection();
    //     $course_id = base64_decode($hashedId);
    //     if (!is_numeric($course_id)) {
    //         die('Invalid course ID');
    //     }
    //     $course = Course::getById($conn, $course_id);
    
    //     if (!$course || empty($course['EC_content'])) {
    //         echo 'EC content not found.';
    //         exit;
    //     }
    
    //     // Ensure EC_content is an array
    //     $ec_content = json_decode($course['EC_content'], true);
    //     if (!is_array($ec_content)) {
    //         echo 'Invalid EC content format.';
    //         exit;
    //     }
    
    //     // Get the index_path from the query parameter
    //     $index_path = $_GET['index_path'] ?? $ec_content[0]['indexPath'];
    
    //     require 'views/admin/book_view.php';
    // }

    public function uploadSingleFaculty() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        $message = '';
        $message_type = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $university_id = $_POST['university_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone']?? null;
            $section = $_POST['section']?? null;
            $stream = $_POST['stream']?? null;
            $department = $_POST['department']?? null;
            $passwordPlain = $_POST['password']; // Store the unhashed passwor
            $password = password_hash($passwordPlain, PASSWORD_BCRYPT);
    
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

                $facultyData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'section' => $section,
                    'stream' => $stream,
                    'department' => $department,
                    'university_id' => $university_id,
                    'password' => $password
                ];
                Student::createFacultyStudentAccount($conn, $facultyData);

                $passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);
                // Send email to the new faculty member
                $mailer = new Mailer();
                $email_student = str_replace('@', '_student@', $email);
                $subject = 'Welcome to EyeBook!';
                $body = "Dear $name,<br><br>Your account has been created successfully.<br><br>Username: $email <br>Password: $passwordPlain<br><br> Student account created with email: $email <br> and password: $passwordPlain <br> You can log in at <a href='https://eyebook.phemesoft.com/'>https://eyebook.phemesoft.com/</a><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($email, $subject, $body);

    
                $message = "Faculty uploaded successfully.";
                $message_type = "success";
            }
        }
    
        require 'views/admin/uploadFaculty.php';
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
                $phone = $row[2] ?? null;
                $section = $row[3] ?? null;
                $stream = $row[4] ?? null;
                $department = $row[5] ?? null;
                $passwordPlain = $row[6];
                $password = password_hash($passwordPlain, PASSWORD_BCRYPT);
    
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

                $facultyData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'section' => $section,
                    'stream' => $stream,
                    'department' => $department,
                    'university_id' => $university_id,
                    'password' => $password
                ];
                Student::createFacultyStudentAccount($conn, $facultyData);

                // Send email to the new faculty member
                $mailer = new Mailer();
                $subject = 'Welcome to EyeBook!';
                $body = "Dear $name,<br><br>Your account has been created successfully.<br><br>Username: $email <br>Password: $passwordPlain<br><br> Student account created with email: $email <br> and password: $passwordPlain <br> You can log in at <a href='https://eyebook.phemesoft.com/'>https://eyebook.phemesoft.com/</a><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($email, $subject, $body);

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
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['course_id'];
            $unit_name = $_POST['unit_name'];
            $scorm_file = $_FILES['scorm_file'];

            if (!$unit_name || !$scorm_file) {
                echo json_encode(['message' => 'Unit name and SCORM package file are required']);
                exit;
            }

            // Fetch the course
            $sql = "SELECT * FROM courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$course) {
                echo json_encode(['message' => 'Course not found']);
                exit;
            }

            // Validate SCORM package
            $zip = new ZipArchive();
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $scormVersion = 'Unknown';
                if ($zip->locateName('imsmanifest.xml') !== false) {
                    $manifest = $zip->getFromName('imsmanifest.xml');
                    if (strpos($manifest, 'ADL SCORM') !== false) {
                        if (strpos($manifest, '2004') !== false) {
                            $scormVersion = 'SCORM 2004';
                        } elseif (strpos($manifest, '1.2') !== false) {
                            $scormVersion = 'SCORM 1.2';
                        } else {
                            $scormVersion = 'Other SCORM Version';
                        }
                    } else {
                        $scormVersion = 'Non-SCORM Package';
                    }
                }
                $zip->close();

                if ($scormVersion === 'Unknown' || $scormVersion === 'Non-SCORM Package') {
                    echo json_encode(['message' => 'Invalid SCORM package']);
                    exit;
                }
            } else {
                echo json_encode(['message' => 'Failed to open SCORM package']);
                exit;
            }

            // Define the upload path
            $uploadPath = __DIR__ . '/../uploads/courses/' . $course_id . '/' . $unit_name;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Extract and save SCORM package locally
            if ($zip->open($scorm_file['tmp_name']) === TRUE) {
                $zip->extractTo($uploadPath);
                $zip->close();
            } else {
                echo json_encode(['message' => 'Failed to extract SCORM package']);
                exit;
            }

            // Get the base URL of the uploaded SCORM package
            $baseUrl = '/uploads/courses/' . $course_id . '/' . $unit_name . '/';

            // Update the SCORM details in the courses table
            $existingCourseBook = json_decode($course['course_book'], true);
            if (!is_array($existingCourseBook)) {
                $existingCourseBook = [];
            }
            $existingCourseBook[] = [
                'unit_name' => $unit_name,
                'scorm_url' => $baseUrl,
                'scorm_version' => $scormVersion
            ];
            $updatedCourseBook = json_encode($existingCourseBook);

            $sql = "UPDATE courses SET course_book = :course_book, scorm_version = :scorm_version WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'course_book' => $updatedCourseBook,
                'scorm_version' => $scormVersion,
                'id' => $course_id,
            ]);

            echo '<script>
                    alert("Unit added successfully!");
                    window.location.href = "/admin/manage_courses";
                </script>';
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
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $university_id = $_POST['university_id'];
            $regd_no = $_POST['regd_no'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $section = $_POST['section'];
            $stream = $_POST['stream'];
            $year = $_POST['year'];
            $dept = $_POST['dept'];
            $password = $_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
            // Check for duplicate email
            if (Student::existsByEmail($conn, $email)) {
                $_SESSION['message'] = 'Duplicate entry for email: ' . $email;
                $_SESSION['message_type'] = 'warning';
                header('Location: /admin/uploadStudents');
                exit();
            }
    
            // Insert student into the database
            $sql = "INSERT INTO students (university_id, regd_no, name, email, phone, section, stream, year, dept, password) 
                    VALUES (:university_id, :regd_no, :name, :email, :phone, :section, :stream, :year, :dept, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':university_id', $university_id);
            $stmt->bindValue(':regd_no', $regd_no);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':phone', $phone);
            $stmt->bindValue(':section', $section);
            $stmt->bindValue(':stream', $stream);
            $stmt->bindValue(':year', $year);
            $stmt->bindValue(':dept', $dept);
            $stmt->bindValue(':password', $hashed_password);
    
            if ($stmt->execute()) {
                // Send email to the student
                $mailer = new Mailer();
                $subject = 'Welcome to EyeBook!';
                $body = "Dear $name,<br><br>Your account has been created successfully.<br><br>Username: $email <br>Password: $password<br><br>You can log in at <a href='https://eyebook.phemesoft.com/'>https://eyebook.phemesoft.com/</a><br><br>Best Regards,<br>EyeBook Team";
                $mailer->sendMail($email, $subject, $body);
    
                $_SESSION['message'] = 'Student uploaded successfully and email sent.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to upload student.';
                $_SESSION['message_type'] = 'danger';
            }
    
            header('Location: /admin/manage_students');
            exit();
        }
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

    public function virtual_classroom_dashboard() {
        $conn = Database::getConnection();
        $zoom = new ZoomAPI(ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET, ZOOM_ACCOUNT_ID, $conn);

        // Fetch all classrooms
        $adminClassrooms = $zoom->getAllClassrooms();

        // Fetch all courses with university short name
        $courses = Course::getAllWithUniversity($conn);

        // Sort classrooms by start_time in descending order
        usort($adminClassrooms, function($a, $b) {
            return strtotime($b['start_time']) - strtotime($a['start_time']);
        });

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? null;
            $topic = $_POST['topic'] ?? null;
            $start_time_local = $_POST['start_time'] ?? null;
            $duration = $_POST['duration'] ?? null;
            $selectedCourses = $_POST['courses'] ?? [];
            $join_url = $_POST['join_url'] ?? [];
            $classroom_id = $_POST['classroom_id'] ?? [];

            // // Convert local time to UTC and then to ISO 8601 format
            // $start_time = new \DateTime($start_time_local, new \DateTimeZone('Asia/Kolkata')); // Set the local time zone
            // $start_time_utc = clone $start_time;
            // $start_time_utc->setTimezone(new \DateTimeZone('UTC')); // Convert to UTC
            // $start_time_iso8601 = $start_time_utc->format(\DateTime::ATOM);

            if (empty($join_url)) {
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

            if ($join_url) {
                $topic = $_POST['topic'] ?? null;
                $start_time_local = $_POST['start_time'] ?? null;
                $duration = $_POST['duration'] ?? null;
                $selectedCourses = $_POST['courses'] ?? [];
                $join_url = $_POST['join_url'] ?? [];
                $classroom_id = $_POST['classroom_id'] ?? [];

                $start_time = new \DateTime($start_time_local, new \DateTimeZone('Asia/Kolkata')); // Set the local time zone
                $start_time_utc = clone $start_time;
                $start_time_utc->setTimezone(new \DateTimeZone('UTC')); // Convert to UTC
                $start_time_iso8601 = $start_time_utc->format(\DateTime::ATOM);

                // Save the start time and course IDs with the classroom in the correct format
                $stmt = $conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$classroom_id, $topic, $start_time->format('Y-m-d H:i:s'), $duration, $join_url, json_encode($selectedCourses)]);

                // Get the ID of the newly inserted entry
                $virtualClassId = $conn->lastInsertId();

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
                echo "Error creating or saving virtual classroom.";
            }
        }

        require 'views/admin/virtual_classroom_dashboard.php';
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
    public function editPublicCourse($course_id) {
        $conn = Database::getConnection();
        $message = '';
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
    
            $sql = "UPDATE public_courses SET name = ?, description = ?, price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $price, $course_id]);
    
            $message = "Public course updated successfully!";
        }
    
        $course = PublicCourse::getById($conn, $course_id);
        require 'views/admin/edit_public_course.php';
    }

    public function deleteCourse($course_id) {
        $conn = Database::getConnection();

        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id]);

        header('Location: /admin/manage_courses');
        exit();
    }

    public function deletePublicCourse($course_id) {
        $conn = Database::getConnection();

        $sql = "DELETE FROM public_courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id]);

        header('Location: /admin/manage_public_courses');
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
        
        if (!file_exists('views/admin/manage_assignments.php')) {
            throw new Exception("View file not found: manage_assignments.php");
        }
        
        require 'views/admin/manage_assignments.php';
    }

    public function editAssignment($id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $id);
        $courses = Course::getAll($conn);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_time = $_POST['start_time'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $file_content = !empty($_FILES['file_content']['tmp_name']) ? file_get_contents($_FILES['file_content']['tmp_name']) : $assignment['file_content'];
    
            Assignment::update($conn, $id, $title, $description, $start_time, $due_date, $course_ids, $file_content);
    
            header('Location: /admin/manage_assignments');
            exit;
        }
    
        require 'views/admin/edit_assignment.php';
    }
    
    public function editPublicAssignment($id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $id);
        $courses = PublicCourse::getAll($conn);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_time = $_POST['start_time'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $file_content = !empty($_FILES['file_content']['tmp_name']) ? file_get_contents($_FILES['file_content']['tmp_name']) : $assignment['file_content'];
    
            Assignment::update($conn, $id, $title, $description, $start_time, $due_date, $course_ids, $file_content);
    
            header('Location: /admin/manage_assignments');
            exit;
        }
    
        require 'views/admin/edit_public_assignment.php';
    }

    public function deleteAssignment($assignmentId) {
        $conn = Database::getConnection();

        // Fetch the assignment details to get the course IDs
        $assignment = Assignment::getById($conn, $assignmentId);
        $courseIds = json_decode($assignment['course_id'], true);

        // Delete the assignment
        Assignment::delete($conn, $assignmentId);

        // Update the assignments column in the courses table
        foreach ($courseIds as $courseId) {
            $course = Course::getById($conn, $courseId);
            $assignments = json_decode($course['assignments'], true);
            $updatedAssignments = array_diff($assignments, [$assignmentId]);
            Course::updateAssignments($conn, $courseId, json_encode(array_values($updatedAssignments)));
        }

        $_SESSION['message'] = 'Assignment deleted successfully.';
        $_SESSION['message_type'] = 'success';

        header('Location: /admin/manage_assignments');
        exit();
    }

    public function createAssignment() {
        $conn = Database::getConnection();
        $courses = Course::getAll($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['assignment_title'];
            $description = $_POST['assignment_description'];
            $start_date = $_POST['start_date'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $file_content = null;

            if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
                $file_content = file_get_contents($_FILES['assignment_file']['tmp_name']);
            }

            $assignment_id = Assignment::create($conn, $title, $description, $start_date, $due_date, $course_ids, $file_content);

            foreach ($course_ids as $course_id) {
                Course::addAssignmentToCourse($conn, $course_id, $assignment_id);
            }

            $_SESSION['message'] = 'Assignment created successfully.';
            $_SESSION['message_type'] = 'success';

            header('Location: /admin/view_course/'.$course_ids[0]);
            exit();
        }

        require 'views/admin/assignment_create.php';
    }

    public function createPublicAssignment() {
        $conn = Database::getConnection();
        $courses = Course::getAll($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['assignment_title'];
            $description = $_POST['assignment_description'];
            $start_date = $_POST['start_date'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $file_content = null;

            if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
                $file_content = file_get_contents($_FILES['assignment_file']['tmp_name']);
            }

            $assignment_id = Assignment::createpublic($conn, $title, $description, $start_date, $due_date, $course_ids, $file_content);

            foreach ($course_ids as $course_id) {
                PublicCourse::addAssignmentToCourse($conn, $course_id, $assignment_id);
            }

            $_SESSION['message'] = 'Assignment created successfully.';
            $_SESSION['message_type'] = 'success';

            header('Location: /admin/view_public_course/'.$course_ids[0]);
            exit();
        }

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

    public function archivePublicCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['archive_course_id'];
            PublicCourse::archiveCourse($conn, $course_id);

            $_SESSION['message'] = 'Public course archived successfully.';
            $_SESSION['message_type'] = 'success';

            header('Location: /admin/manage_public_courses');
            exit();
        }
    }

    public function unarchivePublicCourse() {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $course_id = $_POST['archive_course_id'];
            PublicCourse::unarchiveCourse($conn, $course_id);

            $_SESSION['message'] = 'Public course unarchived successfully.';
            $_SESSION['message_type'] = 'success';

            header('Location: /admin/manage_public_courses');
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

    public function togglePublicFeedback() {
        $conn = Database::getConnection();
        $course_id = $_POST['course_id'];
        $feedback_enabled = $_POST['enabled'] === 'true' ? 1 : 0;

        PublicCourse::updateFeedbackStatus($conn, $course_id, $feedback_enabled);

        header('Location: /admin/view_public_course/' . $course_id);
        exit();
    }

    public function viewAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        $course_id = json_decode($assignment['course_id'], true)[0];

        require 'views/admin/view_assignment.php';
    }

    public function viewPublicAssignment($assignment_id) {
        $conn = Database::getConnection();
        $assignment = Assignment::getById($conn, $assignment_id);
        $submissions = Assignment::getSubmissions($conn, $assignment_id);

        $course_id = json_decode($assignment['course_id'], true)[0];

        require 'views/admin/view_public_assignment.php';
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

    public function gradeSubmission($assignment_id, $student_id) {
        $conn = Database::getConnection();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $grade = $_POST['grade'];
            $feedback = $_POST['feedback'];
            Assignment::grade($conn, $assignment_id, $student_id, $grade, $feedback);
            header('Location: /admin/view_assignment/' . $assignment_id);
            exit;
        }
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

    public function viewCohort($cohort_id) {
        $conn = Database::getConnection();
    
        // Get cohort details
        $cohort = Cohort::getById($conn, $cohort_id);
    
        // Get student IDs from cohort
        $student_ids = json_decode($cohort['student_ids'], true) ?? [];
    
        // Get student details
        $students = !empty($student_ids) ? Student::getByIds($conn, $student_ids) : [];
    
        // Get other necessary data
        $universities = University::getAll($conn);
        $courses = Course::getAll($conn);
        $allCourses = Course::getAll($conn);
        $allStudents = Student::getAll($conn);
        $existing_student_ids = $student_ids;

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
    
        require 'views/admin/view_cohort.php';
    }

    public function assignCohortToCourse() {
        $conn = Database::getConnection();
        $courseId = $_POST['course_id'];
        $cohortIds = $_POST['cohort_ids'] ?? [];

        foreach ($cohortIds as $cohortId) {
            Cohort::addCourse($conn, $cohortId, $courseId);

            // Get student IDs from cohort
            $cohort = Cohort::getById($conn, $cohortIds);
            $student_ids = json_decode($cohort['student_ids'], true) ?? [];

            // Assign course to students
            Student::assignCourseToStudents($conn, $student_ids, $courseId);

            // Assign students to course
            Course::assignStudentsToCourse($conn, $courseId, $student_ids);
        }

        $_SESSION['message'] = 'Cohorts assigned successfully.';
        $_SESSION['message_type'] = 'success';

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    public function unassignCohortFromCourse() {
        $conn = Database::getConnection();
        $courseId = $_POST['course_id'];
        $cohortIds = $_POST['cohort_ids'] ?? [];

        foreach ($cohortIds as $cohortId) {
            Cohort::unassignCourse($conn, $cohortId, $courseId);

            $student_ids = json_decode($cohortId['student_ids'], true) ?? [];
    
        // Unassign course from students
        Student::unassignCourseFromStudents($conn, $student_ids, $courseId);
    
        // Unassign students from course
        Course::unassignStudentsFromCourse($conn, $courseId, $student_ids);
    
        }

        $_SESSION['message'] = 'Cohorts unassigned successfully.';
        $_SESSION['message_type'] = 'success';

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
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

    public function removePublicContent() {
        $conn = Database::getConnection();
        $type = $_POST['type'];
        $index = $_POST['index'];
        $course_id = $_POST['course_id'];

        // Fetch the course data
        $sql = "SELECT * FROM public_courses WHERE id = :course_id";
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
        $sql = "UPDATE public_courses SET $type = :content WHERE id = :course_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':content', $jsonContent, PDO::PARAM_STR);
        $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Content removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove content']);
        }
    }
    public function askguru(){
        require 'views/admin/askguru.php';
    }
    public function createLab() {
        $conn = Database::getConnection();
        $courses = Course::getAll($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['lab_title'];
            $description = $_POST['lab_description'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $input = $_POST['input'];
            $output = $_POST['output'];
            $submissions = []; // Initialize submissions as an empty array

            Lab::create($conn, $title, $description, $due_date, $course_ids, $input, $output, $submissions);
            header('Location: /admin/manage_labs');
            exit;
        }

        require 'views/admin/lab_create.php';
    }

    public function createPublicLab() {
        $conn = Database::getConnection();
        $courses = PublicCourse::getAll($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['lab_title'];
            $description = $_POST['lab_description'];
            $course_ids = $_POST['course_id'];
            $input = $_POST['input'];
            $output = $_POST['output'];
            $submissions = []; // Initialize submissions as an empty array

            PublicLab::create($conn, $title, $description, $course_ids, $input, $output, $submissions);
            header('Location: /admin/manage_public_labs');
            exit;
        }

        require 'views/admin/lab_public_create.php';
    }

    public function manageLabs() {
        $conn = Database::getConnection();
        $courses = Course::getAllWithUniversity($conn); // Fetch all courses with university details
        require 'views/admin/manage_labs.php';
    }
    public function managePublicLabs() {
        $conn = Database::getConnection();
        $courses = PublicCourse::getAll($conn); // Fetch all courses with university details
        require 'views/admin/manage_public_labs.php';
    }

    public function viewLabsByCourse($course_id) {
        $conn = Database::getConnection();
        $labs = Lab::getByCourseId($conn, $course_id);
        $course = Course::getById($conn, $course_id);
        require 'views/admin/view_labs_by_course.php';
    }

    public function viewLabsByPublicCourse($course_id) {
        $conn = Database::getConnection();
        $labs = PublicLab::getByCourseId($conn, $course_id);
        $course = PublicCourse::getById($conn, $course_id);
        require 'views/admin/view_public_labs_by_course.php';
    }

    public function editLab($lab_id) {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $due_date = $_POST['due_date'];
            $course_ids = $_POST['course_id'];
            $input = $_POST['input'];
            $output = $_POST['output'];
    
            $sql = "UPDATE labs SET title = :title, description = :description, due_date = :due_date, course_id = :course_id, input = :input, output = :output WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':due_date' => $due_date,
                ':course_id' => json_encode($course_ids),
                ':input' => $input,
                ':output' => $output,
                ':id' => $lab_id
            ]);
    
            $_SESSION['message'] = 'Lab updated successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/view_labs_by_course/' . $course_ids[0]);
            exit();
        }
    
        $lab = Lab::getById($conn, $lab_id);
        $courses = Course::getAll($conn);
        $course_ids = json_decode($lab['course_id'], true);
    
        require 'views/admin/edit_lab.php';
    }

    public function editPublicLab($lab_id) {
        $conn = Database::getConnection();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $course_ids = $_POST['course_id'];
            $input = $_POST['input'];
            $output = $_POST['output'];
    
            $sql = "UPDATE public_labs SET title = :title, description = :description, course_id = :course_id, input = :input, output = :output WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':course_id' => json_encode($course_ids),
                ':input' => $input,
                ':output' => $output,
                ':id' => $lab_id
            ]);
    
            $_SESSION['message'] = 'Lab updated successfully.';
            $_SESSION['message_type'] = 'success';
    
            header('Location: /admin/view_public_labs_by_course/' . $course_ids[0]);
            exit();
        }
    
        $lab = PublicLab::getById($conn, $lab_id);
        $courses = PublicCourse::getAll($conn);
        $course_ids = json_decode($lab['course_id'], true);
    
        require 'views/admin/edit_public_lab.php';
    }
    
    public function deleteLab($lab_id) {
        $conn = Database::getConnection();
        $lab = Lab::getById($conn, $lab_id);
        $course_ids = json_decode($lab['course_id'], true);
    
        $sql = "DELETE FROM labs WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $lab_id]);
    
        $_SESSION['message'] = 'Lab deleted successfully.';
        $_SESSION['message_type'] = 'success';
    
        header('Location: /admin/view_labs_by_course/' . $course_ids[0]);
        exit();
    }
    public function deletePublicLab($lab_id) {
        $conn = Database::getConnection();
        $lab = PublicLab::getById($conn, $lab_id);
        $course_ids = json_decode($lab['course_id'], true);
    
        $sql = "DELETE FROM public_labs WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $lab_id]);
    
        $_SESSION['message'] = 'Lab deleted successfully.';
        $_SESSION['message_type'] = 'success';
    
        header('Location: /admin/view_labs_by_public_course/' . $course_ids[0]);
        exit();
    }

    public function viewLabDetail($labId) {
        $conn = Database::getConnection();
        if (!is_numeric($labId)) {
            die('Invalid lab ID');
        }

        $lab = Lab::getById($conn, $labId);
        $lab['submissions'] = Lab::getSubmissions($conn, $labId);

        require 'views/admin/view_lab_detail.php';
    }

    public function viewPublicLabDetail($labId) {
        $conn = Database::getConnection();
        if (!is_numeric($labId)) {
            die('Invalid lab ID');
        }

        $lab = PublicLab::getById($conn, $labId);
        $lab['submissions'] = PublicLab::getSubmissions($conn, $labId);

        require 'views/admin/view_public_lab_detail.php';
    }
    public function downloadLabReport($labId) {
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

    public function downloadPublicLabReport($labId) {
        $conn = Database::getConnection();
        $submissions = publicLab::getSubmissions($conn, $labId);

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
    public function createContest() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        require 'views/admin/create_contest.php';
    }

    public function manageContest() {
        $conn = Database::getConnection();
        $contests = Contest::getAll($conn);
        $universities = University::getAll($conn);
        require 'views/admin/manage_contest.php';
    }

    public function saveContest() {
        $conn = Database::getConnection();
        $data = $_POST;
        $data['university_id'] = json_encode($data['university_id']); // Encode the university IDs as JSON
        Contest::create($conn, $data);
        header('Location: /admin/manage_contest');
    }

    public function viewContest($contestId) {
        $conn = Database::getConnection();
        $contest = Contest::getById($conn, $contestId);
        $questions = Contest::getQuestions($conn, $contestId);
        $leaderboard = Contest::getLeaderboard($conn, $contestId);
        require 'views/admin/view_contest.php';
    }
    public function viewContestsByUniversity($university_id) {
        $conn = Database::getConnection();
        $contests = Contest::getByUniversityId($conn, $university_id);
        $university = University::getById($conn, $university_id);
    
        require 'views/admin/view_contests_by_university.php';
    }

    public function editContest($contestId) {
        $conn = Database::getConnection();
        $contest = Contest::getById($conn, $contestId);
        $universities = University::getAll($conn);
        require 'views/admin/edit_contest.php';
    }

    public function updateContest($contestId) {
        $conn = Database::getConnection();
        $data = $_POST;
        $data['university_id'] = json_encode($data['university_id']); // Encode the university IDs as JSON
        Contest::update($conn, $contestId, $data);
        header('Location: /admin/view_contest/' . $contestId);
    }

    public function deleteContest($contestId) {
        $conn = Database::getConnection();
        Contest::delete($conn, $contestId);
        header('Location: /admin/manage_contest');
    }

    public function addQuestions($contestId) {
        $conn = Database::getConnection();
        $contest = Contest::getById($conn, $contestId);
        require 'views/admin/add_question.php';
    }

    public function saveQuestion($contestId) {
        $conn = Database::getConnection();
        $data = $_POST;
        Contest::addQuestion($conn, $contestId, $data);
        header('Location: /admin/view_contest/' . $contestId);
    }
    public function viewQuestion($questionId) {
        $conn = Database::getConnection();
        $question = Contest::getQuestionById($conn, $questionId);
        require 'views/admin/view_question.php';
    }

    public function editQuestion($questionId) {
        $conn = Database::getConnection();
        $question = Contest::getQuestionById($conn, $questionId);
        require 'views/admin/edit_question.php';
    }

    public function updateQuestion($questionId) {
        $conn = Database::getConnection();
        $data = $_POST;
        Contest::updateQuestion($conn, $questionId, $data);
        $question = Contest::getQuestionById($conn, $questionId);
        header('Location: /admin/view_contest/' . $question['contest_id']);
    }

    public function deleteQuestion($questionId) {
        $conn = Database::getConnection();
        Contest::deleteQuestion($conn, $questionId);

        $_SESSION['message'] = 'Question deleted successfully.';
        $_SESSION['message_type'] = 'success';

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    public function bulkAddStudentsToCohort($cohort_id) {
        $conn = Database::getConnection();
        $cohort = Cohort::getById($conn, $cohort_id);
        $existing_student_ids = json_decode($cohort['student_ids'], true) ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bulk_student_file'])) {
            $file = $_FILES['bulk_student_file']['tmp_name'];
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header row
            array_shift($rows);

            // Get all email addresses from the sheet
            $sheetEmails = array_map(function($row) {
                return trim($row[0] ?? '');
            }, $rows);
            $sheetEmails = array_filter($sheetEmails); // Remove empty values

            // Find duplicates within the sheet
            $duplicatesInSheet = array_diff_assoc($sheetEmails, array_unique($sheetEmails));
            
            // Get valid emails (excluding duplicates from sheet)
            $validEmails = array_unique($sheetEmails);
            
            // Query database for these emails
            if (!empty($validEmails)) {
                $placeholders = str_repeat('?,', count($validEmails) - 1) . '?';
                $sql = "SELECT id, email FROM students WHERE email IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->execute($validEmails);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $foundEmails = array_column($students, 'email');
                $notFoundEmails = array_diff($validEmails, $foundEmails);
                
                // Get emails of students already in cohort
                $existingEmails = [];
                if (!empty($existing_student_ids)) {
                    $existingStudentsSql = "SELECT id, email FROM students WHERE id IN (" . implode(',', array_map('intval', $existing_student_ids)) . ")";
                    $existingStmt = $conn->prepare($existingStudentsSql);
                    $existingStmt->execute();
                    $existingStudents = $existingStmt->fetchAll(PDO::FETCH_ASSOC);
                    $existingEmails = array_column($existingStudents, 'email');
                }

                // Check for existing emails in the sheet
                $existingEmailsInSheet = array_intersect($validEmails, $existingEmails);
                
                $studentIdsToAdd = array_column($students, 'id');
                // Count only new students (not already in cohort)
                $newStudents = array_diff($studentIdsToAdd, $existing_student_ids);
                $actuallyAdded = count($newStudents);

                $studentIdsToAdd = array_map('strval', $studentIdsToAdd);
                $existing_student_ids = array_map('strval', $existing_student_ids);
                $newStudentIds = array_values(array_unique(array_merge($existing_student_ids, $studentIdsToAdd)));
                sort($newStudentIds);

                Cohort::updateStudentIds($conn, $cohort_id, $newStudentIds);

                $course_ids = json_decode($cohort['course_ids'], true) ?? [];
                Student::assignCoursesToStudents($conn, $newStudentIds, $course_ids);

                foreach ($course_ids as $course_id) {
                    Course::assigncohortstudents($conn, $course_id, $newStudentIds);
                }

                $_SESSION['bulk_add_result'] = [
                    'success' => true,
                    'added_count' => $actuallyAdded,
                    'total_processed' => count($validEmails),
                    'duplicates_in_sheet' => array_values($duplicatesInSheet),
                    'duplicates_count' => count($duplicatesInSheet),
                    'not_found_emails' => array_values($notFoundEmails),
                    'not_found_count' => count($notFoundEmails),
                    'existing_emails' => array_values($existingEmailsInSheet),
                    'existing_count' => count($existingEmailsInSheet)
                ];
            } else {
                $_SESSION['bulk_add_result'] = [
                    'success' => false,
                    'message' => 'No valid emails found in the sheet.'
                ];
            }

            header("Location: /admin/view_cohort/$cohort_id");
            exit();
        }
    }

    // Add these methods to the existing AdminController class

    public function tickets() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        $conn = Database::getConnection();
        
        // Get all active and closed tickets across all universities
        $activeTickets = Ticket::getAllTickets($conn, 'active');
        $closedTickets = Ticket::getAllTickets($conn, 'closed');
        
        require 'views/admin/tickets.php';
    }

    public function getTicketDetails($ticketId) {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        $conn = Database::getConnection();
        
        // Get ticket details
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        
        // Admin can always close tickets
        $ticket['canClose'] = true;
        
        echo json_encode($ticket);
    }

    public function addTicketReply() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }
        
        $conn = Database::getConnection();
        $adminId = $_SESSION['admin_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        
        if (!$ticketId || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        // Verify ticket is active
        $ticket = Ticket::getTicketDetails($conn, $ticketId);
        if ($ticket['ticket']['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Ticket is closed']);
            exit;
        }
        
        $success = Ticket::addReply($conn, $ticketId, $adminId, 'admin', $message);
        echo json_encode(['success' => $success]);
    }

    public function closeTicket() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }
        
        $conn = Database::getConnection();
        $adminId = $_SESSION['admin_id'];
        
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        
        if (!$ticketId) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
            exit;
        }
        
        // Admin can close any ticket without needing to reply first
        $success = Ticket::closeTicket($conn, $ticketId, $adminId, 'admin');
        echo json_encode(['success' => $success]);
    }

    public function exportTickets() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        $conn = Database::getConnection();
        $status = $_GET['status'] ?? 'all';
        
        // Get tickets based on status
        if ($status === 'active') {
            $tickets = Ticket::getAllTickets($conn, 'active');
        } elseif ($status === 'closed') {
            $tickets = Ticket::getAllTickets($conn, 'closed');
        } else {
            $tickets = array_merge(
                Ticket::getAllTickets($conn, 'active'),
                Ticket::getAllTickets($conn, 'closed')
            );
        }
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Ticket #');
        $sheet->setCellValue('B1', 'Student');
        $sheet->setCellValue('C1', 'University');
        $sheet->setCellValue('D1', 'Subject');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Created');
        $sheet->setCellValue('G1', 'Closed');
        $sheet->setCellValue('H1', 'Replies');
        
        // Add data
        $row = 2;
        foreach ($tickets as $ticket) {
            $sheet->setCellValue('A' . $row, $ticket['ticket_number']);
            $sheet->setCellValue('B' . $row, $ticket['student_name']);
            $sheet->setCellValue('C' . $row, $ticket['university_name']);
            $sheet->setCellValue('D' . $row, $ticket['subject']);
            $sheet->setCellValue('E' . $row, $ticket['status']);
            $sheet->setCellValue('F' . $row, date('Y-m-d H:i', strtotime($ticket['created_at'])));
            $sheet->setCellValue('G' . $row, $ticket['closed_at'] ? date('Y-m-d H:i', strtotime($ticket['closed_at'])) : 'N/A');
            $sheet->setCellValue('H' . $row, $ticket['reply_count']);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="tickets_report.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Save to output
        $writer->save('php://output');
        exit;
    }

    public function ticketAnalytics() {
        if (!isset($_SESSION['admin'])) {
            header('Location: /login');
            exit();
        }
        
        $conn = Database::getConnection();
        
        // Get ticket statistics
        $stats = [
            'total' => Ticket::getCount($conn),
            'active' => Ticket::getCount($conn, 'active'),
            'closed' => Ticket::getCount($conn, 'closed'),
            'response_time' => Ticket::getAverageResponseTime($conn),
            'resolution_time' => Ticket::getAverageResolutionTime($conn),
            'by_university' => Ticket::getCountByUniversity($conn),
            'by_month' => Ticket::getCountByMonth($conn, 6), // Last 6 months
        ];
        
        require 'views/admin/ticket_analytics.php';
    }
}
?>
