<?php
namespace Models;

use PDO;

class Lab {
    public static function getAllByFaculty($conn, $faculty_id) {
        $sql = "SELECT l.*, c.name as course_name 
                FROM labs l
                JOIN courses c ON FIND_IN_SET(c.id, l.course_id) 
                WHERE FIND_IN_SET(:faculty_id, c.assigned_faculty)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':faculty_id' => $faculty_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByIds($conn, $labIds) {
        if (empty($labIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($labIds), '?'));
        $sql = "SELECT * FROM labs WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($labIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll($conn) {
        $sql = "SELECT l.*, c.name as course_name 
                FROM labs l 
                JOIN courses c ON JSON_CONTAINS(l.course_id, JSON_QUOTE(CAST(c.id AS CHAR)), '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $title, $description, $due_date, $course_ids, $input, $output, $submissions) {
        $sql = "INSERT INTO labs (title, description, due_date, course_id, input, output, submissions) VALUES (:title, :description, :due_date, :course_id, :input, :output, :submissions)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $due_date,
            ':course_id' => json_encode($course_ids),
            ':input' => $input,
            ':output' => $output,
            ':submissions' => json_encode($submissions)
        ]);
        return $conn->lastInsertId();
    }
    public static function getAllByCourseId($conn, $course_id) {
        $sql = "SELECT * FROM labs WHERE JSON_CONTAINS(course_id, JSON_QUOTE(CAST(:course_id AS CHAR)), '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getById($conn, $lab_id) {
        $sql = "SELECT * FROM labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getSubmissions($conn, $lab_id) {
        // Fetch the submissions JSON data
        $sql = "SELECT submissions FROM labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = $result ? json_decode($result['submissions'], true) : [];

        // Prepare an array to hold the detailed submissions
        $detailedSubmissions = [];

        // Fetch student names for each submission
        foreach ($submissions as $submission) {
            $sql = "SELECT name FROM students WHERE id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $submission['student_id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $submission['student_name'] = $student['name'];
                $detailedSubmissions[] = $submission;
            }
        }

        return $detailedSubmissions;
    }
    public static function getSubmissionsByStudent($conn, $lab_id, $student_id) {
        $sql = "SELECT submissions FROM labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissions = $result ? json_decode($result['submissions'], true) : [];

        // Filter submissions by student_id
        $studentSubmissions = array_filter($submissions, function($submission) use ($student_id) {
            return $submission['student_id'] == $student_id;
        });

        return $studentSubmissions;
    }
}