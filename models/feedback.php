<?php
namespace Models;

use PDO;

class feedback {
    public static function saveFeedback($conn, $course_id, $student_id, $feedback) {
        $sql = "INSERT INTO feedback (course_id, student_id, depth_of_coverage, emphasis_on_fundamentals, coverage_of_modern_topics, overall_rating, benefits, instructor_assistance, instructor_feedback, motivation, sme_help, overall_very_good) 
                VALUES (:course_id, :student_id, :depth_of_coverage, :emphasis_on_fundamentals, :coverage_of_modern_topics, :overall_rating, :benefits, :instructor_assistance, :instructor_feedback, :motivation, :sme_help, :overall_very_good)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':course_id' => $course_id,
            ':student_id' => $student_id,
            ':depth_of_coverage' => $feedback['depth_of_coverage'],
            ':emphasis_on_fundamentals' => $feedback['emphasis_on_fundamentals'],
            ':coverage_of_modern_topics' => $feedback['coverage_of_modern_topics'],
            ':overall_rating' => $feedback['overall_rating'],
            ':benefits' => $feedback['benefits'],
            ':instructor_assistance' => $feedback['instructor_assistance'],
            ':instructor_feedback' => $feedback['instructor_feedback'],
            ':motivation' => $feedback['motivation'],
            ':sme_help' => $feedback['sme_help'],
            ':overall_very_good' => $feedback['overall_very_good']
        ]);
    }
    public static function hasFeedback($conn, $course_id, $student_id) {
        $sql = "SELECT COUNT(*) FROM feedback WHERE course_id = :course_id AND student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':course_id' => $course_id,
            ':student_id' => $student_id
        ]);
        return $stmt->fetchColumn() > 0;
    }
}