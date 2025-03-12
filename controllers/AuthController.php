<?php
namespace Controllers;

use Models\Admin;
use Models\Spoc;
use Models\Faculty;
use Models\Student;
use Models\Database;
use Models\University;

class AuthController {
    private $adminModel;
    private $spocModel;
    private $facultyModel;
    private $studentModel;

    public function __construct() {
        $conn = Database::getConnection();
        $this->adminModel = new Admin($conn);
        $this->spocModel = new Spoc($conn);
        $this->facultyModel = new Faculty($conn);
        $this->studentModel = new Student($conn);
    }

    private function checkExistingSession() {
        if (isset($_SESSION['admin'])) {
            return ['role' => 'admin', 'name' => $_SESSION['admin']['name'] ?? 'Administrator'];
        }
        if (isset($_SESSION['email'])) {
            if (isset($_SESSION['faculty_id'])) {
                return ['role' => 'faculty', 'name' => $_SESSION['email']];
            }
            if (isset($_SESSION['student_id'])) {
                return ['role' => 'student', 'name' => $_SESSION['email']];
            }
            if (isset($_SESSION['spoc_id'])) {
                return ['role' => 'spoc', 'name' => $_SESSION['email']];
            }
        }
        return null;
    }

    public function showStudentRegisterForm() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        require 'views/studentRegisterView.php';
    }

    public function registerStudent() {
        $conn = Database::getConnection();
        $universities = University::getAll($conn);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $conn = Database::getConnection();
        
                if ($_POST['university_id'] === 'other') {
                    // Create new university
                    $universityData = [
                        'long_name' => $_POST['long_name'],
                        'short_name' => $_POST['short_name'],
                        'location' => $_POST['location'],
                        'country' => $_POST['country']
                    ];
                    $result = University::addUniversity($conn, $universityData['long_name'], $universityData['short_name'], $universityData['location'], $universityData['country'], null, null, null, null, null);
                    if ($result['message_type'] === 'error') {
                        $errorMessage = $result['message'];
                        require 'views/studentRegisterView.php';
                        return;
                    }
                    $university_id = $result['university_id'];
                } else {
                    $university_id = $_POST['university_id'];
                }
        
                $data = [
                    'regd_no' => $_POST['regd_no'],
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'section' => $_POST['section'],
                    'stream' => $_POST['stream'],
                    'year' => $_POST['year'],
                    'dept' => $_POST['dept'],
                    'university_id' => $university_id,
                    'password' => $_POST['password']
                ];
        
                $result = Student::create($conn, $data);
                
                if (isset($result['duplicate']) && $result['duplicate']) {
                    $errorMessage = "A student with this registration number or email already exists.";
                    require 'views/studentRegisterView.php';
                    return;
                }
    
                // Get university details for email
                $university = University::getById($conn, $university_id);
                
                // Send registration confirmation email
                $mailer = new \Models\Mailer();
                $emailBody = $this->getRegistrationEmailBody($data, $university);
                
                try {
                    $mailer->sendMail(
                        $data['email'],
                        'Welcome to EyeBook - Registration Successful',
                        $emailBody
                    );
                    
                    header('Location: /login');
                    exit();
                } catch (\Exception $e) {
                    // Log the error but don't show it to the user
                    error_log("Failed to send registration email: " . $e->getMessage());
                    header('Location: /login');
                    exit();
                }
            } catch (\Exception $e) {
                $errorMessage = "An error occurred during registration. Please try again.";
                require 'views/studentRegisterView.php';
                return;
            }
        }
        require 'views/studentRegisterView.php';
    }
    
    private function getRegistrationEmailBody($data, $university) {
        return "
        <html>
        <head>
            <style>
                .button {
                    background-color: #6B46C1;
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 5px;
                    display: inline-block;
                    margin: 15px 0;
                    font-weight: bold;
                }
                .button:hover {
                    background-color: #553C9A;
                }
                .footer {
                    border-top: 1px solid #E2E8F0;
                    padding-top: 20px;
                    margin-top: 30px;
                }
            </style>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; margin: 0; padding: 0;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <!-- Header with Logo -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <img src='https://i.ibb.co/7xL13b10/knowbots-logo.png' alt='Knowbots Logo' style='max-width: 200px; height: auto;'>
                </div>
                
                <!-- Welcome Message -->
                <div style='background-color: #6B46C1; color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;'>
                    <h1 style='margin: 0; text-align: center; font-size: 24px;'>Welcome to Knowbots!</h1>
                </div>
                
                <p style='font-size: 16px;'>Dear {$data['name']},</p>
                
                <p style='font-size: 16px;'>Thank you for joining Knowbots! Your account has been successfully created, and you're now part of our growing educational community.</p>
                
                <!-- Account Details Box -->
                <div style='background-color: #F7FAFC; padding: 25px; border-radius: 10px; border-left: 4px solid #6B46C1; margin: 25px 0;'>
                    <h3 style='color: #6B46C1; margin-top: 0;'>Your Account Details</h3>
                    <ul style='list-style: none; padding: 0; margin: 0;'>
                        <li style='margin-bottom: 10px;'><strong>User Name:</strong> {$data['regd_no']}</li>
                        <li style='margin-bottom: 10px;'><strong>Email:</strong> {$data['email']}</li>
                        <li style='margin-bottom: 10px;'><strong>University:</strong> {$university['long_name']}</li>
                        <li style='margin-bottom: 10px;'><strong>Department:</strong> {$data['dept']}</li>
                    </ul>
                </div>
                
                <!-- Call to Action -->
                <div style='text-align: center;'>
                    <a href='https://eyebook.phemesoft.com/login' class='button' style='background-color: #6B46C1; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; font-weight: bold;'>
                        Login to Your Account
                    </a>
                </div>
                
                <!-- Security Notice -->
                <div style='background-color: #FFF5F5; padding: 20px; border-radius: 10px; margin: 25px 0;'>
                    <p style='color: #C53030; margin: 0;'><strong>⚠️ Security Recommendation:</strong> Please change your password after your first login for enhanced security.</p>
                </div>
                
                <p style='font-size: 16px;'>If you have any questions or need assistance, our support team is here to help!</p>
                
                <!-- Footer -->
                <div class='footer' style='text-align: center; color: #718096; font-size: 14px;'>
                    <p>© 2025 Knowbots. All rights reserved.</p>
                    <p>
                        <a href='https://eyebook.phemesoft.com/' style='color: #6B46C1; text-decoration: none; margin: 0 10px;'>Terms of Service</a> | 
                        <a href='https://eyebook.phemesoft.com/#privacy-policy' style='color: #6B46C1; text-decoration: none; margin: 0 10px;'>Privacy Policy</a>
                    </p>
                    <p style='margin-top: 15px;'>
                        This is an automated message, please do not reply to this email.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function clearSession() {
        $_SESSION = array();
        session_destroy();
        session_start();
    }

    public function login() {
        $message = '';
        $warning = '';
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Check for existing session
                $existingSession = $this->checkExistingSession();
                if ($existingSession) {
                    $warning = "Note: You are currently logged in as {$existingSession['name']} ({$existingSession['role']}). Proceeding will end that session.";
                }

                // Check admin credentials
                $admin = $this->adminModel->login($username, $password);
                if ($admin) {
                    $this->clearSession();
                    $_SESSION['admin'] = $admin;
                    $_SESSION['admin_id'] = $admin['id'];
                    $this->adminModel->updateLastLogin($admin['id']);
                    header('Location: /admin/dashboard');
                    exit();
                }
    
                // Check spoc credentials
                $spoc = $this->spocModel->login($username, $password);
                if ($spoc) {
                    $this->clearSession();
                    $_SESSION['email'] = $username;
                    $this->spocModel->updateLoginDetails($spoc['id']);
                    header('Location: /spoc/dashboard');
                    exit();
                }

                // Check faculty credentials
                $faculty = $this->facultyModel->login($username, $password);
                if ($faculty) {
                    $this->clearSession();
                    $_SESSION['faculty_id'] = $faculty['id'];
                    $_SESSION['email'] = $username;
                    $this->facultyModel->updateLoginDetails($faculty['id']);
                    header('Location: /faculty/dashboard');
                    exit();
                }

                // Check student credentials
                $student = $this->studentModel->login($username, $password);
                if ($student) {
                    $this->clearSession();
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['email'] = $username;
                    $this->studentModel->updateLoginDetails($student['id']);
                    header('Location: /student/dashboard');
                    exit();
                }
    
                $message = 'Invalid username or password';
            } else {
                $message = 'Username and password are required';
            }
        }
    
        require 'views/index.php';
    }

    public function forceResetPassword() {
        if (!isset($_SESSION['force_reset_password']) || !$_SESSION['force_reset_password']) {
            header('Location: /login');
            exit();
        }
        require 'views/force_reset_password.php';
    }
    
    public function handleForceResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
    
            if ($newPassword !== $confirmPassword) {
                $message = 'Passwords do not match.';
                require 'views/force_reset_password.php';
                return;
            }
    
            $userId = $_SESSION['user_id'];
            $userType = $_SESSION['user_type'];
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $conn = Database::getConnection();
            if ($userType === 'student') {
                Student::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'students';
            } elseif ($userType === 'faculty') {
                Faculty::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'faculty';
            } elseif ($userType === 'spoc') {
                Spoc::updatePassword($conn, $userId, $hashedPassword);
                $tableName = 'spocs';
            }
    
            // Update the first_login and login_count fields
            $sql = "UPDATE $tableName SET first_login = NOW(), login_count = login_count + 1 WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $userId]);
    
            if ($stmt->rowCount() > 0) {
                unset($_SESSION['force_reset_password']);
                $_SESSION['password_reset_success'] = true;
    
                echo "<script>
                        alert('Password reset successful. Please log in again.');
                        window.location.href = '/login';
                      </script>";
            } else {
                echo "<script>
                        alert('Failed to update login count. Please contact support.');
                        window.location.href = '/login';
                      </script>";
            }
            exit();
        }
    }

    public function logout() {
        $this->clearSession();
        header('Location: /');
        exit();
    }

    public function checkAuth() {
        if (!isset($_SESSION['email']) && !isset($_SESSION['admin'])) {
            header("Location: /session-timeout");
            exit;
        }
    }
}