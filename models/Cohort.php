<?php
namespace Models;

use PDO;

class Cohort {
    public static function create($conn, $name, $student_ids = [], $course_ids = []) {
        $sql = "INSERT INTO cohorts (name, student_ids, course_ids) VALUES (:name, :student_ids, :course_ids)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':student_ids' => json_encode($student_ids),
            ':course_ids' => json_encode($course_ids)
        ]);
    }

    public static function getAll($conn) {
        $sql = "SELECT * FROM cohorts ORDER BY id ASC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $sql = "SELECT * FROM cohorts WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($conn, $id, $name) {
        $sql = "UPDATE cohorts SET name = :name WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':id' => $id
        ]);
    }

    public static function delete($conn, $id) {
        $sql = "DELETE FROM cohorts WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public static function getStudentCount($conn, $cohort_id) {
        $sql = "SELECT student_ids FROM cohorts WHERE id = :cohort_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':cohort_id' => $cohort_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $student_ids = json_decode($result['student_ids'], true);
        return is_array($student_ids) ? count($student_ids) : 0;
    }
    public static function addCourse($conn, $cohort_id, $course_id) {
        $cohort = self::getById($conn, $cohort_id);
        $course_ids = json_decode($cohort['course_ids'], true) ?? [];
    
        if (!in_array($course_id, $course_ids)) {
            $course_ids[] = $course_id;
            $sql = "UPDATE cohorts SET course_ids = :course_ids WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':course_ids' => json_encode($course_ids),
                ':id' => $cohort_id
            ]);
        }
    }
    public static function unassignCourse($conn, $cohort_id, $course_id) {
        $cohort = self::getById($conn, $cohort_id);
        $course_ids = json_decode($cohort['course_ids'], true) ?? [];
    
        if (in_array($course_id, $course_ids)) {
            $course_ids = array_diff($course_ids, [$course_id]);
            $sql = "UPDATE cohorts SET course_ids = :course_ids WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':course_ids' => json_encode($course_ids),
                ':id' => $cohort_id
            ]);
        }
    }
    public static function updateStudentIds($conn, $cohort_id, $student_ids) {
        // If $student_ids is an array, convert it to JSON
        if (is_array($student_ids)) {
            // Convert all IDs to strings
            $student_ids = array_map('strval', $student_ids);
            // Sort the array to maintain consistent order
            sort($student_ids);
            // Encode as JSON array without numeric keys
            $student_ids = json_encode(array_values($student_ids));
        }

        $sql = "UPDATE cohorts SET student_ids = :student_ids WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':student_ids' => $student_ids,
            ':id' => $cohort_id
        ]);
    }
}