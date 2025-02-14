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
        $contest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add debug logging
        error_log("Contest fetch result: " . print_r($contest, true));
        
        return $contest;
    }

    public static function getByUniversityId($conn, $universityId) {
        $sql = "SELECT * FROM contests WHERE JSON_CONTAINS(university_id, :university_id, '$')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':university_id' => json_encode((string)$universityId)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $data) {
        $sql = "INSERT INTO contests (
                title, description, start_date, end_date, 
                university_id, time_limit
                ) 
                VALUES (
                :title, :description, :start_date, :end_date, 
                :university_id, :time_limit
                )";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':university_id' => $data['university_id'],
            ':time_limit' => isset($data['time_limit']) ? $data['time_limit'] : 120 // Default 120 minutes if not set
        ]);
    }

    public static function update($conn, $id, $data) {
        $sql = "UPDATE contests SET 
                title = :title, 
                description = :description, 
                start_date = :start_date, 
                end_date = :end_date, 
                university_id = :university_id,
                time_limit = :time_limit 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':university_id' => json_encode($data['university_id']),
            ':time_limit' => isset($data['time_limit']) ? $data['time_limit'] : 120,
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

    public static function getQuestionById($conn, $questionId) {
        $sql = "SELECT * FROM contest_questions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $questionId]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add debug logging
        error_log("Question fetch result: " . print_r($question, true));
        
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
        // Fetch the contest ID associated with the question
        $sql = "SELECT contest_id FROM contest_questions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($question) {
            $contestId = $question['contest_id'];

            // Delete the question from the contest_questions table
            $sql = "DELETE FROM contest_questions WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Update the questions JSON column in the contests table
            $contest = self::getById($conn, $contestId);
            $questions = json_decode($contest['questions'], true) ?? [];
            $updatedQuestions = array_diff($questions, [$id]);
            $sql = "UPDATE contests SET questions = :questions WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':questions' => json_encode(array_values($updatedQuestions)),
                ':id' => $contestId
            ]);
        }
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
    public static function getCountByUniversityId($conn, $university_id) {
        $sql = "SELECT COUNT(*) as contest_count FROM contests WHERE JSON_CONTAINS(university_id, :university_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':university_id' => json_encode($university_id)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['contest_count'];
    }
    
    public static function getQuestionWithContest($conn, $questionId) {
        // First get the question
        $sql = "SELECT q.*, c.id as contest_id, c.title as contest_title, 
                c.start_date, c.end_date, c.time_limit 
                FROM contest_questions q 
                JOIN contests c ON q.contest_id = c.id 
                WHERE q.id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $questionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return [
                'question' => [
                    'id' => $result['id'],
                    'question' => $result['question'],
                    'description' => $result['description'],
                    'input' => $result['input'],
                    'output' => $result['output'],
                    'grade' => $result['grade'],
                    'submissions' => $result['submissions']
                ],
                'contest' => [
                    'id' => $result['contest_id'],
                    'title' => $result['contest_title'],
                    'start_date' => $result['start_date'],
                    'end_date' => $result['end_date'],
                    'time_limit' => $result['time_limit']
                ]
            ];
        }
        
        return null;
    }
}