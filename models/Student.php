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

    public static function getAll($conn) {
        $sql = "SELECT * FROM students";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $_SESSION['email'] = $student['email']; // Set email in session
            return $student;
        }

        return false;
    }

    public static function getById($conn, $id) {
        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByEmail($conn, $email) {
        $sql = "SELECT * FROM students WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAllByUniversity($conn, $university_id) {
        $sql = "SELECT * FROM students WHERE university_id = :university_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllByFaculty($conn, $facultyEmail) {
        $sql = "SELECT students.* 
                FROM students 
                JOIN faculty ON students.university_id = faculty.university_id 
                WHERE faculty.email = :facultyEmail";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':facultyEmail', $facultyEmail, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // public static function submitAssignment($conn, $student_id, $assignment_id, $file_path) {
    //     $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, file_path) VALUES (:student_id, :assignment_id, :file_path)
    //             ON DUPLICATE KEY UPDATE file_path = :file_path";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute([
    //         ':student_id' => $student_id,
    //         ':assignment_id' => $assignment_id,
    //         ':file_path' => $file_path
    //     ]);
    // }

    public static function updatePassword($conn, $student_id, $new_password) {
        $stmt = $conn->prepare("UPDATE students SET password = :new_password WHERE id = :admin_id");
        $stmt->execute([
            ':new_password' => $new_password,
            ':admin_id' => $student_id
        ]);
    }

    public static function getAssignmentsByStudentId($conn, $student_id) {
        $sql = "SELECT a.*, s.file_path, s.grade 
                FROM assignments a
                LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
                WHERE a.course_id IN (SELECT course_id FROM student_courses WHERE student_id = :student_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllBySectionYearAndUniversity($conn, $facultyEmail, $year = null, $section = null) {
        $sql = "SELECT students.* 
                FROM students 
                JOIN faculty ON students.university_id = faculty.university_id 
                WHERE faculty.email = :facultyEmail";

        $params = ['facultyEmail' => $facultyEmail];

        if ($year !== null) {
            $sql .= " AND students.year = :year";
            $params['year'] = $year;
        }

        if ($section !== null) {
            $sql .= " AND students.section = :section";
            $params['section'] = $section;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getCountByUniversityId($conn, $university_id) {
        $sql = "SELECT COUNT(*) as student_count FROM students WHERE university_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$university_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }
    public function updateLastLogin($student_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE students SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$student_id]);
    }

    // In Student.php
    public static function getAllByCourseAndSection($conn, $course_id, $section_id) {
        $sql = "SELECT * FROM students WHERE course_id = :course_id AND section_id = :section_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id, ':section_id' => $section_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // In Course.php
    public static function getCoursesByFaculty($conn) {
        $faculty_id = $_SESSION['faculty_id']; // Assuming faculty_id is stored in session
        $sql = "SELECT * FROM courses WHERE faculty_id = :faculty_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submitAssignment($conn, $student_id, $assignment_id, $file_content) {
        $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, file_content) VALUES (:student_id, :assignment_id, :file_content)
                ON DUPLICATE KEY UPDATE file_content = :file_content";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':file_content', $file_content, PDO::PARAM_LOB);
        $stmt->execute();
    }
}
?>