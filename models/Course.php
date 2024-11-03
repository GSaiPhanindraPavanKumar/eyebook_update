<?php
namespace Models;

use PDO;

class Course {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as course_count FROM courses";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['course_count'] ?? 0;
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM courses";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $query = 'SELECT * FROM courses WHERE id = :id';
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure universities field is an array
        $course['universities'] = isset($course['universities']) ? json_decode($course['universities'], true) : [];

        return $course;
    }

    public static function getUniversitiesByCourseId($conn, $course) {
        $universities = [];
        if (!empty($course['universities'])) {
            $universityIds = implode(',', array_map('intval', $course['universities']));
            $query = "SELECT * FROM universities WHERE id IN ($universityIds)";
            $stmt = $conn->query($query);
            $universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $universities;
    }
}
?>