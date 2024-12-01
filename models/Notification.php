<?php
namespace Models;

use PDO;

class Notification {
    public static function create($conn, $studentId, $message) {
        $sql = "INSERT INTO notifications (student_id, message, created_at) VALUES (:student_id, :message, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'message' => $message,
        ]);
    }

    public static function getByStudentId($conn, $studentId) {
        $sql = "SELECT * FROM notifications WHERE student_id = :student_id ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}