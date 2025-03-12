<?php
namespace Models;

use PDO;

class Spoc {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as spoc_count FROM spocs";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['spoc_count'] ?? 0;
    }

    public function updateLoginDetails($spoc_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE spocs SET 
                    last_login = NOW(), 
                    login_count = login_count + 1, 
                    first_login = IF(first_login IS NULL, NOW(), first_login) 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$spoc_id]);
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM spocs";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllWithUniversity($conn) {
        $sql = "SELECT spocs.*, universities.short_name AS university_short_name
                FROM spocs
                LEFT JOIN universities ON spocs.university_id = universities.id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByEmail($conn, $email) {
        $sql = "SELECT * FROM spocs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function existsByEmail($conn, $email) {
        $sql = "SELECT COUNT(*) as count FROM spocs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM spocs WHERE email = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $spoc = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($spoc && password_verify($password, $spoc['password'])) {
            $_SESSION['email'] = $spoc['email']; // Set email in session
            $_SESSION['user_id'] = $spoc['id'];
            $_SESSION['user_type'] = 'spoc';
            if (is_null($spoc['first_login']) || $spoc['login_count'] == 0) {
                $_SESSION['force_reset_password'] = true;
                header('Location: /force_reset_password');
                exit();
            }
            return $spoc;
        }
    
        return false;
    }

    public function getUserProfile() {
        $query = 'SELECT * FROM spocs WHERE email = :email';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['email' => $_SESSION['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public static function updatePassword($conn, $spoc_id, $new_password) {
        $sql = "UPDATE spocs SET password = :new_password WHERE id = :spoc_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':new_password' => $new_password,
            ':spoc_id' => $spoc_id
        ]);
    }

    public function getUserData($email) {
        $sql = "SELECT * FROM spocs WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserDataspoc($email) {
        $sql = "SELECT spocs.*, universities.long_name AS university_name 
                FROM spocs 
                JOIN universities ON spocs.university_id = universities.id 
                WHERE spocs.email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserData($email, $name, $newEmail, $phone) {
        $sql = "UPDATE spocs SET name = :name, email = :newEmail, phone = :phone";
        $sql .= " WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':newEmail', $newEmail);
        $stmt->bindValue(':phone', $phone);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
    }

    public function getFacultyCount($university_id) {
        $sql = "SELECT COUNT(*) as faculty_count FROM faculty WHERE university_id = :university_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['faculty_count'];
    }

    public function getStudentCount($university_id) {
        $sql = "SELECT COUNT(*) as student_count FROM students WHERE university_id = :university_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['student_count'];
    }

    public function getSpocCount() {
        $sql = "SELECT COUNT(*) as spoc_count FROM spocs";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['spoc_count'];
    }

    public function getCourseCount() {
        $sql = "SELECT COUNT(*) as course_count FROM courses";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['course_count'];
    }

    public function getAllSpocs() {
        $sql = "SELECT name, email, phone, university_id FROM spocs";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUniversities() {
        $sql = "SELECT * FROM universities";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCourses() {
        $sql = "SELECT * FROM courses";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addFaculty($conn,$name, $email, $phone, $section, $stream, $year, $department, $university_id, $password) {
        $sql = "INSERT INTO faculty (name, email, phone, section, stream, year, department, university_id, password) VALUES (:name, :email, :phone, :section, :stream, :year, :department, :university_id, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':section' => $section,
            ':stream' => $stream,
            ':year' => $year,
            ':department' => $department,
            ':university_id' => $university_id,
            ':password' => $password
        ]);
        return $this->conn->lastInsertId();
    }

    public function getUserByEmail($conn, $email) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function storeResetToken($conn, $email, $token) {
        $stmt = $conn->prepare("UPDATE spocs SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $stmt->execute([$token, $email]);
    }

    public function getUserByResetToken($conn, $token) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePasswordByEmail($conn, $email, $new_password) {
        $stmt = $conn->prepare("UPDATE spocs SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
        $stmt->execute([$new_password, $email]);
    }
    public static function getByUniversityId($conn, $university_id) {
        $stmt = $conn->prepare("SELECT * FROM spocs WHERE university_id = :university_id");
        $stmt->execute([':university_id' => $university_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateLastLogin($spoc_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE spocs SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$spoc_id]);
    }
}
?>