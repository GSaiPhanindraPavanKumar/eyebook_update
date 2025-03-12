<?php
namespace Models;

use PDO;
use PDOException;

class Assessment {
    public static function getAll($conn) {
        try {
            $sql = "SELECT * FROM assessments";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching assessments: " . $e->getMessage());
            return [];
        }
    }

    public static function create($conn, $data) {
        try {
            $sql = "INSERT INTO assessments (title, start_time, end_time, duration, questions, submissions) 
                    VALUES (:title, :start_time, :end_time, :duration, :questions, :submissions)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':duration' => $data['duration'],
                ':questions' => json_encode($data['questions']),
                ':submissions' => json_encode($data['submissions'])
            ]);
            return $conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating assessment: " . $e->getMessage());
            return false;
        }
    }

    public static function getById($conn, $id) {
        try {
            $sql = "SELECT * FROM assessments WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching assessment: " . $e->getMessage());
            return false;
        }
    }

    public static function update($conn, $id, $data) {
        try {
            $sql = "UPDATE assessments SET title = :title, start_time = :start_time, end_time = :end_time, 
                    duration = :duration, questions = :questions, submissions = :submissions WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':duration' => $data['duration'],
                ':questions' => json_encode($data['questions']),
                ':submissions' => json_encode($data['submissions']),
                ':id' => $id
            ]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error updating assessment: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($conn, $id) {
        try {
            $sql = "DELETE FROM assessments WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error deleting assessment: " . $e->getMessage());
            return false;
        }
    }
    public function viewAssessment($id) {
        if (!isset($_SESSION['email'])) {
            header('Location: /session-timeout');
            exit;
        }
    
        $conn = Database::getConnection();
        $assessment = Assessment::getById($conn, $id);
    
        if (!$assessment) {
            $_SESSION['error'] = "Assessment not found.";
            header('Location: /student/view_assessments');
            exit;
        }
    
        require 'views/student/view_assessment.php';
    }
}
?>