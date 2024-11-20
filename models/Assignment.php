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
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
}