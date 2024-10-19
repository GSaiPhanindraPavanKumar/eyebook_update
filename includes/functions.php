<?php
require_once __DIR__ . '/../config/database.php';

function generateQuestions($topic, $numQuestions, $marksPerQuestion) {
    $apiKey = GEMINI_API_KEY;
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

    // Attempt to parse the entire response as JSON
    $questions = json_decode($generatedText, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $questions;
    }

    // If parsing fails, try to extract JSON from the response
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

function createAssessment($title, $questions, $deadline) {
    global $conn;
    
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO assessments (title, deadline) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $deadline);
        $stmt->execute();
        $assessmentId = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO questions (assessment_id, question_text, options, correct_answer, marks) VALUES (?, ?, ?, ?, ?)");
        foreach ($questions as $question) {
            $options = json_encode($question['options']);
            $stmt->bind_param("isssi", $assessmentId, $question['questionText'], $options, $question['correctAnswer'], $question['marks']);
            $stmt->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
