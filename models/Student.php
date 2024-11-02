<?php
namespace Models;
use PDO;
class Student {
    public static function getCount($conn) {
        $sql = "SELECT COUNT(*) as student_count FROM students";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['student_count'] ?? 0;
    }
}
?>