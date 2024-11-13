<?php
// File: models/AssessmentModel.php
namespace Models;

use PDO;
use Exception;

class AssessmentModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createAssessment($title, $questions, $deadline) {
        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare("INSERT INTO assessments (title, deadline) VALUES (?, ?)");
            $stmt->execute([$title, $deadline]);
            $assessmentId = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("INSERT INTO questions (assessment_id, question_text, options, correct_answer, marks) VALUES (?, ?, ?, ?, ?)");
            foreach ($questions as $question) {
                $options = json_encode($question['options']);
                $stmt->execute([$assessmentId, $question['questionText'], $options, $question['correctAnswer'], $question['marks']]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function generateQuestionsUsingGemini($topic, $numQuestions, $marksPerQuestion) {
        $apiKey = 'AIzaSyAmfv5ML6txGnCXH3-7AYD-UwT57yj3VmI';
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

        $prompt = "Generate {$numQuestions} multiple-choice questions about {$topic}. For each question, provide 4 options, indicate the correct answer, and assign {$marksPerQuestion} marks to each question. Format the response as a JSON array of objects, where each object has properties: questionText, options (an array of 4 strings), correctAnswer (the correct option string), and marks (an integer equal to {$marksPerQuestion}). Do not include any markdown formatting or additional text.";

        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    "x-goog-api-key: $apiKey"
                ],
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);

        if ($result === FALSE) {
            throw new Exception('Failed to call Gemini API');
        }

        $response = json_decode($result, true);
        $generatedText = $response['candidates'][0]['content']['parts'][0]['text'];

        $questions = json_decode($generatedText, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $questions;
        }

        preg_match('/\[[\s\S]*\]/', $generatedText, $matches);
        if (empty($matches)) {
            throw new Exception('No valid JSON found in the response');
        }
        $jsonString = $matches[0];
        $questions = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse generated questions');
        }

        return $questions;
    }
}
?>