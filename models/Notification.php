<?php
namespace Models;

use PDO;

class Notification {
    public static function create($conn, $courseId, $message) {
        $sql = "INSERT INTO notifications (course_id, message, created_at) VALUES (:course_id, :message, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'course_id' => $courseId,
            'message' => $message
        ]);
    }

    public static function getByStudentId($conn, $studentId) {
        // Fetch assigned courses for the student
        $sql = "SELECT assigned_courses FROM students WHERE id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        $assignedCourses = $stmt->fetchColumn();
        $assignedCourses = $assignedCourses ? json_decode($assignedCourses, true) : [];

        if (empty($assignedCourses)) {
            return [];
        }

        // Fetch notifications for the assigned courses
        $placeholders = implode(',', array_fill(0, count($assignedCourses), '?'));
        $sql = "SELECT * FROM notifications WHERE course_id IN ($placeholders) ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($assignedCourses);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}