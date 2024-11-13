<?php
namespace Models;

use PDO;

class VirtualClassroom {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO virtual_classrooms (classroom_id, topic, start_time, duration, join_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['id'], $data['topic'], $data['start_time'], $data['duration'], $data['join_url']]);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM virtual_classrooms ORDER BY start_time DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStartTime($classroomId, $startTime) {
        $stmt = $this->conn->prepare("UPDATE virtual_classrooms SET start_time = ? WHERE classroom_id = ?");
        $stmt->execute([$startTime, $classroomId]);
    }

    public function getAttendance($classroomId) {
        $stmt = $this->conn->prepare("SELECT * FROM attendance WHERE classroom_id = ?");
        $stmt->execute([$classroomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>