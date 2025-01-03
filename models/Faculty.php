<?php
namespace Models;

use PDO;

class Faculty {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as Faculty_count FROM faculty";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['Faculty_count'] ?? 0;
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM faculty";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByIds($conn, $facultyIds) {
        if (empty($facultyIds)) {
            return [];
        }
    
        $placeholders = implode(',', array_fill(0, count($facultyIds), '?'));
        $sql = "SELECT * FROM faculty WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($facultyIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function search($conn, $searchQuery, $sortColumn = 'name', $sortOrder = 'asc') {
        $validColumns = ['name', 'email', 'university_short_name'];
        if (!in_array($sortColumn, $validColumns)) {
            $sortColumn = 'name';
        }
        $sql = "SELECT faculty.*, universities.short_name as university_short_name 
                FROM faculty 
                JOIN universities ON faculty.university_id = universities.id 
                WHERE faculty.name LIKE :search OR faculty.email LIKE :search 
                ORDER BY $sortColumn $sortOrder";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['search' => '%' . $searchQuery . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLoginDetails($faculty_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE faculty SET 
                    last_login = NOW(), 
                    login_count = login_count + 1, 
                    first_login = IF(first_login IS NULL, NOW(), first_login) 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$faculty_id]);
    }

    public static function getAssignedCourses($conn, $faculty_id) {
        $sql = "SELECT assigned_courses FROM faculty WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $faculty_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? ($result['assigned_courses'] ? json_decode($result['assigned_courses'], true) : []) : [];
    }

    public static function getUserDataByEmail($conn, $email) {
        $sql = "SELECT faculty.*, universities.long_name as university, universities.short_name as university_short_name 
                FROM faculty 
                JOIN universities ON faculty.university_id = universities.id 
                WHERE faculty.email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAssignedCoursesForFaculty($conn, $faculty_id) {
        $sql = "SELECT assigned_courses FROM faculty WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $faculty_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['assigned_courses'] ? json_decode($result['assigned_courses'], true) : [];
    }

    public static function assignCourse($conn, $faculty_id, $course_id) {
        $assigned_courses = self::getAssignedCourses($conn, $faculty_id);
        if (!in_array($course_id, $assigned_courses)) {
            $assigned_courses[] = $course_id;
            $sql = "UPDATE faculty SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode($assigned_courses),
                ':id' => $faculty_id
            ]);
        }
    }

    public static function unassignCourse($conn, $faculty_id, $course_id) {
        $assigned_courses = self::getAssignedCourses($conn, $faculty_id);
        if (in_array($course_id, $assigned_courses)) {
            $assigned_courses = array_values(array_diff($assigned_courses, [$course_id]));
            $sql = "UPDATE faculty SET assigned_courses = :assigned_courses WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':assigned_courses' => json_encode(array_values($assigned_courses)), // Ensure the array is re-indexed
                ':id' => $faculty_id
            ]);
        }
    }

    

    public static function existsByEmail($conn, $email) {
        $sql = "SELECT COUNT(*) as count FROM faculty WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function login($username, $password) {
        $conn = Database::getConnection();
        $sql = "SELECT * FROM faculty WHERE email = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $Faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($Faculty && password_verify($password, $Faculty['password'])) {
            return $Faculty;
        }

        return false;
    }

    public static function getByEmail($conn, $email) {
        $sql = "SELECT * FROM faculty WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserProfile($conn) {
        $query = 'SELECT * FROM faculty where email = :email';
        $stmt = $conn->prepare($query);
        $stmt->execute(['email' => $_SESSION['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public static function updatePassword($conn, $email, $newPassword) {
        $sql = "UPDATE faculty SET password = :new_password WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':new_password' => $newPassword,
            ':email' => $email
        ]);
    }

    public static function getById($conn, $id) {
        $query = "SELECT faculty.*, universities.long_name AS university_name 
                    FROM faculty 
                    JOIN universities ON faculty.university_id = universities.id 
                    WHERE faculty.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($conn, $id, $name, $email, $phone, $department) {
        $query = "UPDATE faculty SET name=?, email=?, phone=?, department=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $phone);
        $stmt->bindParam(4, $department);
        $stmt->bindParam(5, $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function getAllByUniversity($conn, $university_id) {
        $sql = "SELECT * FROM faculty WHERE university_id = :university_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['university_id' => $university_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin($faculty_id) {
        $conn = Database::getConnection();
        $sql = "UPDATE faculty SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$faculty_id]);
    }
}