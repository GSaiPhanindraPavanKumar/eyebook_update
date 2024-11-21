<?php

namespace Models;

use PDO;

class Assignment {
    public static function create($conn, $title, $instructions, $deadline, $course_id) {
        $sql = "INSERT INTO assignments (title, instructions, deadline, course_id) VALUES (:title, :instructions, :deadline, :course_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':instructions' => $instructions,
            ':deadline' => $deadline,
            ':course_id' => $course_id
        ]);
    }

    public static function getAllByFaculty($conn, $facultyEmail) {
        $sql = "SELECT a.*, c.name as course_name FROM assignments a
                JOIN courses c ON a.course_id = c.id
                JOIN faculty f ON c.id = f.course_id
                WHERE f.email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $facultyEmail]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSubmission($conn, $assignmentId, $studentId) {
        $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':assignment_id' => $assignmentId,
            ':student_id' => $studentId
        ]);
        return $student_coursesstmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function grade($conn, $assignmentId, $studentId, $grade) {
        $sql = "UPDATE assignment_submissions SET grade = :grade WHERE assignment_id = :assignment_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':grade' => $grade,
            ':assignment_id' => $assignmentId,
            ':student_id' => $studentId
        ]);
    }

    public static function getAll($conn) {
        $sql = "SELECT assignments.*, courses.name as course_name FROM assignments JOIN courses ON assignments.course_id = courses.id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function submitAssignment($conn, $student_id, $assignment_id, $file_path) {
        $sql = "INSERT INTO submissions (student_id, assignment_id, file_path) VALUES (:student_id, :assignment_id, :file_path)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':file_path', $file_path);
        return $stmt->execute();
    }

    public static function getAllByStudentId($conn, $student_id) {
        $sql = "SELECT a.*, s.file_path, s.grade 
                FROM assignments a
                LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
                WHERE a.course_id IN (SELECT id FROM courses WHERE student_id = :student_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':student_id' => $student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}