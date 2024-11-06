<?php
namespace Models;

use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once __DIR__ . '/../vendor/autoload.php';

class Student {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as student_count FROM students";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }

    public static function uploadStudents($conn, $data, $university_id) {
        // Check for duplicates
        $sql = "SELECT * FROM students WHERE regd_no = :regd_no OR email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':regd_no' => $data['regd_no'],
            ':email' => $data['email']
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Return the duplicate record
            return [
                'duplicate' => true,
                'data' => $data
            ];
        } else {
            // Insert the new record
            $sql = "INSERT INTO students (regd_no, name, email, section, stream, year, dept, university_id, password) 
                    VALUES (:regd_no, :name, :email, :section, :stream, :year, :dept, :university_id, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':regd_no' => $data['regd_no'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':section' => $data['section'],
                ':stream' => $data['stream'],
                ':year' => $data['year'],
                ':dept' => $data['dept'],
                ':university_id' => $university_id,
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            return [
                'duplicate' => false
            ];
        }
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM students WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student['password'])) {
            return $student;
        }

        return false;
    }
}