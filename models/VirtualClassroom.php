<?php
namespace Models;

use PDO;

class VirtualClassroom {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url, course_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['id'], $data['topic'], $data['start_time'], $data['duration'], $data['join_url'], json_encode($data['course_id'])]);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM virtual_classrooms ORDER BY start_time DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVirtualClassroomsByIds($virtualClassIds) {
        if (empty($virtualClassIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
        $sql = "SELECT * FROM virtual_classrooms WHERE classroom_id IN ($placeholders) ORDER BY start_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($virtualClassIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVirtualClassroomsByIdsspoc($ids) {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM virtual_classrooms WHERE id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIds($virtualClassIds) {
        if (empty($virtualClassIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
        $sql = "SELECT * FROM virtual_classrooms WHERE classroom_id IN ($placeholders) ORDER BY start_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($virtualClassIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVirtualClassroomsByCourseIdsForSpoc($course_ids) {
        if (empty($course_ids)) {
            return [];
        }
    
        $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
        $sql = "SELECT * FROM virtual_classrooms WHERE JSON_CONTAINS(course_id, JSON_QUOTE(CAST(id AS CHAR)), '$') AND course_id IN ($placeholders) ORDER BY start_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($course_ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($virtualClassIds) {
        if (empty($virtualClassIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($virtualClassIds), '?'));
        $sql = "SELECT * FROM virtual_classrooms WHERE id IN ($placeholders) ORDER BY start_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($virtualClassIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendance($classroomId) {
        $stmt = $this->conn->prepare("SELECT attendance FROM virtual_classrooms WHERE classroom_id = ?");
        $stmt->execute([$classroomId]);
        return $stmt->fetchColumn();
    }

    public function getVirtualClassesForStudent($studentId, $date) {
        $stmt = $this->conn->prepare("
            SELECT vc.* 
            FROM virtual_classrooms vc
            JOIN student_classrooms sc ON vc.classroom_id = sc.classroom_id
            WHERE sc.student_id = :student_id AND vc.date = :date
        ");
        $stmt->execute(['student_id' => $studentId, 'date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}