<?php

namespace Models;
use PDO;
use PDOException;

use Models\Database;


class Assignment {
    public static function getAllByFaculty($conn, $faculty_id) {
        $sql = "SELECT a.*, c.name as course_name 
                FROM assignments a
                JOIN courses c ON a.course_id = c.id
                WHERE c.id = :faculty_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public static function getAll($conn) {
            $sql = "SELECT a.*, c.name as course_name FROM assignments a JOIN courses c ON a.course_id = c.id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        public static function getById($conn, $id) {
            $sql = "SELECT * FROM assignments WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    
        public static function getSubmissions($conn, $assignmentId) {
            $sql = "SELECT s.*, st.name as student_name, st.email, s.grade 
                    FROM assignment_submissions s
                    JOIN students st ON s.student_id = st.id
                    WHERE s.assignment_id = :assignment_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':assignment_id' => $assignmentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        
    
        // public static function getSubmission($conn, $assignmentId, $studentId) {
        //     $sql = "SELECT * FROM submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
        //     $stmt = $conn->prepare($sql);
        //     $stmt->execute([':assignment_id' => $assignmentId, ':student_id' => $studentId]);
        //     return $stmt->fetch(PDO::FETCH_ASSOC);
        // }
    
        public static function grade($conn, $assignmentId, $studentId, $grade, $feedback) {
            $sql = "UPDATE assignment_submissions SET grade = :grade, feedback = :feedback WHERE assignment_id = :assignment_id AND student_id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':grade' => $grade, ':feedback' => $feedback, ':assignment_id' => $assignmentId, ':student_id' => $studentId]);
        }
    
        public static function getSubmission($conn, $assignmentId, $studentId) {
            $sql = "SELECT * FROM assignment_submissions WHERE assignment_id = :assignment_id AND student_id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':assignment_id' => $assignmentId, ':student_id' => $studentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public static function getAssignmentsByStudentId($conn, $student_id) {
            $sql = "SELECT a.*, s.file_path, s.grade 
                    FROM assignments a
                    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = :student_id";
                   // -- WHERE a.course_id IN (SELECT course_id FROM courses WHERE student_id = :student_id)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        public static function getGradesByStudentId($conn, $student_id) {
            $sql = "SELECT a.title, s.grade, s.feedback 
                    FROM assignments a
                    JOIN assignment_submissions s ON a.id = s.assignment_id
                    WHERE s.student_id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
