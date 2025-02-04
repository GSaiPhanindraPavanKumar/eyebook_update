<?php
namespace Models;

use PDO;

class PublicLab {
    public static function create($conn, $title, $description, $course_ids, $input, $output, $submissions) {
        $sql = "INSERT INTO public_labs (title, description, course_id, input, output, submissions) VALUES (:title, :description, :course_id, :input, :output, :submissions)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':course_id' => json_encode($course_ids),
            ':input' => $input,
            ':output' => $output,
            ':submissions' => json_encode($submissions)
        ]);
        return $conn->lastInsertId();
    }

    public static function getByCourseId($conn, $course_id) {
        $sql = "SELECT * FROM public_labs WHERE JSON_CONTAINS(course_id, JSON_QUOTE(CAST(:course_id AS CHAR)), '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $lab_id) {
        $sql = "SELECT * FROM public_labs WHERE id = :lab_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':lab_id' => $lab_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getSubmissions($conn, $lab_id) {
        // Fetch the submissions JSON data
        $sql = "SELECT submissions FROM public_labs WHERE id = :lab_id";
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

    public static function getAllByCourseId($conn, $course_id) {
        $sql = "SELECT * FROM public_labs WHERE JSON_CONTAINS(course_id, JSON_QUOTE(CAST(:course_id AS CHAR)), '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':course_id' => $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getSubmissionsByStudent($conn, $lab_id, $student_id) {
        $sql = "SELECT submissions FROM public_labs WHERE id = :lab_id";
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