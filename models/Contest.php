<?php
namespace Models;

use PDO;

class Contest {
    public static function getAll($conn) {
        $sql = "SELECT * FROM contests";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($conn, $id) {
        $sql = "SELECT * FROM contests WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByUniversityId($conn, $universityId) {
        $sql = "SELECT * FROM contests WHERE JSON_CONTAINS(university_id, :university_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':university_id' => json_encode((string)$universityId)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $data) {
        $sql = "INSERT INTO contests (title, description, start_date, end_date, university_id) 
                VALUES (:title, :description, :start_date, :end_date, :university_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':university_id' => json_encode($data['university_id'])
        ]);
    }

    public static function update($conn, $id, $data) {
        $sql = "UPDATE contests SET title = :title, description = :description, start_date = :start_date, end_date = :end_date, university_id = :university_id WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':university_id' => json_encode($data['university_id']),
            ':id' => $id
        ]);
    }

    public static function delete($conn, $id) {
        $sql = "DELETE FROM contests WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public static function getQuestions($conn, $contestId) {
        $sql = "SELECT * FROM contest_questions WHERE contest_id = :contest_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':contest_id' => $contestId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode the submissions JSON and count the number of submissions
        foreach ($questions as &$question) {
            $submissions = !empty($question['submissions']) ? json_decode($question['submissions'], true) : [];
            $question['submission_count'] = is_array($submissions) ? count($submissions) : 0;
        }

        return $questions;
    }

    public static function addQuestion($conn, $contestId, $data) {
        $sql = "INSERT INTO contest_questions (contest_id, question, description, input, output, grade) 
                VALUES (:contest_id, :question, :description, :input, :output, :grade)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contest_id' => $contestId,
            ':question' => $data['question'],
            ':description' => $data['description'],
            ':input' => $data['input'],
            ':output' => $data['output'],
            ':grade' => $data['grade']
        ]);

        // Get the last inserted question ID
        $questionId = $conn->lastInsertId();

        // Update the questions JSON field in the contests table
        $contest = self::getById($conn, $contestId);
        $questions = json_decode($contest['questions'], true) ?? [];
        $questions[] = $questionId;
        $sql = "UPDATE contests SET questions = :questions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':questions' => json_encode($questions),
            ':id' => $contestId
        ]);
    }

    public static function getQuestionById($conn, $id) {
        $sql = "SELECT * FROM contest_questions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch the submissions JSON data
        $submissions = !empty($question['submissions']) ? json_decode($question['submissions'], true) : [];

        // Prepare an array to hold the detailed submissions
        $detailedSubmissions = [];

        // Fetch student names for each submission and format the submission date
        foreach ($submissions as $submission) {
            $sql = "SELECT name FROM students WHERE id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $submission['student_id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $submission['student_name'] = $student['name'];
                $submission['submission_date'] = date('Y-m-d H:i:s', strtotime($submission['submission_date']));
                $detailedSubmissions[] = $submission;
            }
        }

        $question['submissions'] = $detailedSubmissions;

        return $question;
    }

    public static function updateQuestion($conn, $id, $data) {
        $sql = "UPDATE contest_questions SET question = :question, description = :description, input = :input, output = :output, grade = :grade WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':question' => $data['question'],
            ':description' => $data['description'],
            ':input' => $data['input'],
            ':output' => $data['output'],
            ':grade' => $data['grade'],
            ':id' => $id
        ]);
    }

    public static function deleteQuestion($conn, $id) {
        $sql = "DELETE FROM contest_questions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public static function getLeaderboard($conn, $contestId) {
        $sql = "SELECT cq.submissions, cq.grade FROM contest_questions cq WHERE cq.contest_id = :contest_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':contest_id' => $contestId]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $leaderboard = [];

        foreach ($questions as $question) {
            $submissions = !empty($question['submissions']) ? json_decode($question['submissions'], true) : [];
            foreach ($submissions as $submission) {
                if ($submission['status'] == 'passed') {
                    if (!isset($leaderboard[$submission['student_id']])) {
                        $leaderboard[$submission['student_id']] = [
                            'student_id' => $submission['student_id'],
                            'total_grade' => 0
                        ];
                    }
                    $leaderboard[$submission['student_id']]['total_grade'] += $question['grade'];
                }
            }
        }

        // Fetch student names
        foreach ($leaderboard as &$entry) {
            $sql = "SELECT name FROM students WHERE id = :student_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':student_id' => $entry['student_id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $entry['student_name'] = $student['name'];
        }

        // Sort leaderboard by total_grade in descending order
        usort($leaderboard, function($a, $b) {
            return $b['total_grade'] - $a['total_grade'];
        });

        return $leaderboard;
    }
}